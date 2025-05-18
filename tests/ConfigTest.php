<?php

declare(strict_types=1);

namespace AmazonPaapi5\Tests;

use AmazonPaapi5\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testConfigInitialization(): void
    {
        $config = new Config(
            'test-access-key',
            'test-secret-key',
            'test-partner-tag',
            'test-encryption-key'
        );

        $this->assertEquals('test-access-key', $config->getAccessKey());
        $this->assertEquals('test-secret-key', $config->getSecretKey());
        $this->assertEquals('test-partner-tag', $config->getPartnerTag());
        $this->assertEquals('test-encryption-key', $config->getEncryptionKey());
        $this->assertEquals('www.amazon.com', $config->getMarketplace());
    }
}