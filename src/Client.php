<?php

declare(strict_types=1);

namespace AmazonPaapi5;

use AmazonPaapi5\Auth\AwsV4Signer;
use AmazonPaapi5\Security\CredentialManager;
use AmazonPaapi5\Cache\AdvancedCache;
use AmazonPaapi5\Cache\ThrottleManager;
use AmazonPaapi5\Connection\ConnectionPool;
use AmazonPaapi5\Batch\BatchProcessor;
use AmazonPaapi5\Queue\RequestQueueOptimizer;
use AmazonPaapi5\Exceptions\ApiException;
use AmazonPaapi5\Exceptions\AuthenticationException;
use AmazonPaapi5\Exceptions\RequestException;
use AmazonPaapi5\Exceptions\ThrottleException;
use AmazonPaapi5\Monitoring\Monitor;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Client
{
    private Config $config;
    private GuzzleClient $httpClient;
    private AwsV4Signer $signer;
    private CredentialManager $credentialManager;
    private CacheItemPoolInterface $cache;
    private ThrottleManager $throttleManager;
    private ConnectionPool $connectionPool;
    private BatchProcessor $batchProcessor;
    private RequestQueueOptimizer $queueOptimizer;
    private LoggerInterface $logger;
    private Monitor $monitor;

    public function __construct(
        Config $config,
        ?CacheItemPoolInterface $cache = null,
        ?LoggerInterface $logger = null
    ) {
        // Initialize basic components
        $this->config = $config;
        $this->logger = $logger ?? new NullLogger();
        $this->monitor = new Monitor($this->logger);
        
        // Initialize credential manager and signer
        $this->credentialManager = new CredentialManager($config);
        $this->signer = new AwsV4Signer(
            $this->credentialManager->getAccessKey(),
            $this->credentialManager->getSecretKey(),
            $config->getRegion()
        );
        
        // Initialize connection pool with security settings
        $this->connectionPool = new ConnectionPool([
            'verify_ssl' => $config->getVerifySsl(),
            'tls_version' => $config->getTlsVersion(),
            'request_timeout' => $config->getRequestTimeout(),
            'connection_timeout' => $config->getConnectionTimeout()
        ]);
        $this->httpClient = $this->connectionPool->getConnection();
        
        // Initialize cache
        $this->cache = $cache ?? new AdvancedCache($config->getCacheDir());
        
        // Initialize managers and processors
        $this->throttleManager = new ThrottleManager($config->getThrottleDelay());
        $this->batchProcessor = new BatchProcessor($this, $this->logger);
        $this->queueOptimizer = new RequestQueueOptimizer($this->logger);
    }

    public function execute(AbstractOperation $operation): PromiseInterface
    {
        $operation->setClient($this);
        return $operation->executeAsync();
    }

    public function sendAsync(AbstractOperation $operation): PromiseInterface
    {
        $requestId = $this->monitor->startRequest(get_class($operation));
        $cacheKey = $this->generateCacheKey($operation);

        try {
            if ($this->cache->hasItem($cacheKey)) {
                $item = $this->cache->getItem($cacheKey);
                if ($item->isHit()) {
                    $this->monitor->recordCacheResult(true);
                    $this->logger->debug('Cache hit', ['operation' => get_class($operation)]);
                    $this->monitor->endRequest($requestId, true);
                    return \GuzzleHttp\Promise\promise_for(
                        $this->createResponse($operation, $item->get())
                    );
                }
            }
            $this->monitor->recordCacheResult(false);
        } catch (\Exception $e) {
            $this->logger->warning('Cache error', [
                'error' => $e->getMessage(),
                'operation' => get_class($operation)
            ]);
        }

        return $this->throttleManager->throttle(
            function () use ($operation, $cacheKey, $requestId) {
                $request = $this->createRequest($operation);
                $signedRequest = $this->signer->signRequest($request);

                $this->logger->info('Sending request', [
                    'operation' => get_class($operation),
                    'path' => $operation->getPath(),
                    'url' => (string)$signedRequest->getUri()
                ]);

                return $this->httpClient->sendAsync($signedRequest)->then(
                    function ($response) use ($operation, $cacheKey, $requestId) {
                        $statusCode = $response->getStatusCode();
                        $data = json_decode((string)$response->getBody(), true);

                        if ($statusCode >= 400) {
                            $this->handleError($data, $statusCode);
                        }

                        try {
                            $responseObj = $this->createResponse($operation, $data);
                            $this->cacheResponse($cacheKey, $data);
                            $this->monitor->endRequest($requestId, true);
                            return $responseObj;
                        } catch (\Exception $e) {
                            $this->monitor->endRequest($requestId, false, 'processing_error');
                            throw new ApiException(
                                'Failed to process response: ' . $e->getMessage(),
                                ['response_data' => $data]
                            );
                        }
                    },
                    function ($exception) use ($requestId) {
                        if ($exception instanceof GuzzleRequestException) {
                            $this->monitor->endRequest($requestId, false, 'network_error');
                            throw new RequestException(
                                'Request failed: ' . $exception->getMessage(),
                                ['code' => $exception->getCode()]
                            );
                        }
                        $this->monitor->endRequest($requestId, false, 'unknown_error');
                        throw new ApiException('Unexpected error: ' . $exception->getMessage());
                    }
                );
            },
            [],
            $operation->getMarketplace()
        );
    }

    public function processBatch(array $operations): array
    {
        return $this->batchProcessor->processBatch($operations);
    }

    public function queueRequest(AbstractOperation $operation, int $priority = 1): void
    {
        $this->queueOptimizer->addRequest($operation, $priority);
    }

    public function processQueue(): array
    {
        $batches = $this->queueOptimizer->optimizeQueue();
        $results = [];

        foreach ($batches as $type => $operations) {
            try {
                $batchResults = $this->processBatch($operations);
                $results[$type] = $batchResults;
            } catch (\Throwable $e) {
                $this->logger->error('Error processing queue batch', [
                    'type' => $type,
                    'error' => $e->getMessage()
                ]);
                $results[$type] = null;
            }
        }

        return $results;
    }

    private function createRequest(AbstractOperation $operation): RequestInterface
    {
        // Get the correct API host from marketplace configuration
        $apiHost = Marketplace::getHost($this->config->getMarketplace());
        
        $baseUrl = sprintf(
            'https://%s',
            $apiHost
        );
        
        $this->logger->debug('Creating request', [
            'marketplace' => $this->config->getMarketplace(),
            'api_host' => $apiHost,
            'base_url' => $baseUrl,
            'path' => $operation->getPath()
        ]);
        
        return new Request(
            $operation->getMethod(),
            $baseUrl . $operation->getPath(),
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip'
            ],
            json_encode($operation->getRequest()->toArray())
        );
    }

    private function cacheResponse(string $cacheKey, array $data): void
    {
        try {
            $cacheItem = $this->cache->getItem($cacheKey);
            $cacheItem->set($data)->expiresAfter($this->config->getCacheTtl());
            $this->cache->save($cacheItem);
            $this->logger->debug('Response cached', ['key' => $cacheKey]);
        } catch (\Exception $e) {
            $this->logger->warning('Failed to cache response', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function generateCacheKey(AbstractOperation $operation): string
    {
        return md5(
            get_class($operation) .
            json_encode($operation->getRequest()->toArray()) .
            $operation->getMarketplace()
        );
    }

    private function handleError($data, int $statusCode): void
    {
        // Handle null data case (404 responses may have empty bodies)
        if ($data === null) {
            $message = 'Empty response from API with status code ' . $statusCode;
            
            $errorContext = [
                'status_code' => $statusCode,
                'error_code' => 'EmptyResponse',
                'message' => $message
            ];
            
            if ($this->logger) {
                $this->logger->error('API error with null response', $errorContext);
            }
            
            switch ($statusCode) {
                case 401:
                case 403:
                    throw new AuthenticationException("Authentication failed. Check your Access Key, Secret Key, and Partner Tag. Status: $statusCode", ['status_code' => $statusCode]);
                case 429:
                    throw new ThrottleException("Rate limit exceeded. Increase throttle_delay. Status: $statusCode", ['status_code' => $statusCode]);
                case 404:
                    throw new RequestException("Resource not found (404). Possible causes: 1) Invalid marketplace configuration, 2) Incorrect credentials, 3) Wrong API endpoint. Check your marketplace and partner tag settings. Suggestion: Check request parameters and try again.", ['status_code' => $statusCode]);
                case 400:
                    throw new RequestException("Bad Request (400). Check your request parameters.", ['status_code' => $statusCode]);
                default:
                    throw new ApiException($message, ['status_code' => $statusCode]);
            }
            return;
        }
        
        // Original code for array data
        $message = $data['Errors'][0]['Message'] ?? 'Unknown error';
        $code = $data['Errors'][0]['Code'] ?? 'Unknown';
        $details = $data['Errors'][0]['Details'] ?? [];

        $errorContext = [
            'status_code' => $statusCode,
            'error_code' => $code,
            'message' => $message,
            'details' => $details
        ];

        if ($this->logger) {
            $this->logger->error('API error', $errorContext);
        }

        switch ($statusCode) {
            case 401:
            case 403:
                throw new AuthenticationException($message, $errorContext);
            case 429:
                throw new ThrottleException($message, $errorContext);
            case 400:
                throw new RequestException($message, $errorContext);
            default:
                throw new ApiException($message, $errorContext);
        }
    }

    private function createResponse(AbstractOperation $operation, array $data)
    {
        $responseClass = $operation->getResponseClass();
        return new $responseClass($data);
    }

    public function getMetrics(): array
    {
        return $this->monitor->getMetrics();
    }

    public function __destruct()
    {
        if (isset($this->connectionPool)) {
            $this->connectionPool->closeAll();
        }
    }
}