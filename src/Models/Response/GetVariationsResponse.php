<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\Response;

use AmazonPaapi5\Models\Item;

class GetVariationsResponse
{
    private array $variations = [];

    public function __construct(array $data)
    {
        if (isset($data['VariationsResult']['Items'])) {
            foreach ($data['VariationsResult']['Items'] as $itemData) {
                $this->variations[] = new Item($itemData);
            }
        }
    }

    public function getVariations(): array
    {
        return $this->variations;
    }
}