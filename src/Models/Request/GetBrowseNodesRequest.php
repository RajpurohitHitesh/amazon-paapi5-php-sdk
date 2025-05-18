<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\Request;

class GetBrowseNodesRequest
{
    private string $partnerTag;
    private string $partnerType = 'Associates';
    private array $browseNodeIds = [];
    private array $resources = [];

    public function setPartnerTag(string $partnerTag): self
    {
        $this->partnerTag = $partnerTag;
        return $this;
    }

    public function setBrowseNodeIds(array $browseNodeIds): self
    {
        $this->browseNodeIds = $browseNodeIds;
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
            'BrowseNodeIds' => $this->browseNodeIds,
            'Resources' => $this->resources,
        ];
    }
}