<?php

declare(strict_types=1);

namespace AmazonPaapi5\Cache;

use AmazonPaapi5\Exceptions\ThrottleException;
use GuzzleHttp\Promise\PromiseInterface;

class ThrottleManager
{
    private float $delay;
    private int $maxRetries;
    private float $jitter;
    private array $queue = [];
    private float $lastRequestTime = 0.0;
    private array $marketplaceRates = [];

    public function __construct(float $delay = 1.0, int $maxRetries = 3, float $jitter = 0.1)
    {
        $this->delay = $delay;
        $this->maxRetries = $maxRetries;
        $this->jitter = $jitter;
    }

    public function setMarketplaceRate(string $marketplace, int $requestsPerSecond): void
    {
        $this->marketplaceRates[$marketplace] = 1 / $requestsPerSecond;
    }

    /**
     * @return PromiseInterface
     */
    public function throttle(callable $callback, array $args = [], ?string $marketplace = null): PromiseInterface
    {
        $delay = $marketplace && isset($this->marketplaceRates[$marketplace])
            ? $this->marketplaceRates[$marketplace]
            : $this->delay;

        $attempt = 0;
        $lastError = null;

        while ($attempt < $this->maxRetries) {
            try {
                $this->wait($delay);
                $result = $callback(...$args);
                $this->lastRequestTime = microtime(true);
                return $result;
            } catch (\Exception $e) {
                $lastError = $e;
                if ($e->getCode() === 429) {
                    $attempt++;
                    $delay = $this->calculateBackoffDelay($attempt);
                    continue;
                }
                throw $e;
            }
        }

        throw new ThrottleException(
            'Max retry attempts reached',
            [
                'attempts' => $attempt,
                'last_error' => $lastError ? $lastError->getMessage() : null
            ]
        );
    }

    public function addToQueue(callable $callback, array $args = [], ?string $marketplace = null): void
    {
        $this->queue[] = [
            'callback' => $callback,
            'args' => $args,
            'marketplace' => $marketplace
        ];
    }

    /**
     * @return PromiseInterface[]
     */
    public function processQueue(): array
    {
        $results = [];
        while ($this->queue) {
            $task = array_shift($this->queue);
            $results[] = $this->throttle(
                $task['callback'],
                $task['args'],
                $task['marketplace']
            );
        }
        return $results;
    }

    private function wait(float $delay): void
    {
        $now = microtime(true);
        $waitTime = ($this->lastRequestTime + $delay) - $now;
        
        if ($waitTime > 0) {
            $jitterAmount = $this->jitter * (2 * (mt_rand() / mt_getrandmax()) - 1);
            usleep((int)(($waitTime + $jitterAmount) * 1000000));
        }
    }

    private function calculateBackoffDelay(int $attempt): float
    {
        return $this->delay * pow(2, $attempt - 1) * (1 + $this->jitter * (mt_rand() / mt_getrandmax()));
    }
}