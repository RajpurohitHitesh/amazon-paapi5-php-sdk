<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\OffersV2;

/**
 * LoyaltyPoints model for OffersV2
 * Loyalty Points (Amazon Japan only)
 */
class LoyaltyPoints
{
    private ?int $points = null;

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->points = isset($data['Points']) ? (int)$data['Points'] : null;
        }
    }

    /**
     * Get loyalty points
     */
    public function getPoints(): ?int
    {
        return $this->points;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'Points' => $this->points,
        ], fn($value) => $value !== null);
    }
}
