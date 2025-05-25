<?php

declare(strict_types=1);

namespace AmazonPaapi5\Exceptions;

class CacheException extends \Exception
{
    public function __construct(string $message, array $metadata = [])
    {
        parent::__construct($message . ' Suggestion: Check cache directory permissions and configuration.', 0, null);
        $this->metadata = $metadata;
    }

    private array $metadata;

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}