<?php

declare(strict_types=1);

namespace AmazonPaapi5;

class Config
{
    private array $config;
    private static array $requiredFields = [
        'access_key',
        'secret_key',
        'region',
        'marketplace',
        'partner_tag',
        'encryption_key'
    ];

    public function __construct(array $config)
    {
        $this->validateConfig($config);
        $this->config = array_merge([
            'cache_dir' => sys_get_temp_dir() . '/amazon-paapi5-cache',
            'cache_ttl' => 3600,
            'throttle_delay' => 1.0,
            'max_retries' => 3
        ], $config);
    }

    private function validateConfig(array $config): void
    {
        foreach (self::$requiredFields as $field) {
            if (!isset($config[$field])) {
                throw new \InvalidArgumentException("Missing required config field: {$field}");
            }
        }
    }

    public function getAccessKey(): string
    {
        return $this->config['access_key'];
    }

    public function getSecretKey(): string
    {
        return $this->config['secret_key'];
    }

    public function getRegion(): string
    {
        return $this->config['region'];
    }

    public function getMarketplace(): string
    {
        return $this->config['marketplace'];
    }

    public function getPartnerTag(): string
    {
        return $this->config['partner_tag'];
    }

    public function getEncryptionKey(): string
    {
        return $this->config['encryption_key'];
    }

    public function getCacheDir(): string
    {
        return $this->config['cache_dir'];
    }

    public function getCacheTtl(): int
    {
        return $this->config['cache_ttl'];
    }

    public function getThrottleDelay(): float
    {
        return $this->config['throttle_delay'];
    }

    public function getMaxRetries(): int
    {
        return $this->config['max_retries'];
    }
}