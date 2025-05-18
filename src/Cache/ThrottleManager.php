<?php

declare(strict_types=1);

namespace AmazonPaapi5\Cache;

use AmazonPaapi5\Exceptions\ThrottleException;

class ThrottleManager
{
    private float $delay;
    private int $maxRetries = 3;
    private array $queue = [];
    private float $lastRequestTime = 0.0;

    public function __construct(float $delay)
    {
        $this->delay = $delay;
    }

    public function throttle(callable $callback, array $args = []): mixed
    {
        $attempt = 0;
        while ($attempt < $this->maxRetries) {
            $now = microtime(true);
            if ($now - $this->lastRequestTime < $this->delay) {
                usleep((int)(($this->delay - ($now - $this->lastRequestTime)) * 1000000));
            }

            try {
                $this->lastRequestTime = microtime(true);
                return $callback(...$args);
            } catch (\Exception $e) {
                if ($e->getCode() === 429) {
                    $attempt++;
                    $delay = $this->delay * pow(2, $attempt); // Exponential backoff
                    usleep((int)($delay * 1000000));
                    continue;
                }
                throw new ThrottleException('Throttling failed: ' . $e->getMessage(), ['code' => $e->getCode()]);
            }
        }

        throw new ThrottleException('Max retry attempts reached', ['attempts' => $attempt]);
    }

    public function addToQueue(callable $callback, array $args = []): void
    {
        $this->queue[] = ['callback' => $callback, 'args' => $args];
    }

    public function processQueue(): array
    {
        $results = [];
        while ($this->queue) {
            $task = array_shift($this->queue);
            $results[] = $this->throttle($task['callback'], $task['args']);
        }
        return $results;
    }
}