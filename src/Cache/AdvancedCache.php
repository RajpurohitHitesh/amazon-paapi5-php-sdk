<?php

declare(strict_types=1);

namespace AmazonPaapi5\Cache;

use Psr\Cache\CacheItemPoolInterface;
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

    public function getItem($key)
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

        $item = new CacheItem($key);
        $item->set($data['value'])->expiresAt(
            $data['expiry'] ? \DateTime::createFromFormat('U', (string)$data['expiry']) : null
        );

        $this->items[$key] = $item;
        return $item;
    }

    public function getItems(array $keys = [])
    {
        return array_map([$this, 'getItem'], $keys);
    }

    public function hasItem($key)
    {
        return $this->getItem($key)->isHit();
    }

    public function clear()
    {
        $this->items = [];
        array_map('unlink', glob($this->cacheDir . '/*'));
        return true;
    }

    public function deleteItem($key)
    {
        $this->validateKey($key);
        unset($this->items[$key]);
        $path = $this->getFilePath($key);
        
        if (file_exists($path)) {
            return unlink($path);
        }
        
        return true;
    }

    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }
        return true;
    }

    public function save(CacheItemInterface $item)
    {
        $key = $item->getKey();
        $this->validateKey($key);
        
        $this->items[$key] = $item;
        
        $data = [
            'value' => $item->get(),
            'expiry' => $item->getExpiry() ? $item->getExpiry()->getTimestamp() : null
        ];

        return $this->writeFile($this->getFilePath($key), $data);
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        $this->items[$item->getKey()] = $item;
        return true;
    }

    public function commit()
    {
        foreach ($this->items as $item) {
            if (!$this->save($item)) {
                return false;
            }
        }
        return true;
    }

    private function validateKey($key)
    {
        if (!is_string($key) || preg_match('/[^a-zA-Z0-9._-]/', $key)) {
            throw new CacheException('Invalid cache key');
        }
    }

    private function getFilePath($key)
    {
        return $this->cacheDir . '/' . $key . '.cache';
    }

    private function writeFile($path, $data)
    {
        return file_put_contents($path, serialize($data), LOCK_EX) !== false;
    }

    private function readFile($path)
    {
        $data = file_get_contents($path);
        return $data === false ? false : unserialize($data);
    }
}