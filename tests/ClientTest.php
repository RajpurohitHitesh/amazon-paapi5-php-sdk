<?php

declare(strict_types=1);

namespace AmazonPaapi5\Tests;

use PHPUnit\Framework\TestCase;
use AmazonPaapi5\Client;
use AmazonPaapi5\Config;
use AmazonPaapi5\Cache\AdvancedCache;
use AmazonPaapi5\Exceptions\ApiException;
use AmazonPaapi5\Operations\SearchItems;
use AmazonPaapi5\Models\Request\SearchItemsRequest;

class ClientTest extends TestCase
{
    private Client $client;
    private Config $config;
    private string $tempDir;
    private SearchItemsRequest $searchRequest;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/amazon-paapi5-tests-' . uniqid();
        mkdir($this->tempDir);

        $this->config = new Config([
            'access_key' => str_repeat('A', 20) . 'TESTKEY',
            'secret_key' => str_repeat('B', 30) . 'TESTSECRET',
            'region' => 'us-east-1',
            'marketplace' => 'www.amazon.com',
            'partner_tag' => 'test-tag',
            'encryption_key' => str_repeat('x', 32),
            'cache_dir' => $this->tempDir
        ]);

        $cache = new AdvancedCache($this->tempDir);
        $this->client = new Client($this->config, $cache);

        $this->searchRequest = new SearchItemsRequest();
        $this->searchRequest->setPartnerTag($this->config->getPartnerTag())
            ->setKeywords('test product')
            ->setResources(['Images.Primary.Small', 'ItemInfo.Title']);
    }

    public function testSearchItemsOperation(): void
    {
        $this->expectNotToPerformAssertions();
        
        $operation = new SearchItems($this->searchRequest);
        $this->client->execute($operation);
    }

        public function testInvalidCredentials(): void
    {
        $this->expectException(AuthenticationException::class);
        
        // Invalid credentials config
        $invalidConfig = new Config([
            'access_key' => 'INVALID',
            'secret_key' => 'INVALID',
            'region' => 'us-east-1',
            'marketplace' => 'webservices.amazon.com',
            'partner_tag' => 'test-tag'
        ]);
        
        $client = new Client($invalidConfig);
        $operation = new SearchItems($this->searchRequest);
        $promise = $client->execute($operation);
        $promise->wait();
    }

    public function testCaching(): void
    {
        $this->expectNotToPerformAssertions();
        
        $operation = new SearchItems($this->searchRequest);
        
        // First request
        $this->client->execute($operation);
        
        // Second request (should hit cache)
        $this->client->execute($operation);
    }

    protected function tearDown(): void
    {
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
}