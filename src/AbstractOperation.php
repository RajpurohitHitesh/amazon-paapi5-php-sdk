<?php

declare(strict_types=1);

namespace AmazonPaapi5;

use AmazonPaapi5\Models\Request\AbstractRequest;
use GuzzleHttp\Promise\PromiseInterface;

abstract class AbstractOperation
{
    protected string $path;
    protected string $method;
    protected mixed $request; // Changed from AbstractRequest to mixed
    protected Client $client;

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRequest(): mixed // Changed return type
    {
        return $this->request;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function getMarketplace(): ?string
    {
        return null;
    }

    abstract public function getResponseClass(): string;

    abstract public function executeAsync(): PromiseInterface;
}