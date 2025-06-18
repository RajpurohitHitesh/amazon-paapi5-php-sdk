<?php

declare(strict_types=1);

namespace AmazonPaapi5\Exceptions;

class ThrottleException extends BaseException
{
    protected string $suggestion = 'Suggestion: Reduce request frequency, increase throttle delay, or implement request queuing.';

    public function getErrorType(): string
    {
        return 'THROTTLE_ERROR';
    }
}