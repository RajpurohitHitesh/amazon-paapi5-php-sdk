<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\OffersV2;

/**
 * Condition model for OffersV2
 * Specifies the condition of the offer
 */
class Condition
{
    private ?string $conditionNote = null;
    private ?string $subCondition = null;
    private ?string $value = null;

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->conditionNote = $data['ConditionNote'] ?? null;
            $this->subCondition = $data['SubCondition'] ?? null;
            $this->value = $data['Value'] ?? null;
        }
    }

    /**
     * Get condition note provided by seller
     */
    public function getConditionNote(): ?string
    {
        return $this->conditionNote;
    }

    /**
     * Get sub-condition
     * Valid values: LikeNew, Good, VeryGood, Acceptable, Refurbished, OEM, OpenBox, Unknown
     */
    public function getSubCondition(): ?string
    {
        return $this->subCondition;
    }

    /**
     * Get condition value
     * Valid values: New, Used, Refurbished, Unknown
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'ConditionNote' => $this->conditionNote,
            'SubCondition' => $this->subCondition,
            'Value' => $this->value,
        ], fn($value) => $value !== null);
    }
}
