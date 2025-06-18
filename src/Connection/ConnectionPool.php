<?php

declare(strict_types=1);

namespace AmazonPaapi5\Connection;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Client\ClientInterface;

class ConnectionPool
{
    private array $connections = [];
    private array $inUseConnections = [];
    private int $maxConnections;
    private array $config;

    public function __construct(array $config, int $maxConnections = 10)
    {
        $this->maxConnections = $maxConnections;
        $this->config = $config;
    }

    public function getConnection(): ClientInterface
    {
        // Try to get an available connection
        foreach ($this->connections as $key => $connection) {
            if (!isset($this->inUseConnections[$key])) {
                $this->inUseConnections[$key] = true;
                return $connection;
            }
        }

        // Create new connection if under limit
        if (count($this->connections) < $this->maxConnections) {
            $connection = $this->createNewConnection();
            $key = spl_object_hash($connection);
            $this->connections[$key] = $connection;
            $this->inUseConnections[$key] = true;
            return $connection;
        }

        // Wait for an available connection
        return $this->waitForAvailableConnection();
    }

    public function releaseConnection(ClientInterface $connection): void
    {
        $key = spl_object_hash($connection);
        unset($this->inUseConnections[$key]);
    }

    private function createNewConnection(): ClientInterface
    {
        return new GuzzleClient([
            'timeout' => $this->config['timeout'] ?? 30,
            'connect_timeout' => $this->config['connect_timeout'] ?? 5,
            'http_errors' => false,
            'verify' => true,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ],
            'curl' => [
                CURLOPT_TCP_KEEPALIVE => 1,
                CURLOPT_TCP_KEEPIDLE => 60,
                CURLOPT_TCP_NODELAY => 1
            ]
        ]);
    }

    private function waitForAvailableConnection(): ClientInterface
    {
        $maxWaitTime = 10; // Maximum wait time in seconds
        $startTime = microtime(true);

        while (microtime(true) - $startTime < $maxWaitTime) {
            foreach ($this->connections as $key => $connection) {
                if (!isset($this->inUseConnections[$key])) {
                    $this->inUseConnections[$key] = true;
                    return $connection;
                }
            }
            usleep(100000); // Sleep for 100ms
        }

        throw new \RuntimeException('No connection available after waiting');
    }

    public function closeAll(): void
    {
        foreach ($this->connections as $connection) {
            // Close any active connections
            if (method_exists($connection, 'close')) {
                $connection->close();
            }
        }
        $this->connections = [];
        $this->inUseConnections = [];
    }
}