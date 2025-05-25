<?php

declare(strict_types=1);

namespace AmazonPaapi5\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;
use AmazonPaapi5\Exceptions\CacheException;

class AdvancedCache implements CacheItemPoolInterface
{
    private string $cacheDir;
    private int $defaultTtl;
    private array $items = [];

    public function __construct(string $cacheDir, int $defaultTtl = 3600)
    {
        $this->cacheDir = rtrim($cacheDir, '/');
        $this->defaultTtl = $defaultTtl;
        
        if (!is_dir($this->cacheDir)) {
            if (!mkdir($this->cacheDir, 0777, true)) {
                throw new CacheException("Unable to create cache directory: {$this->cacheDir}");
            }
        }
        
        if (!is_writable($this->cacheDir)) {
            throw new CacheException("Cache directory is not writable: {$this->cacheDir}");
        }
    }

    public function getItem($key): CacheItemInterface
    {
        $this->validateKey($key);
        
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        $path = $this->getFilePath($key);
        
        if (!file_exists($path)) {
            return new CacheItem($key);
        }

        $data = $this->readFile($path);
        if ($data === false || !isset($data['value'], $data['expiry'])) {
            return new CacheItem($key);
        }

        if ($data['expiry'] !== null && $data['expiry'] < time()) {
            $this->deleteItem($key);
            return new CacheItem($key);
        }

        $item = new CacheItem($key, $data['value'], true);
        if ($data['expiry']) {
            $item->expiresAt(\DateTime::createFromFormat('U', (string)$data['expiry']));
        }

        $this->items[$key] = $item;
        return $item;
    }

    public function getItems(array $keys = []): array
    {
        return array_combine($keys, array_map([$this, 'getItem'], $keys));
    }

    public function hasItem($key): bool
    {
        return $this->getItem($key)->isHit();
    }

    public function clear(): bool
    {
        $this->items = [];
        array_map('unlink', glob($this->cacheDir . '/*'));
        return true;
    }

    public function deleteItem($key): bool
    {
        $this->validateKey($key);
        unset($this->items[$key]);
        $path = $this->getFilePath($key);
        
        if (file_exists($path)) {
            return unlink($path);
        }
        
        return true;
    }

    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->deleteItem($key)) {
                return false;
            }
        }
        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        $key = $item->getKey();
        $this->validateKey($key);
        
        $this->items[$key] = $item;
        
        $data = [
            'value' => $item->get(),
            'expiry' => $item instanceof CacheItem && $item->getExpiry()
                ? $item->getExpiry()->getTimestamp()
                : null
        ];

        return $this->writeFile($this->getFilePath($key), $data);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->items[$item->getKey()] = $item;
        return true;
    }

    public function commit(): bool
    {
        foreach ($this->items as $item) {
            if (!$this->save($item)) {
                return false;
            }
        }
        return true;
    }

    private function validateKey($key): void
    {
        if (!is_string($key) || preg_match('/[^a-zA-Z0-9._-]/', $key)) {
            throw new CacheException('Invalid cache key');
        }
    }

    private function getFilePath($key): string
    {
        return $this->cacheDir . '/' . $key . '.cache';
    }

    private function writeFile($path, $data): bool
    {
        return file_put_contents($path, serialize($data), LOCK_EX) !== false;
    }

    private function readFile($path)
    {
        $data = file_get_contents($path);
        return $data === false ? false : unserialize($data);
    }
}