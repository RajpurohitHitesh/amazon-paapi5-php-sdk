<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\Request;

abstract class AbstractRequest
{
    abstract public function toArray(): array;
}