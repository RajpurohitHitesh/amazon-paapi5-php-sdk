<?php

declare(strict_types=1);

namespace AmazonPaapi5\Models\OffersV2;

/**
 * OffersV2 model
 * Contains various resources related to offer listings for an item
 */
class OffersV2
{
    /** @var OfferListing[] */
    private array $listings = [];

    public function __construct(?array $data = null)
    {
        if ($data && isset($data['Listings']) && is_array($data['Listings'])) {
            foreach ($data['Listings'] as $listing) {
                $this->listings[] = new OfferListing($listing);
            }
        }
    }

    /**
     * Get all offer listings
     * 
     * @return OfferListing[]
     */
    public function getListings(): array
    {
        return $this->listings;
    }

    /**
     * Get the BuyBox winner listing (if exists)
     */
    public function getBuyBoxWinner(): ?OfferListing
    {
        foreach ($this->listings as $listing) {
            if ($listing->isBuyBoxWinner() === true) {
                return $listing;
            }
        }
        return null;
    }

    /**
     * Get listings with active deals
     * 
     * @return OfferListing[]
     */
    public function getDealListings(): array
    {
        return array_filter($this->listings, function($listing) {
            return $listing->getDealDetails() !== null;
        });
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'Listings' => array_map(fn($listing) => $listing->toArray(), $this->listings),
        ];
    }
}
