<?php

declare(strict_types=1);

namespace AmazonPaapi5\Queue;

use AmazonPaapi5\AbstractOperation;
use Psr\Log\LoggerInterface;

class RequestQueueOptimizer
{
    private array $queue = [];
    private array $priorities = [];
    private LoggerInterface $logger;
    private int $maxQueueSize;

    public function __construct(LoggerInterface $logger, int $maxQueueSize = 1000)
    {
        $this->logger = $logger;
        $this->maxQueueSize = $maxQueueSize;
    }

    public function addRequest(AbstractOperation $operation, int $priority = 1): void
    {
        if (count($this->queue) >= $this->maxQueueSize) {
            $this->logger->warning('Queue is full, removing oldest request');
            array_shift($this->queue);
            array_shift($this->priorities);
        }

        $this->queue[] = $operation;
        $this->priorities[] = $priority;
    }

    public function optimizeQueue(): array
    {
        if (empty($this->queue)) {
            return [];
        }

        // Sort by priority
        array_multisort($this->priorities, SORT_DESC, $this->queue);

        // Group similar operations
        $grouped = $this->groupSimilarOperations();

        // Prepare optimized batches
        return $this->prepareBatches($grouped);
    }

    private function groupSimilarOperations(): array
    {
        $grouped = [];
        foreach ($this->queue as $index => $operation) {
            $type = get_class($operation);
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = [
                'operation' => $operation,
                'priority' => $this->priorities[$index]
            ];
        }
        return $grouped;
    }

    private function prepareBatches(array $grouped): array
    {
        $batches = [];
        foreach ($grouped as $type => $operations) {
            // Sort operations within each type by priority
            usort($operations, function ($a, $b) {
                return $b['priority'] - $a['priority'];
            });

            // Extract just the operations
            $batches[$type] = array_column($operations, 'operation');
        }

        // Clear the queue after processing
        $this->queue = [];
        $this->priorities = [];

        return $batches;
    }

    public function getQueueSize(): int
    {
        return count($this->queue);
    }

    public function clearQueue(): void
    {
        $this->queue = [];
        $this->priorities = [];
    }
}