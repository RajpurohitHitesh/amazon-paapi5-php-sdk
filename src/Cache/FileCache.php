<?php

declare(strict_types=1);

namespace AmazonPaapi5\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class FileCache implements CacheItemPoolInterface
{
    private string $cacheDir;
    private array $items = [];

    public function __construct(string $cacheDir = '/tmp/amazon-paapi5-cache')
    {
        $this->cacheDir = rtrim($cacheDir, '/');
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function getItem($key): CacheItemInterface
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] > time()) {
                return new FileCacheItem($key, $data['value'], true, $data['expires']);
            }
            unlink($file);
        }
        return new FileCacheItem($key, null, false);
    }

    public function getItems(array $keys = []): iterable
    {
        $items = [];
        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }
        return $items;
    }

    public function hasItem($key): bool
    {
        $item = $this->getItem($key);
        return $item->isHit();
    }

    public function clear(): bool
    {
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }

    public function deleteItem($key): bool
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }
        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        $file = $this->getFilePath($item->getKey());
        $data = [
            'value' => $item->get(),
            'expires' => $item->getExpiresAt() ? $item->getExpiresAt()->getTimestamp() : PHP_INT_MAX,
        ];
        return file_put_contents($file, serialize($data)) !== false;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->items[$item->getKey()] = $item;
        return true;
    }

    public function commit(): bool
    {
        foreach ($this->items as $item) {
            $this->save($item);
        }
        $this->items = [];
        return true;
    }

    private function getFilePath(string $key): string
    {
        return $this->cacheDir . '/' . hash('sha256', $key) . '.cache';
    }
}

class FileCacheItem implements CacheItemInterface
{
    private string $key;
    private $value;
    private bool $isHit;
    private ?int $expires;

    public function __construct(string $key, $value, bool $isHit, ?int $expires = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->isHit = $isHit;
        $this->expires = $expires;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get()
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->isHit;
    }

    public function set($value): CacheItemInterface
    {
        $this->value = $value;
        $this->isHit = true;
        return $this;
    }

    public function expiresAt($expiration): CacheItemInterface
    {
        $this->expires = $expiration ? $expiration->getTimestamp() : null;
        return $this;
    }

    public function expiresAfter($time): CacheItemInterface
    {
        $this->expires = $time ? time() + $time : null;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expires ? (new \DateTime())->setTimestamp($this->expires) : null;
    }
}
