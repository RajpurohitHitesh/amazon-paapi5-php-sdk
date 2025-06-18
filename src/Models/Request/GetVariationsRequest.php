<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\Request;

class GetVariationsRequest
{
    private string $partnerTag;
    private string $partnerType = 'Associates';
    private string $asin;
    private array $resources = [];

    public function setPartnerTag(string $partnerTag): self
    {
        $this->partnerTag = $partnerTag;
        return $this;
    }

    public function setAsin(string $asin): self
    {
        $this->asin = $asin;
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
            'ASIN' => $this->asin,
            'Resources' => $this->resources,
        ];
    }
}