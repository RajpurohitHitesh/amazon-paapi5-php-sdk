<?php

declare(strict_types=1);

namespace AmazonPaapi5\Cache;

use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{
    private string $key;
    /** @var mixed */
    private $value;
    private bool $hit;
    private ?\DateTimeInterface $expiry = null;

    /**
     * @param mixed $value
     */
    public function __construct(string $key, $value = null, bool $hit = false)
    {
        $this->key = $key;
        $this->value = $value;
        $this->hit = $hit;
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
        return $this->hit;
    }

    public function set($value): static
    {
        $this->value = $value;
        $this->hit = true;
        return $this;
    }

    public function expiresAt($expiration): static
    {
        $this->expiry = $expiration;
        return $this;
    }

    public function expiresAfter($time): static
    {
        if ($time === null) {
            $this->expiry = null;
        } elseif ($time instanceof \DateInterval) {
            $this->expiry = (new \DateTime())->add($time);
        } elseif (is_int($time)) {
            $this->expiry = (new \DateTime())->add(new \DateInterval("PT{$time}S"));
        }
        return $this;
    }

    public function getExpiry(): ?\DateTimeInterface
    {
        return $this->expiry;
    }
}