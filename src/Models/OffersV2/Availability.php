<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\OffersV2;

/**
 * Availability model for OffersV2
 * Specifies availability information about an offer
 */
class Availability
{
    private ?int $maxOrderQuantity = null;
    private ?string $message = null;
    private ?int $minOrderQuantity = null;
    private ?string $type = null;

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->maxOrderQuantity = isset($data['MaxOrderQuantity']) ? (int)$data['MaxOrderQuantity'] : null;
            $this->message = $data['Message'] ?? null;
            $this->minOrderQuantity = isset($data['MinOrderQuantity']) ? (int)$data['MinOrderQuantity'] : null;
            $this->type = $data['Type'] ?? null;
        }
    }

    /**
     * Get maximum order quantity
     */
    public function getMaxOrderQuantity(): ?int
    {
        return $this->maxOrderQuantity;
    }

    /**
     * Get availability message (e.g., "In Stock")
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Get minimum order quantity
     */
    public function getMinOrderQuantity(): ?int
    {
        return $this->minOrderQuantity;
    }

    /**
     * Get availability type
     * Valid values: AVAILABLE_DATE, IN_STOCK, IN_STOCK_SCARCE, LEADTIME,
     * OUT_OF_STOCK, PREORDER, UNAVAILABLE, UNKNOWN
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'MaxOrderQuantity' => $this->maxOrderQuantity,
            'Message' => $this->message,
            'MinOrderQuantity' => $this->minOrderQuantity,
            'Type' => $this->type,
        ], fn($value) => $value !== null);
    }
}
