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

    public function toArray(): array
    {
        $data = [
            'PartnerTag' => $this->partnerTag,
            'PartnerType' => $this->partnerType,
            'Keywords' => $this->keywords,
            'Resources' => $this->resources
        ];

        if ($this->searchIndex !== null) {
            $data['SearchIndex'] = $this->searchIndex;
        }

        return $data;
    }
}