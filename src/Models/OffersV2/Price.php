<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\OffersV2;

/**
 * Price model for OffersV2
 * Specifies buying price of an offer
 */
class Price
{
    private ?Money $money = null;
    private ?Money $pricePerUnit = null;
    private ?SavingBasis $savingBasis = null;
    private ?Savings $savings = null;

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->money = isset($data['Money']) ? new Money($data['Money']) : null;
            $this->pricePerUnit = isset($data['PricePerUnit']) ? new Money($data['PricePerUnit']) : null;
            $this->savingBasis = isset($data['SavingBasis']) ? new SavingBasis($data['SavingBasis']) : null;
            $this->savings = isset($data['Savings']) ? new Savings($data['Savings']) : null;
        }
    }

    /**
     * Get buying price amount
     */
    public function getMoney(): ?Money
    {
        return $this->money;
    }

    /**
     * Get price per unit (includes unit formatting in DisplayAmount)
     */
    public function getPricePerUnit(): ?Money
    {
        return $this->pricePerUnit;
    }

    /**
     * Get saving basis (reference price for savings calculation)
     */
    public function getSavingBasis(): ?SavingBasis
    {
        return $this->savingBasis;
    }

    /**
     * Get savings information
     */
    public function getSavings(): ?Savings
    {
        return $this->savings;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'Money' => $this->money?->toArray(),
            'PricePerUnit' => $this->pricePerUnit?->toArray(),
            'SavingBasis' => $this->savingBasis?->toArray(),
            'Savings' => $this->savings?->toArray(),
        ], fn($value) => $value !== null);
    }
}
