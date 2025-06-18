<?php

declare(strict_types=1);

namespace AmazonPaapi5\Monitoring;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Monitor
{
    private LoggerInterface $logger;
    private array $metrics = [];
    private array $requestTimes = [];
    private int $totalRequests = 0;
    private int $successfulRequests = 0;
    private int $failedRequests = 0;
    private array $responseTimeHistory = [];
    private array $errorHistory = [];

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
        $this->initializeMetrics();
    }

    private function initializeMetrics(): void
    {
        $this->metrics = [
            'requests' => [
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
                'avg_response_time' => 0,
            ],
            'cache' => [
                'hits' => 0,
                'misses' => 0,
                'ratio' => 0,
            ],
            'errors' => [
                'auth' => 0,
                'throttle' => 0,
                'network' => 0,
                'validation' => 0,
                'other' => 0,
            ],
            'operations' => [],
        ];
    }

    public function startRequest(string $operation): string
    {
        $requestId = uniqid('req_', true);
        $this->requestTimes[$requestId] = microtime(true);
        $this->totalRequests++;
        $this->metrics['requests']['total']++;
        
        if (!isset($this->metrics['operations'][$operation])) {
            $this->metrics['operations'][$operation] = [
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
                'avg_time' => 0,
            ];
        }
        $this->metrics['operations'][$operation]['total']++;

        $this->logger->debug('Starting request', [
            'request_id' => $requestId,
            'operation' => $operation,
        ]);

        return $requestId;
    }

    public function endRequest(string $requestId, bool $success, ?string $errorType = null): void
    {
        if (!isset($this->requestTimes[$requestId])) {
            return;
        }

        $duration = microtime(true) - $this->requestTimes[$requestId];
        $this->responseTimeHistory[] = $duration;

        if ($success) {
            $this->successfulRequests++;
            $this->metrics['requests']['successful']++;
        } else {
            $this->failedRequests++;
            $this->metrics['requests']['failed']++;
            
            if ($errorType) {
                $this->metrics['errors'][$errorType] = ($this->metrics['errors'][$errorType] ?? 0) + 1;
                $this->errorHistory[] = [
                    'type' => $errorType,
                    'time' => time(),
                    'request_id' => $requestId,
                ];
            }
        }

        $this->metrics['requests']['avg_response_time'] = array_sum($this->responseTimeHistory) / count($this->responseTimeHistory);

        $this->logger->info('Request completed', [
            'request_id' => $requestId,
            'success' => $success,
            'duration' => $duration,
            'error_type' => $errorType,
        ]);

        unset($this->requestTimes[$requestId]);
    }

    public function recordCacheResult(bool $hit): void
    {
        if ($hit) {
            $this->metrics['cache']['hits']++;
        } else {
            $this->metrics['cache']['misses']++;
        }

        $total = $this->metrics['cache']['hits'] + $this->metrics['cache']['misses'];
        if ($total > 0) {
            $this->metrics['cache']['ratio'] = $this->metrics['cache']['hits'] / $total;
        }
    }

    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function getErrorHistory(int $limit = 10): array
    {
        return array_slice($this->errorHistory, -$limit);
    }

    public function getSuccessRate(): float
    {
        if ($this->totalRequests === 0) {
            return 0.0;
        }
        return ($this->successfulRequests / $this->totalRequests) * 100;
    }

    public function getAverageResponseTime(): float
    {
        if (empty($this->responseTimeHistory)) {
            return 0.0;
        }
        return array_sum($this->responseTimeHistory) / count($this->responseTimeHistory);
    }

    public function reset(): void
    {
        $this->initializeMetrics();
        $this->requestTimes = [];
        $this->totalRequests = 0;
        $this->successfulRequests = 0;
        $this->failedRequests = 0;
        $this->responseTimeHistory = [];
        $this->errorHistory = [];
    }
}