<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\Request;

class SearchItemsRequest
{
    private string $partnerTag;
    private string $partnerType = 'Associates';
    private ?string $keywords = null;
    private ?string $searchIndex = 'All';
    private array $resources = [];
    private ?int $itemCount = null;

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

    public function setSearchIndex(string $searchIndex): self
    {
        $this->searchIndex = $searchIndex;
        return $this;
    }

    public function setItemCount(int $itemCount): self
    {
        $this->itemCount = max(1, min(10, $itemCount));
        return $this;
    }

    public function setResources(array $resources): self
    {
        $this->resources = $resources;
        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'PartnerTag' => $this->partnerTag,
            'PartnerType' => $this->partnerType,
            'Keywords' => $this->keywords,
            'SearchIndex' => $this->searchIndex,
            'ItemCount' => $this->itemCount,
            'Resources' => $this->resources,
        ], fn($value) => !is_null($value));
    }
}