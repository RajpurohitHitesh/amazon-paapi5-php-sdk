<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\OffersV2;

/**
 * SavingBasis model for OffersV2
 * Reference value which is used to calculate savings against
 */
class SavingBasis
{
    private ?Money $money = null;
    private ?string $savingBasisType = null;
    private ?string $savingBasisTypeLabel = null;

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->money = isset($data['Money']) ? new Money($data['Money']) : null;
            $this->savingBasisType = $data['SavingBasisType'] ?? null;
            $this->savingBasisTypeLabel = $data['SavingBasisTypeLabel'] ?? null;
        }
    }

    /**
     * Get saving basis money
     */
    public function getMoney(): ?Money
    {
        return $this->money;
    }

    /**
     * Get saving basis type
     * Valid values: LIST_PRICE, LOWEST_PRICE, LOWEST_PRICE_STRIKETHROUGH, WAS_PRICE
     */
    public function getSavingBasisType(): ?string
    {
        return $this->savingBasisType;
    }

    /**
     * Get saving basis type label (e.g., "List Price")
     */
    public function getSavingBasisTypeLabel(): ?string
    {
        return $this->savingBasisTypeLabel;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'Money' => $this->money?->toArray(),
            'SavingBasisType' => $this->savingBasisType,
            'SavingBasisTypeLabel' => $this->savingBasisTypeLabel,
        ], fn($value) => $value !== null);
    }
}
