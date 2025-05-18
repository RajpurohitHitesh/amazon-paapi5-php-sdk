<?php

declare(strict_types=1);

namespace AmazonPaapi5\Operations;

use AmazonPaapi5\Models\Request\GetVariationsRequest;
use GuzzleHttp\Promise\PromiseInterface;

class GetVariations extends AbstractOperation
{
    protected string $path = '/paapi5/getvariations';
    protected string $method = 'POST';

    public function __construct(GetVariationsRequest $request)
    {
        $this->request = $request;
    }

    public function executeAsync(): PromiseInterface
    {
        return $this->client->sendAsync($this);
    }
}