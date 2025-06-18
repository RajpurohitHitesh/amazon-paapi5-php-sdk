<?php

declare(strict_types=1);

namespace AmazonPaapi5\Operations;

use AmazonPaapi5\AbstractOperation;
use AmazonPaapi5\Models\Request\GetItemsRequest;
use GuzzleHttp\Promise\PromiseInterface;

class GetItems extends AbstractOperation
{
    protected string $path = '/paapi5/getitems';
    protected string $method = 'POST';

    public function __construct(GetItemsRequest $request)
    {
        $this->request = $request;
    }

    public function executeAsync(): PromiseInterface
    {
        return $this->client->sendAsync($this);
    }

    // Add missing method
    public function getResponseClass(): string
    {
        return \AmazonPaapi5\Models\Response\GetItemsResponse::class;
    }
}