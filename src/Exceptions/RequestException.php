<?php

declare(strict_types=1);

namespace AmazonPaapi5\Exceptions;

class RequestException extends \Exception
{
    public function __construct(string $message, array $metadata = [])
    {
        parent::__construct($message . ' Suggestion: Check request parameters and try again.', 0, null);
        $this->metadata = $metadata;
    }

    private array $metadata;

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}