<?php

declare(strict_types=1);

namespace AmazonPaapi5\Batch;

use AmazonPaapi5\Client;
use AmazonPaapi5\AbstractOperation;
use GuzzleHttp\Promise;
use Psr\Log\LoggerInterface;
use Throwable;

class BatchProcessor
{
    private Client $client;
    private LoggerInterface $logger;
    private int $maxBatchSize;
    private int $concurrentRequests;

    public function __construct(
        Client $client,
        LoggerInterface $logger,
        int $maxBatchSize = 10,
        int $concurrentRequests = 5
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->maxBatchSize = $maxBatchSize;
        $this->concurrentRequests = $concurrentRequests;
    }

    /**
     * Process multiple operations in optimized batches
     *
     * @param AbstractOperation[] $operations
     * @return array
     */
    public function processBatch(array $operations): array
    {
        $results = [];
        $batches = array_chunk($operations, $this->maxBatchSize);
        
        foreach ($batches as $batchIndex => $batch) {
            try {
                $batchResults = $this->processBatchConcurrently($batch, $batchIndex);
                $results = array_merge($results, $batchResults);
            } catch (Throwable $e) {
                $this->logger->error('Batch processing error', [
                    'batch_index' => $batchIndex,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }

        return $results;
    }

    private function processBatchConcurrently(array $operations, int $batchIndex): array
    {
        $promises = [];
        $results = [];

        // Create promises for each operation
        foreach ($operations as $index => $operation) {
            $promises[$index] = $this->client->sendAsync($operation)
                ->then(
                    function ($response) use ($index) {
                        return ['success' => true, 'index' => $index, 'data' => $response];
                    },
                    function ($reason) use ($index) {
                        return ['success' => false, 'index' => $index, 'error' => $reason];
                    }
                );

            // If we've reached concurrent limit or last item, wait for completion
            if (count($promises) >= $this->concurrentRequests || $index === count($operations) - 1) {
                $batchResults = Promise\Utils::settle($promises)->wait();
                
                foreach ($batchResults as $result) {
                    if ($result['state'] === 'fulfilled') {
                        $value = $result['value'];
                        $results[$value['index']] = $value['data'];
                    } else {
                        $this->logger->warning('Operation failed', [
                            'batch_index' => $batchIndex,
                            'operation_index' => $result['value']['index'],
                            'error' => $result['reason']->getMessage()
                        ]);
                        $results[$result['value']['index']] = null;
                    }
                }
                
                $promises = []; // Reset promises array for next batch
            }
        }

        return $results;
    }

    public function setMaxBatchSize(int $size): void
    {
        $this->maxBatchSize = $size;
    }

    public function setConcurrentRequests(int $count): void
    {
        $this->concurrentRequests = $count;
    }
}