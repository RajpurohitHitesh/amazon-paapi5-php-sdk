<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models;

class Item
{
    private string $asin;
    private ?string $detailPageUrl = null;
    private array $images = [];
    private array $itemInfo = [];
    private array $offers = [];

    public function __construct(array $data)
    {
        $this->asin = $data['ASIN'] ?? '';
        $this->detailPageUrl = $data['DetailPageURL'] ?? null;
        $this->images = $data['Images'] ?? [];
        $this->itemInfo = $data['ItemInfo'] ?? [];
        $this->offers = $data['Offers'] ?? [];
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
}