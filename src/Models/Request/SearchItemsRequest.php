<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\Request;

class SearchItemsRequest extends AbstractRequest
{
    private string $partnerTag;
    private string $partnerType = 'Associates';
    private string $keywords;
    private array $resources = [];
    private ?string $searchIndex = null;
    private int $itemCount = 10;
    private int $itemPage = 1;
    private ?string $sortBy = null;
    private ?string $brand = null;
    private array $browseNodeId = [];
    private ?string $condition = null;
    private ?string $currencyOfPreference = null;
    private ?string $languagesOfPreference = null;
    private ?string $marketplace = null;
    private ?string $merchant = null;
    private array $maxPrice = [];
    private array $minPrice = [];
    private ?int $minReviewsRating = null;
    private ?int $minSavingPercent = null;
    private ?string $offerCount = null;
    private array $properties = [];
    private ?string $title = null;
    private ?string $actor = null;
    private ?string $artist = null;
    private ?string $author = null;
    private ?string $availability = null;

    public function setPartnerTag(string $partnerTag): self
    {
        $this->partnerTag = $partnerTag;
        return $this;
    }

    public function setKeywords(string $keywords): self
    {
        $this->keywords = $keywords;
        return $this;
    }

    public function setResources(array $resources): self
    {
        $this->resources = $resources;
        return $this;
    }

    public function setSearchIndex(?string $searchIndex): self
    {
        $this->searchIndex = $searchIndex;
        return $this;
    }

    public function setItemCount(int $itemCount): self
    {
        $this->itemCount = max(1, min(10, $itemCount)); // Amazon PA-API allows 1-10 items per request
        return $this;
    }

    public function setItemPage(int $itemPage): self
    {
        $this->itemPage = max(1, min(10, $itemPage)); // Amazon PA-API allows max 10 pages
        return $this;
    }

    public function setSortBy(?string $sortBy): self
    {
        $this->sortBy = $sortBy;
        return $this;
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function setBrowseNodeId(array $browseNodeId): self
    {
        $this->browseNodeId = $browseNodeId;
        return $this;
    }

    public function setCondition(?string $condition): self
    {
        $this->condition = $condition;
        return $this;
    }

    public function setCurrencyOfPreference(?string $currencyOfPreference): self
    {
        $this->currencyOfPreference = $currencyOfPreference;
        return $this;
    }

    public function setLanguagesOfPreference(?string $languagesOfPreference): self
    {
        $this->languagesOfPreference = $languagesOfPreference;
        return $this;
    }

    public function setMarketplace(?string $marketplace): self
    {
        $this->marketplace = $marketplace;
        return $this;
    }

    public function setMerchant(?string $merchant): self
    {
        $this->merchant = $merchant;
        return $this;
    }

    public function setMaxPrice(array $maxPrice): self
    {
        $this->maxPrice = $maxPrice;
        return $this;
    }

    public function setMinPrice(array $minPrice): self
    {
        $this->minPrice = $minPrice;
        return $this;
    }

    public function setMinReviewsRating(?int $minReviewsRating): self
    {
        $this->minReviewsRating = $minReviewsRating;
        return $this;
    }

    public function setMinSavingPercent(?int $minSavingPercent): self
    {
        $this->minSavingPercent = $minSavingPercent;
        return $this;
    }

    public function setOfferCount(?string $offerCount): self
    {
        $this->offerCount = $offerCount;
        return $this;
    }

    public function setProperties(array $properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setActor(?string $actor): self
    {
        $this->actor = $actor;
        return $this;
    }

    public function setArtist(?string $artist): self
    {
        $this->artist = $artist;
        return $this;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function setAvailability(?string $availability): self
    {
        $this->availability = $availability;
        return $this;
    }

    public function getMarketplace(): ?string
    {
        return $this->marketplace;
    }

    public function toArray(): array
    {
        $data = [
            'PartnerTag' => $this->partnerTag,
            'PartnerType' => $this->partnerType,
            'Keywords' => $this->keywords,
            'Resources' => $this->resources,
            'ItemCount' => $this->itemCount,
            'ItemPage' => $this->itemPage
        ];

        if ($this->searchIndex !== null) {
            $data['SearchIndex'] = $this->searchIndex;
        }

        if ($this->sortBy !== null) {
            $data['SortBy'] = $this->sortBy;
        }

        if ($this->brand !== null) {
            $data['Brand'] = $this->brand;
        }

        if (!empty($this->browseNodeId)) {
            $data['BrowseNodeId'] = $this->browseNodeId;
        }

        if ($this->condition !== null) {
            $data['Condition'] = $this->condition;
        }

        if ($this->currencyOfPreference !== null) {
            $data['CurrencyOfPreference'] = $this->currencyOfPreference;
        }

        if ($this->languagesOfPreference !== null) {
            $data['LanguagesOfPreference'] = $this->languagesOfPreference;
        }

        if ($this->marketplace !== null) {
            $data['Marketplace'] = $this->marketplace;
        }

        if ($this->merchant !== null) {
            $data['Merchant'] = $this->merchant;
        }

        if (!empty($this->maxPrice)) {
            $data['MaxPrice'] = $this->maxPrice;
        }

        if (!empty($this->minPrice)) {
            $data['MinPrice'] = $this->minPrice;
        }

        if ($this->minReviewsRating !== null) {
            $data['MinReviewsRating'] = $this->minReviewsRating;
        }

        if ($this->minSavingPercent !== null) {
            $data['MinSavingPercent'] = $this->minSavingPercent;
        }

        if ($this->offerCount !== null) {
            $data['OfferCount'] = $this->offerCount;
        }

        if (!empty($this->properties)) {
            $data['Properties'] = $this->properties;
        }

        if ($this->title !== null) {
            $data['Title'] = $this->title;
        }

        if ($this->actor !== null) {
            $data['Actor'] = $this->actor;
        }

        if ($this->artist !== null) {
            $data['Artist'] = $this->artist;
        }

        if ($this->author !== null) {
            $data['Author'] = $this->author;
        }

        if ($this->availability !== null) {
            $data['Availability'] = $this->availability;
        }

        return $data;
    }
}