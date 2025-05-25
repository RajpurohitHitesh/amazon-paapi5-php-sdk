<?php

declare(strict_types=1);

namespace AmazonPaapi5\Exceptions;

class ApiException extends BaseException
{
    protected string $suggestion = 'Suggestion: Review API response for details and ensure request format is correct.';

    public function getErrorType(): string
    {
        return 'API_ERROR';
    }
}