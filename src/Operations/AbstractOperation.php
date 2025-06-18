<?php

declare(strict_types=1);

namespace AmazonPaapi5\Operations;

use AmazonPaapi5\Client;

abstract class AbstractOperation
{
    protected string $path;
    protected string $method;
    protected $request;
    protected Client $client;

    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRequest()
    {
        return $this->request;
    }

    // Add missing methods
    public function getMarketplace(): string
    {
        // This should return the marketplace from the client's config
        if (isset($this->client)) {
            return $this->client->getConfig()->getMarketplace();
        }
        return 'www.amazon.com'; // fallback
    }

    abstract public function getResponseClass(): string;
    
    abstract public function executeAsync(): \GuzzleHttp\Promise\PromiseInterface;
}