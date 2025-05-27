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

    public function __construct(
        Config $config,
        ?CacheItemPoolInterface $cache = null,
        ?LoggerInterface $logger = null
    ) {
        $this->config = $config;
        $this->logger = $logger ?? new NullLogger();
        
        // Initialize credential manager and signer
        $this->credentialManager = new CredentialManager($config);
        $this->signer = new AwsV4Signer(
            $this->credentialManager->getAccessKey(),
            $this->credentialManager->getSecretKey(),
            $config->getRegion()
        );
        
        // Initialize connection pool
        $this->connectionPool = new ConnectionPool($config->toArray());
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
        $cacheKey = $this->generateCacheKey($operation);

        try {
            if ($this->cache->hasItem($cacheKey)) {
                $item = $this->cache->getItem($cacheKey);
                if ($item->isHit()) {
                    $this->logger->debug('Cache hit', ['operation' => get_class($operation)]);
                    return \GuzzleHttp\Promise\promise_for(
                        $this->createResponse($operation, $item->get())
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning('Cache error', [
                'error' => $e->getMessage(),
                'operation' => get_class($operation)
            ]);
        }

        return $this->throttleManager->throttle(
            function () use ($operation, $cacheKey) {
                $request = $this->createRequest($operation);
                $signedRequest = $this->signer->signRequest($request);

                $this->logger->info('Sending request', [
                    'operation' => get_class($operation),
                    'path' => $operation->getPath()
                ]);

                return $this->httpClient->sendAsync($signedRequest)->then(
                    function ($response) use ($operation, $cacheKey) {
                        $statusCode = $response->getStatusCode();
                        $data = json_decode((string)$response->getBody(), true);

                        if ($statusCode >= 400) {
                            $this->handleError($data, $statusCode);
                        }

                        try {
                            $responseObj = $this->createResponse($operation, $data);
                            $this->cacheResponse($cacheKey, $data);
                            return $responseObj;
                        } catch (\Exception $e) {
                            throw new ApiException(
                                'Failed to process response: ' . $e->getMessage(),
                                ['response_data' => $data]
                            );
                        }
                    },
                    function ($exception) {
                        if ($exception instanceof GuzzleRequestException) {
                            throw new RequestException(
                                'Request failed: ' . $exception->getMessage(),
                                ['code' => $exception->getCode()]
                            );
                        }
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
        $baseUrl = sprintf(
            'https://%s',
            $this->config->getMarketplace()
        );
        
        return new Request(
            $operation->getMethod(),
            $baseUrl . $operation->getPath(),
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
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

    private function handleError(array $data, int $statusCode): void
    {
        $message = $data['Errors'][0]['Message'] ?? 'Unknown error';
        $code = $data['Errors'][0]['Code'] ?? 'Unknown';

        $this->logger->error('API error', [
            'status_code' => $statusCode,
            'error_code' => $code,
            'message' => $message
        ]);

        switch ($statusCode) {
            case 401:
            case 403:
                throw new AuthenticationException($message, ['code' => $code]);
            case 429:
                throw new ThrottleException($message, ['code' => $code]);
            case 400:
                throw new RequestException($message, ['code' => $code]);
            default:
                throw new ApiException($message, ['code' => $code]);
        }
    }

    private function createResponse(AbstractOperation $operation, array $data)
    {
        $responseClass = $operation->getResponseClass();
        return new $responseClass($data);
    }

    public function __destruct()
    {
        if (isset($this->connectionPool)) {
            $this->connectionPool->closeAll();
        }
    }

    private array $config;
    
    private const SECURITY_DEFAULTS = [
        'secure_storage_dir' => null,
        'encryption_key' => null,
        'tls_version' => 'TLS1.2',
        'verify_ssl' => true,
        'signature_version' => '2.0',
        'request_timeout' => 30,
        'connection_timeout' => 5
    ];

    public function __construct(array $config)
    {
        $this->config = array_merge(self::SECURITY_DEFAULTS, $config);
    }

    public function getSecureStorageDir(): ?string
    {
        return $this->config['secure_storage_dir'];
    }

    public function getEncryptionKey(): ?string
    {
        return $this->config['encryption_key'];
    }

    public function getTlsVersion(): string
    {
        return $this->config['tls_version'];
    }

    public function getVerifySsl(): bool
    {
        return $this->config['verify_ssl'];
    }

    public function getSignatureVersion(): string
    {
        return $this->config['signature_version'];
    }

    // In the Client class, add the following property
private Monitor $monitor;

// In the constructor, add:
public function __construct(
    Config $config,
    ?CacheItemPoolInterface $cache = null,
    ?LoggerInterface $logger = null
) {
    // ... existing initialization code ...
    
    $this->monitor = new Monitor($logger);
}

// In the sendAsync method, modify to use monitoring:
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
                'path' => $operation->getPath()
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
                        $this->monitor->endRequest($requestId, false, 'other');
                        throw new ApiException(
                            'Failed to process response: ' . $e->getMessage(),
                            ['response_data' => $data]
                        );
                    }
                },
                function ($exception) use ($requestId) {
                    if ($exception instanceof GuzzleRequestException) {
                        $this->monitor->endRequest($requestId, false, 'network');
                        throw new RequestException(
                            'Request failed: ' . $exception->getMessage(),
                            ['code' => $exception->getCode()]
                        );
                    }
                    $this->monitor->endRequest($requestId, false, 'other');
                    throw new ApiException('Unexpected error: ' . $exception->getMessage());
                }
            );
        },
        [],
        $operation->getMarketplace()
    );
}

// Add a new method to get monitoring metrics
public function getMetrics(): array
{
    return $this->monitor->getMetrics();
}
  
}