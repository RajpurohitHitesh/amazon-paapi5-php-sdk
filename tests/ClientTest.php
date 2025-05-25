<?php

declare(strict_types=1);

namespace AmazonPaapi5\Tests;

use PHPUnit\Framework\TestCase;
use AmazonPaapi5\Client;
use AmazonPaapi5\Config;
use AmazonPaapi5\Cache\AdvancedCache;
use AmazonPaapi5\Exceptions\ApiException;
use AmazonPaapi5\Operations\SearchItems;

class ClientTest extends TestCase
{
    private Client $client;
    private Config $config;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/amazon-paapi5-tests-' . uniqid();
        mkdir($this->tempDir);

        $this->config = new Config([
            'access_key' => 'test_access_key',
            'secret_key' => 'test_secret_key',
            'region' => 'us-east-1',
            'marketplace' => 'www.amazon.com',
            'partner_tag' => 'test-tag',
            'encryption_key' => str_repeat('x', 32),
            'cache_dir' => $this->tempDir
        ]);

        $cache = new AdvancedCache($this->tempDir);
        $this->client = new Client($this->config, $cache);
    }

    protected function tearDown(): void
    {
        // Clean up test directory
        if (is_dir($this->tempDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($this->tempDir);
        }
    }

    public function testSearchItemsOperation(): void
    {
        $this->expectNotToPerformAssertions();
        
        $operation = new SearchItems();
        $operation->setKeywords('test product');
        
        $this->client->sendAsync($operation);
    }

    public function testInvalidCredentials(): void
    {
        $this->expectException(ApiException::class);
        
        $operation = new SearchItems();
        $operation->setKeywords('test product');
        
        $promise = $this->client->sendAsync($operation);
        $promise->wait();
    }

    public function testCaching(): void
    {
        $this->expectNotToPerformAssertions();
        
        $operation = new SearchItems();
        $operation->setKeywords('test product');
        
        // First request
        $this->client->sendAsync($operation);
        
        // Second request (should hit cache)
        $this->client->sendAsync($operation);
    }
}