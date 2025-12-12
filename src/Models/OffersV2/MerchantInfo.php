<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\OffersV2;

/**
 * MerchantInfo model for OffersV2
 * Specifies merchant information of an offer
 */
class MerchantInfo
{
    private ?string $id = null;
    private ?string $name = null;

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->id = $data['Id'] ?? null;
            $this->name = $data['Name'] ?? null;
        }
    }

    /**
     * Get merchant ID
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get merchant name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'Id' => $this->id,
            'Name' => $this->name,
        ], fn($value) => $value !== null);
    }
}
