<?php

declare(strict_types=1);

namespace AmazonPaapi5;

use Psr\Cache\CacheItemPoolInterface;

class Config
{
    private string $accessKey;
    private string $secretKey;
    private string $partnerTag;
    private string $encryptionKey;
    private string $marketplace = 'www.amazon.com';
    private string $region = 'us-east-1';
    private string $host = 'webservices.amazon.com';
    private float $throttleDelay = 1.0; // seconds
    private int $cacheTtl = 3600; // 1 hour
    private ?CacheItemPoolInterface $cachePool;

    public function __construct(
        string $accessKey,
        string $secretKey,
        string $partnerTag,
        string $encryptionKey,
        ?CacheItemPoolInterface $cachePool = null
    ) {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->partnerTag = $partnerTag;
        $this->encryptionKey = $encryptionKey;
        $this->cachePool = $cachePool;
    }

    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getPartnerTag(): string
    {
        return $this->partnerTag;
    }

    public function getEncryptionKey(): string
    {
        return $this->encryptionKey;
    }

    public function setMarketplace(string $marketplace): self
    {
        $this->marketplace = $marketplace;
        $this->host = Marketplace::getHost($marketplace);
        $this->region = Marketplace::getRegion($marketplace);
        return $this;
    }

    public function getMarketplace(): string
    {
        return $this->marketplace;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setThrottleDelay(float $delay): self
    {
        $this->throttleDelay = max(0.1, $delay);
        return $this;
    }

    public function getThrottleDelay(): float
    {
        return $this->throttleDelay;
    }

    public function setCacheTtl(int $ttl): self
    {
        $this->cacheTtl = max(60, $ttl);
        return $this;
    }

    public function getCacheTtl(): int
    {
        return $this->cacheTtl;
    }

    public function getCachePool(): ?CacheItemPoolInterface
    {
        return $this->cachePool;
    }
}