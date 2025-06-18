<?php

declare(strict_types=1);

namespace AmazonPaapi5\Operations;

use AmazonPaapi5\AbstractOperation;
use AmazonPaapi5\Models\Request\SearchItemsRequest;
use GuzzleHttp\Promise\PromiseInterface;

class SearchItems extends AbstractOperation
{
    protected string $path = '/paapi5/searchitems';
    protected string $method = 'POST';

    public function __construct(SearchItemsRequest $request)
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
        return \AmazonPaapi5\Models\Response\SearchItemsResponse::class;
    }
}