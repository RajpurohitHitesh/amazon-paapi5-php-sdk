<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\Response;

class GetBrowseNodesResponse
{
    private array $nodes = [];

    public function __construct(array $data)
    {
        if (isset($data['BrowseNodesResult']['BrowseNodes'])) {
            foreach ($data['BrowseNodesResult']['BrowseNodes'] as $nodeData) {
                $this->nodes[] = $nodeData;
            }
        }
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }
}