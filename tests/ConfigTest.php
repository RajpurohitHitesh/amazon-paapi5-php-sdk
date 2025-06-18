<?php

declare(strict_types=1);

namespace AmazonPaapi5\Tests;

use PHPUnit\Framework\TestCase;
use AmazonPaapi5\Config;
use AmazonPaapi5\Exceptions\ConfigException;

class ConfigTest extends TestCase
{
    private array $validConfig;

    protected function setUp(): void
    {
        $this->validConfig = [
            'access_key' => 'test_access_key',
            'secret_key' => 'test_secret_key',
            'region' => 'us-east-1',
            'marketplace' => 'www.amazon.com',
            'partner_tag' => 'test-tag',
            'encryption_key' => str_repeat('x', 32)
        ];
    }

    public function testConfigInitialization(): void
    {
        $config = new Config($this->validConfig);
        
        $this->assertEquals('test_access_key', $config->getAccessKey());
        $this->assertEquals('test_secret_key', $config->getSecretKey());
        $this->assertEquals('us-east-1', $config->getRegion());
    }

    public function testMissingRequiredField(): void
    {
        $this->expectException(ConfigException::class);
        
        $invalidConfig = $this->validConfig;
        unset($invalidConfig['access_key']);
        
        new Config($invalidConfig);
    }

    public function testEmptyRequiredField(): void
    {
        $this->expectException(ConfigException::class);
        
        $invalidConfig = $this->validConfig;
        $invalidConfig['access_key'] = '';
        
        new Config($invalidConfig);
    }

    public function testDefaultValues(): void
    {
        $config = new Config($this->validConfig);
        
        $this->assertIsInt($config->getCacheTtl());
        $this->assertIsFloat($config->getThrottleDelay());
        $this->assertIsInt($config->getMaxRetries());
        $this->assertIsString($config->getCacheDir());
    }
}