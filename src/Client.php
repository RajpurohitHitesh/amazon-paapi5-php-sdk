<?php

declare(strict_types=1);

namespace AmazonPaapi5;

use AmazonPaapi5\Auth\AwsV4Signer;
use AmazonPaapi5\Auth\CredentialManager;
use AmazonPaapi5\Cache\FileCache;
use AmazonPaapi5\Cache\ThrottleManager;
use AmazonPaapi5\Exceptions\ApiException;
use AmazonPaapi5\Exceptions\AuthenticationException;
use AmazonPaapi5\Exceptions\RequestException;
use AmazonPaapi5\Exceptions\ThrottleException;
use AmazonPaapi5\Models\Response\GetBrowseNodesResponse;
use AmazonPaapi5\Models\Response\GetItemsResponse;
use AmazonPaapi5\Models\Response\GetVariationsResponse;
use AmazonPaapi5\Models\Response\SearchItemsResponse;
use AmazonPaapi5\Operations\AbstractOperation;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Cache\CacheItemPoolInterface;

class Client
{
    private Config $config;
    private GuzzleClient $httpClient;
    private AwsV4Signer $signer;
    private CredentialManager $credentialManager;
    private ThrottleManager $throttleManager;
    private ?CacheItemPoolInterface $cache;

    public function __construct(Config $config, ?CacheItemPoolInterface $cache = null)
    {
        $this->config = $config;
        $this->credentialManager = new CredentialManager(
            $config->getAccessKey(),
            $config->getSecretKey(),
            $config->getEncryptionKey()
        );
        $this->signer = new AwsV4Signer(
            $this->credentialManager->getAccessKey(),
            $this->credentialManager->getSecretKey(),
            $config->getRegion()
        );
        $this->httpClient = new GuzzleClient([
            'base_uri' => 'https://' . $config->getHost(),
            'headers' => ['Accept-Encoding' => 'gzip'],
            'http_errors' => false,
        ]);
        $this->throttleManager = new ThrottleManager($config->getThrottleDelay());
        $this->cache = $cache ?? $config->getCachePool() ?? new FileCache();
    }

    public function sendAsync(AbstractOperation $operation): PromiseInterface
    {
        $operation->setClient($this);
        $cacheKey = $this->generateCacheKey($operation);

        if ($this->cache->hasItem($cacheKey)) {
            $item = $this->cache->getItem($cacheKey);
            if ($item->isHit()) {
                return \GuzzleHttp\Promise\promise_for($this->createResponse($operation, $item->get()));
            }
        }

        return $this->throttleManager->throttle(function () use ($operation, $cacheKey) {
            $request = new \GuzzleHttp\Psr7\Request(
                $operation->getMethod(),
                $operation->getPath(),
                [],
                json_encode($operation->getRequest()->toArray())
            );

            $signedRequest = $this->signer->signRequest(
                $request,
                $operation->getPath(),
                json_encode($operation->getRequest()->toArray())
            );

            return $this->httpClient->sendAsync($signedRequest)->then(
                function ($response) use ($operation, $cacheKey) {
                    $data = json_decode((string)$response->getBody(), true);
                    if ($response->getStatusCode() >= 400) {
                        $this->handleError($data, $response->getStatusCode());
                    }

                    // Invalidate cache for this key before storing new data
                    $this->cache->deleteItem($cacheKey);

                    $responseObj = $this->createResponse($operation, $data);
                    $cacheItem = $this->cache->getItem($cacheKey);
                    $cacheItem->set($data)->expiresAfter($this->config->getCacheTtl());
                    $this->cache->save($cacheItem);

                    return $responseObj;
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
        });
    }

    private function createResponse(AbstractOperation $operation, array $data)
    {
        switch (get_class($operation)) {
            case \AmazonPaapi5\Operations\SearchItems::class:
                return new SearchItemsResponse($data);
            case \AmazonPaapi5\Operations\GetItems::class:
                return new GetItemsResponse($data);
            case \AmazonPaapi5\Operations\GetVariations::class:
                return new GetVariationsResponse($data);
            case \AmazonPaapi5\Operations\GetBrowseNodes::class:
                return new GetBrowseNodesResponse($data);
            default:
                throw new ApiException('Unsupported operation');
        }
    }

    private function handleError(array $data, int $statusCode): void
    {
        $message = $data['Errors'][0]['Message'] ?? 'Unknown error';
        $code = $data['Errors'][0]['Code'] ?? 'Unknown';

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

    private function generateCacheKey(AbstractOperation $operation): string
    {
        return hash('sha256',
            get_class($operation) . ':' .
            json_encode($operation->getRequest()->toArray()) . ':' .
            $this->config->getMarketplace()
        );
    }

    public function executeBatch(array $operations): array
    {
        $promises = [];
        foreach ($operations as $operation) {
            $promises[] = $this->sendAsync($operation);
        }
        return \GuzzleHttp\Promise\Utils::unwrap($promises);
    }

    public function getConfig(): Config
    {
        return $this->config;
    }
}
