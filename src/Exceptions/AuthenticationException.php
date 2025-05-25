<?php

declare(strict_types=1);

namespace AmazonPaapi5\Exceptions;

class AuthenticationException extends BaseException
{
    protected string $suggestion = 'Suggestion: Verify your AWS credentials, region settings, and ensure proper encryption.';

    public function getErrorType(): string
    {
        return 'AUTHENTICATION_ERROR';
    }
}