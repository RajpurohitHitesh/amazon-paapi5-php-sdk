<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\Request;

class GetItemsRequest
{
    private string $partnerTag;
    private string $partnerType = 'Associates';
    private array $itemIds = [];
    private array $resources = [];

    public function setPartnerTag(string $partnerTag): self
    {
        $this->partnerTag = $partnerTag;
        return $this;
    }

    public function setItemIds(array $itemIds): self
    {
        $this->itemIds = array_slice($itemIds, 0, 10); // Max 10 ASINs
        return $this;
    }

    public function setResources(array $resources): self
    {
        $this->resources = $resources;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'PartnerTag' => $this->partnerTag,
            'PartnerType' => $this->partnerType,
            'ItemIds' => $this->itemIds,
            'Resources' => $this->resources,
        ];
    }
}