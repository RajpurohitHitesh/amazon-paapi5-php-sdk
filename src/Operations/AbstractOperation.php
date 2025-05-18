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
}