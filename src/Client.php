<?php

declare(strict_types=1);

namespace AmazonPaapi5;

use AmazonPaapi5\Auth\AwsV4Signer;
use AmazonPaapi5\Security\CredentialManager;
use AmazonPaapi5\Cache\AdvancedCache;
use AmazonPaapi5\Cache\ThrottleManager;
use AmazonPaapi5\Exceptions\ApiException;
use AmazonPaapi5\Exceptions\AuthenticationException;
use AmazonPaapi5\Exceptions\RequestException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Promise\PromiseInterface;
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
    private LoggerInterface $logger;

    public function __construct(
        Config $config,
        ?CacheItemPoolInterface $cache = null,
        ?LoggerInterface $logger = null
    ) {
        $this->config = $config;
        $this->credentialManager = new CredentialManager($config);
        $this->signer = new AwsV4Signer(
            $this->credentialManager->getAccessKey(),
            $this->credentialManager->getSecretKey(),
            $config->getRegion()
        );
        
        $this->httpClient = new GuzzleClient([
            'verify' => true,
            'http_errors' => false,
            'connect_timeout' => 5,
            'timeout' => 30
        ]);
        
        $this->cache = $cache ?? new AdvancedCache($config->getCacheDir());
        $this->throttleManager = new ThrottleManager($config->getThrottleDelay());
        $this->logger = $logger ?? new NullLogger();
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

    private function createRequest(AbstractOperation $operation): RequestInterface
    {
        return new \GuzzleHttp\Psr7\Request(
            $operation->getMethod(),
            $operation->getPath(),
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
}