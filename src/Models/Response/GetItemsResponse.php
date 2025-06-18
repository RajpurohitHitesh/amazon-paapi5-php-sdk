<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\Response;

use AmazonPaapi5\Models\Item;

class GetItemsResponse
{
    private array $items = [];

    public function __construct(array $data)
    {
        if (isset($data['ItemsResult']['Items'])) {
            foreach ($data['ItemsResult']['Items'] as $itemData) {
                $this->items[] = new Item($itemData);
            }
        }
    }

    public function getItems(): array
    {
        return $this->items;
    }
}