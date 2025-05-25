<?php

declare(strict_types=1);

namespace Tests;

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
            'cache_dir' => $this->tempDir,
            'encryption_key' => str_repeat('x', 32)
        ]);

        $cache = new AdvancedCache($this->tempDir);
        $this->client = new Client($this->config, $cache);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->tempDir . '/*'));
        rmdir($this->tempDir);
    }

    public function testSearchItemsOperation(): void
    {
        $operation = new SearchItems();
        $operation->setKeywords('test product');

        $promise = $this->client->sendAsync($operation);
        
        $this->assertInstanceOf(\GuzzleHttp\Promise\PromiseInterface::class, $promise);
    }

    public function testInvalidCredentials(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $operation = new SearchItems();
        $operation->setKeywords('test product');

        $promise = $this->client->sendAsync($operation);
        $promise->wait();
    }

    public function testCaching(): void
    {
        $operation = new SearchItems();
        $operation->setKeywords('test product');

        // First request
        $promise1 = $this->client->sendAsync($operation);
        $result1 = $promise1->wait();

        // Second request (should hit cache)
        $promise2 = $this->client->sendAsync($operation);
        $result2 = $promise2->wait();

        $this->assertEquals($result1, $result2);
    }
}