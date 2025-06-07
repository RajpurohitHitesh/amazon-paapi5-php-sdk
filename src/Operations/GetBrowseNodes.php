<?php

declare(strict_types=1);

namespace AmazonPaapi5\Operations;

use AmazonPaapi5\AbstractOperation;
use AmazonPaapi5\Models\Request\GetBrowseNodesRequest;
use GuzzleHttp\Promise\PromiseInterface;

class GetBrowseNodes extends AbstractOperation
{
    protected string $path = '/paapi5/getbrowsenodes';
    protected string $method = 'POST';

    public function __construct(GetBrowseNodesRequest $request)
    {
        $this->request = $request;
    }

    public function executeAsync(): PromiseInterface
    {
        return $this->client->sendAsync($this);
    }

    public function getResponseClass(): string
    {
        return \AmazonPaapi5\Models\Response\GetBrowseNodesResponse::class;
    }
}