<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models;

use AmazonPaapi5\Models\OffersV2\OffersV2;

class Item
{
    private string $asin;
    private ?string $detailPageUrl = null;
    private array $images = [];
    private array $itemInfo = [];
    private array $offers = [];
    private ?OffersV2 $offersV2 = null;

    public function __construct(array $data)
    {
        $this->asin = $data['ASIN'] ?? '';
        $this->detailPageUrl = $data['DetailPageURL'] ?? null;
        $this->images = $data['Images'] ?? [];
        $this->itemInfo = $data['ItemInfo'] ?? [];
        $this->offers = $data['Offers'] ?? [];
        $this->offersV2 = isset($data['OffersV2']) ? new OffersV2($data['OffersV2']) : null;
    }

    public function getAsin(): string
    {
        return $this->asin;
    }

    public function getDetailPageUrl(): ?string
    {
        return $this->detailPageUrl;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function getItemInfo(): array
    {
        return $this->itemInfo;
    }

    public function getOffers(): array
    {
        return $this->offers;
    }

    /**
     * Get OffersV2 data (recommended over Offers V1)
     */
    public function getOffersV2(): ?OffersV2
    {
        return $this->offersV2;
    }
}