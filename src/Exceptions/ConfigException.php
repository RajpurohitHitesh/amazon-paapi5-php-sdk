<?php

declare(strict_types=1);

namespace AmazonPaapi5\Exceptions;

class ConfigException extends \Exception
{
    public function __construct(string $message, array $metadata = [])
    {
        parent::__construct(
            $message . ' Suggestion: Check your configuration parameters and ensure all required fields are provided.',
            0,
            null
        );
        $this->metadata = $metadata;
    }

    private array $metadata;

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}