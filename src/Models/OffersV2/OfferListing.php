<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\OffersV2;

/**
 * OfferListing model for OffersV2
 * Specifies an individual offer listing for a product
 */
class OfferListing
{
    private ?Availability $availability = null;
    private ?Condition $condition = null;
    private ?DealDetails $dealDetails = null;
    private ?bool $isBuyBoxWinner = null;
    private ?LoyaltyPoints $loyaltyPoints = null;
    private ?MerchantInfo $merchantInfo = null;
    private ?Price $price = null;
    private ?string $type = null;
    private ?bool $violatesMAP = null;

    public function __construct(?array $data = null)
    {
        if ($data) {
            $this->availability = isset($data['Availability']) ? new Availability($data['Availability']) : null;
            $this->condition = isset($data['Condition']) ? new Condition($data['Condition']) : null;
            $this->dealDetails = isset($data['DealDetails']) ? new DealDetails($data['DealDetails']) : null;
            $this->isBuyBoxWinner = isset($data['IsBuyBoxWinner']) ? (bool)$data['IsBuyBoxWinner'] : null;
            $this->loyaltyPoints = isset($data['LoyaltyPoints']) ? new LoyaltyPoints($data['LoyaltyPoints']) : null;
            $this->merchantInfo = isset($data['MerchantInfo']) ? new MerchantInfo($data['MerchantInfo']) : null;
            $this->price = isset($data['Price']) ? new Price($data['Price']) : null;
            $this->type = $data['Type'] ?? null;
            $this->violatesMAP = isset($data['ViolatesMAP']) ? (bool)$data['ViolatesMAP'] : null;
        }
    }

    /**
     * Get availability information
     */
    public function getAvailability(): ?Availability
    {
        return $this->availability;
    }

    /**
     * Get condition information
     */
    public function getCondition(): ?Condition
    {
        return $this->condition;
    }

    /**
     * Get deal details (if offer has a deal)
     */
    public function getDealDetails(): ?DealDetails
    {
        return $this->dealDetails;
    }

    /**
     * Check if this is the BuyBox winner
     */
    public function isBuyBoxWinner(): ?bool
    {
        return $this->isBuyBoxWinner;
    }

    /**
     * Get loyalty points (Japan only)
     */
    public function getLoyaltyPoints(): ?LoyaltyPoints
    {
        return $this->loyaltyPoints;
    }

    /**
     * Get merchant information
     */
    public function getMerchantInfo(): ?MerchantInfo
    {
        return $this->merchantInfo;
    }

    /**
     * Get price information
     */
    public function getPrice(): ?Price
    {
        return $this->price;
    }

    /**
     * Get offer type
     * Valid values: LIGHTNING_DEAL, SUBSCRIBE_AND_SAVE
     * Most listings will not have a type
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Check if offer violates MAP (Minimum Advertised Price)
     */
    public function violatesMAP(): ?bool
    {
        return $this->violatesMAP;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'Availability' => $this->availability?->toArray(),
            'Condition' => $this->condition?->toArray(),
            'DealDetails' => $this->dealDetails?->toArray(),
            'IsBuyBoxWinner' => $this->isBuyBoxWinner,
            'LoyaltyPoints' => $this->loyaltyPoints?->toArray(),
            'MerchantInfo' => $this->merchantInfo?->toArray(),
            'Price' => $this->price?->toArray(),
            'Type' => $this->type,
            'ViolatesMAP' => $this->violatesMAP,
        ], fn($value) => $value !== null);
    }
}
