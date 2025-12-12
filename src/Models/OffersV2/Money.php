<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\OffersV2;

/**
 * Money model for OffersV2
 * Common struct used for representing money
 */
class Money
{
    private ?float $amount = null;
    private ?string $currency = null;
    private ?string $displayAmount = null;

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->amount = isset($data['Amount']) ? (float)$data['Amount'] : null;
            $this->currency = $data['Currency'] ?? null;
            $this->displayAmount = $data['DisplayAmount'] ?? null;
        }
    }

    /**
     * Get the amount
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * Get the currency code (e.g., USD, EUR, JPY)
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * Get the formatted display amount (e.g., "$59.49")
     */
    public function getDisplayAmount(): ?string
    {
        return $this->displayAmount;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'Amount' => $this->amount,
            'Currency' => $this->currency,
            'DisplayAmount' => $this->displayAmount,
        ], fn($value) => $value !== null);
    }
}
