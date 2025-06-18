<?php

declare(strict_types=1);

namespace AmazonPaapi5;

use AmazonPaapi5\Exceptions\ConfigException;

class Config
{
    private array $config;

    private const REQUIRED_FIELDS = [
        'access_key',
        'secret_key',
        'partner_tag',
        'marketplace'
    ];

    private const SECURITY_DEFAULTS = [
        'secure_storage_dir' => null,
        'encryption_key' => null,
        'tls_version' => 'TLS1.2',
        'verify_ssl' => true,
        'signature_version' => '2.0',
        'request_timeout' => 30,
        'connection_timeout' => 5
    ];

    private const CACHE_DEFAULTS = [
        'cache_dir' => null,
        'cache_ttl' => 3600
    ];

    private const THROTTLE_DEFAULTS = [
        'throttle_delay' => 1.0,
        'max_retries' => 3
    ];

    public function __construct(array $config)
    {
        $this->validateRequiredFields($config);
        
        $this->config = array_merge(
            self::SECURITY_DEFAULTS,
            self::CACHE_DEFAULTS,
            self::THROTTLE_DEFAULTS,
            $config
        );

        // Set default cache directory if not provided
        if ($this->config['cache_dir'] === null) {
            $this->config['cache_dir'] = sys_get_temp_dir() . '/amazon-paapi5-cache';
        }
    }

    private function validateRequiredFields(array $config): void
    {
        $missing = [];
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!isset($config[$field]) || empty($config[$field])) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new ConfigException(
                'Missing required configuration fields: ' . implode(', ', $missing)
            );
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

    public function getPartnerTag(): string
    {
        return $this->config['partner_tag'];
    }

    public function getMarketplace(): string
    {
        return $this->config['marketplace'];
    }

    public function getRegion(): string
    {
        return $this->config['region'] ?? Marketplace::getRegion($this->getMarketplace());
    }

    public function getCacheDir(): string
    {
        return $this->config['cache_dir'];
    }

    public function getCacheTtl(): int
    {
        return (int)$this->config['cache_ttl'];
    }

    public function getThrottleDelay(): float
    {
        return (float)$this->config['throttle_delay'];
    }

    public function getMaxRetries(): int
    {
        return (int)$this->config['max_retries'];
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
        return (bool)$this->config['verify_ssl'];
    }

    public function getSignatureVersion(): string
    {
        return $this->config['signature_version'];
    }

    public function getRequestTimeout(): int
    {
        return (int)$this->config['request_timeout'];
    }

    public function getConnectionTimeout(): int
    {
        return (int)$this->config['connection_timeout'];
    }

    public function toArray(): array
    {
        return $this->config;
    }

    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}