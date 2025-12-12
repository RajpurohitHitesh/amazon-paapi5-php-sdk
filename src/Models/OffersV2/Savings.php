<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\OffersV2;

/**
 * Savings model for OffersV2
 * Savings of an offer
 */
class Savings
{
    private ?Money $money = null;
    private ?int $percentage = null;

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->money = isset($data['Money']) ? new Money($data['Money']) : null;
            $this->percentage = isset($data['Percentage']) ? (int)$data['Percentage'] : null;
        }
    }

    /**
     * Get savings amount
     */
    public function getMoney(): ?Money
    {
        return $this->money;
    }

    /**
     * Get savings percentage
     */
    public function getPercentage(): ?int
    {
        return $this->percentage;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'Money' => $this->money?->toArray(),
            'Percentage' => $this->percentage,
        ], fn($value) => $value !== null);
    }
}
