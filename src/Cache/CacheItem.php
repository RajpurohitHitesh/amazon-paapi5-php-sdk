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

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function get()
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->hit;
    }

    /**
     * @param mixed $value
     * @return self
     */
    #[\ReturnTypeWillChange]
    public function set($value)
    {
        $this->value = $value;
        $this->hit = true;
        return $this;
    }

    /**
     * @param \DateTimeInterface|null $expiration
     * @return self
     */
    #[\ReturnTypeWillChange]
    public function expiresAt($expiration)
    {
        $this->expiry = $expiration;
        return $this;
    }

    /**
     * @param \DateInterval|int|null $time
     * @return self
     */
    #[\ReturnTypeWillChange]
    public function expiresAfter($time)
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