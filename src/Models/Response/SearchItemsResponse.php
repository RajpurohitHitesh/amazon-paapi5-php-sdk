<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\Response;

use AmazonPaapi5\Models\Item;

class SearchItemsResponse
{
    private array $items = [];
    private int $totalResultCount = 0;
    private ?string $searchUrl = null;

    public function __construct(array $data)
    {
        if (isset($data['SearchResult']['Items'])) {
            foreach ($data['SearchResult']['Items'] as $itemData) {
                $this->items[] = new Item($itemData);
            }
            $this->totalResultCount = $data['SearchResult']['TotalResultCount'] ?? 0;
            $this->searchUrl = $data['SearchResult']['SearchURL'] ?? null;
        }
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalResultCount(): int
    {
        return $this->totalResultCount;
    }

    public function getSearchUrl(): ?string
    {
        return $this->searchUrl;
    }
}