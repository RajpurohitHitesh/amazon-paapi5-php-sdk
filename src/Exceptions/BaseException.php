<?php

declare(strict_types=1);

namespace AmazonPaapi5\Exceptions;

abstract class BaseException extends \Exception
{
    protected array $metadata = [];
    protected string $suggestion = '';

    public function __construct(string $message, array $metadata = [], ?\Throwable $previous = null)
    {
        $this->metadata = $metadata;
        parent::__construct($this->formatMessage($message), 0, $previous);
    }

    protected function formatMessage(string $message): string
    {
        return trim($message . ' ' . $this->suggestion);
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    abstract public function getErrorType(): string;
}