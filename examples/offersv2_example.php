<?php

/**
 * Example: Using OffersV2 API
 * 
 * This example demonstrates how to use the new Amazon PA-API 5.0 OffersV2 API
 * to retrieve detailed offer information including prices, deals, availability,
 * merchant information, and more.
 * 
 * OffersV2 provides improved reliability and data quality compared to the original
 * Offers API and includes new features like deal information and enhanced price data.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use AmazonPaapi5\Client;
use AmazonPaapi5\Config;
use AmazonPaapi5\Operations\GetItems;
use AmazonPaapi5\Models\Request\GetItemsRequest;

// Configure your API credentials
$config = new Config([
    'accessKey' => 'YOUR_ACCESS_KEY',
    'secretKey' => 'YOUR_SECRET_KEY',
    'partnerTag' => 'YOUR_PARTNER_TAG',
    'marketplace' => 'US' // or other marketplace
]);

$client = new Client($config);

// Create a GetItems request with OffersV2 resources
$request = new GetItemsRequest([
    'ItemIds' => ['B00MNV8E0C'], // Example ASIN
    'Resources' => [
        'ItemInfo.Title',
        'OffersV2.Listings.Availability.MaxOrderQuantity',
        'OffersV2.Listings.Availability.Message',
        'OffersV2.Listings.Availability.MinOrderQuantity',
        'OffersV2.Listings.Availability.Type',
        'OffersV2.Listings.Condition.ConditionNote',
        'OffersV2.Listings.Condition.SubCondition',
        'OffersV2.Listings.Condition.Value',
        'OffersV2.Listings.DealDetails.AccessType',
        'OffersV2.Listings.DealDetails.Badge',
        'OffersV2.Listings.DealDetails.EarlyAccessDurationInMilliseconds',
        'OffersV2.Listings.DealDetails.EndTime',
        'OffersV2.Listings.DealDetails.PercentClaimed',
        'OffersV2.Listings.DealDetails.StartTime',
        'OffersV2.Listings.IsBuyBoxWinner',
        'OffersV2.Listings.LoyaltyPoints.Points',
        'OffersV2.Listings.MerchantInfo.Id',
        'OffersV2.Listings.MerchantInfo.Name',
        'OffersV2.Listings.Price.Money',
        'OffersV2.Listings.Price.PricePerUnit',
        'OffersV2.Listings.Price.SavingBasis.Money',
        'OffersV2.Listings.Price.SavingBasis.SavingBasisType',
        'OffersV2.Listings.Price.SavingBasis.SavingBasisTypeLabel',
        'OffersV2.Listings.Price.Savings.Money',
        'OffersV2.Listings.Price.Savings.Percentage',
        'OffersV2.Listings.Type',
        'OffersV2.Listings.ViolatesMAP',
    ]
]);

try {
    $response = $client->getItems($request);
    
    if ($response && isset($response['ItemsResult']['Items'])) {
        foreach ($response['ItemsResult']['Items'] as $itemData) {
            $item = new \AmazonPaapi5\Models\Item($itemData);
            
            echo "ASIN: " . $item->getAsin() . "\n";
            echo "URL: " . $item->getDetailPageUrl() . "\n\n";
            
            // Get OffersV2 data
            $offersV2 = $item->getOffersV2();
            
            if ($offersV2) {
                echo "=== OFFERS V2 DATA ===\n\n";
                
                // Get BuyBox Winner
                $buyBoxWinner = $offersV2->getBuyBoxWinner();
                if ($buyBoxWinner) {
                    echo "--- BuyBox Winner ---\n";
                    displayOfferListing($buyBoxWinner);
                }
                
                // Get all listings
                $listings = $offersV2->getListings();
                echo "\nTotal Listings: " . count($listings) . "\n\n";
                
                foreach ($listings as $index => $listing) {
                    echo "--- Listing #" . ($index + 1) . " ---\n";
                    displayOfferListing($listing);
                }
                
                // Get listings with deals
                $dealListings = $offersV2->getDealListings();
                if (!empty($dealListings)) {
                    echo "\n=== ACTIVE DEALS ===\n";
                    foreach ($dealListings as $index => $listing) {
                        echo "\n--- Deal #" . ($index + 1) . " ---\n";
                        displayDealInfo($listing);
                    }
                }
            } else {
                echo "No OffersV2 data available\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

/**
 * Display offer listing details
 */
function displayOfferListing($listing)
{
    // Price information
    $price = $listing->getPrice();
    if ($price && $price->getMoney()) {
        echo "Price: " . $price->getMoney()->getDisplayAmount() . "\n";
        
        if ($price->getPricePerUnit()) {
            echo "Price Per Unit: " . $price->getPricePerUnit()->getDisplayAmount() . "\n";
        }
        
        // Savings information
        $savings = $price->getSavings();
        if ($savings && $savings->getMoney()) {
            echo "Savings: " . $savings->getMoney()->getDisplayAmount();
            if ($savings->getPercentage()) {
                echo " (" . $savings->getPercentage() . "%)";
            }
            echo "\n";
        }
        
        // Saving basis (original price)
        $savingBasis = $price->getSavingBasis();
        if ($savingBasis && $savingBasis->getMoney()) {
            $label = $savingBasis->getSavingBasisTypeLabel() ?: $savingBasis->getSavingBasisType();
            echo "$label: " . $savingBasis->getMoney()->getDisplayAmount() . "\n";
        }
    }
    
    // Merchant information
    $merchant = $listing->getMerchantInfo();
    if ($merchant) {
        echo "Merchant: " . $merchant->getName();
        if ($merchant->getId()) {
            echo " (ID: " . $merchant->getId() . ")";
        }
        echo "\n";
    }
    
    // Availability
    $availability = $listing->getAvailability();
    if ($availability) {
        echo "Availability: " . ($availability->getType() ?: 'Unknown') . "\n";
        if ($availability->getMessage()) {
            echo "  Message: " . $availability->getMessage() . "\n";
        }
        if ($availability->getMinOrderQuantity()) {
            echo "  Min Order: " . $availability->getMinOrderQuantity() . "\n";
        }
        if ($availability->getMaxOrderQuantity()) {
            echo "  Max Order: " . $availability->getMaxOrderQuantity() . "\n";
        }
    }
    
    // Condition
    $condition = $listing->getCondition();
    if ($condition) {
        echo "Condition: " . ($condition->getValue() ?: 'Unknown');
        if ($condition->getSubCondition() && $condition->getSubCondition() !== 'Unknown') {
            echo " (" . $condition->getSubCondition() . ")";
        }
        echo "\n";
        if ($condition->getConditionNote()) {
            echo "  Note: " . $condition->getConditionNote() . "\n";
        }
    }
    
    // Deal information
    $deal = $listing->getDealDetails();
    if ($deal) {
        echo "Deal: YES\n";
        if ($deal->getBadge()) {
            echo "  Badge: " . $deal->getBadge() . "\n";
        }
        if ($deal->getAccessType()) {
            echo "  Access: " . $deal->getAccessType() . "\n";
        }
    }
    
    // BuyBox winner status
    if ($listing->isBuyBoxWinner()) {
        echo "*** BuyBox Winner ***\n";
    }
    
    // Offer type
    if ($listing->getType()) {
        echo "Type: " . $listing->getType() . "\n";
    }
    
    // MAP violation
    if ($listing->violatesMAP()) {
        echo "Violates MAP: YES\n";
    }
    
    // Loyalty points (Japan only)
    $loyaltyPoints = $listing->getLoyaltyPoints();
    if ($loyaltyPoints && $loyaltyPoints->getPoints()) {
        echo "Loyalty Points: " . $loyaltyPoints->getPoints() . "\n";
    }
    
    echo "\n";
}

/**
 * Display deal information
 */
function displayDealInfo($listing)
{
    $deal = $listing->getDealDetails();
    if (!$deal) {
        return;
    }
    
    $price = $listing->getPrice();
    if ($price && $price->getMoney()) {
        echo "Deal Price: " . $price->getMoney()->getDisplayAmount() . "\n";
    }
    
    if ($deal->getBadge()) {
        echo "Badge: " . $deal->getBadge() . "\n";
    }
    
    if ($deal->getAccessType()) {
        $accessType = $deal->getAccessType();
        echo "Access Type: " . $accessType . "\n";
        
        if ($accessType === 'PRIME_EXCLUSIVE') {
            echo "  (Prime members only)\n";
        } elseif ($accessType === 'PRIME_EARLY_ACCESS') {
            echo "  (Prime members get early access)\n";
            if ($deal->getEarlyAccessDurationInMilliseconds()) {
                $minutes = round($deal->getEarlyAccessDurationInMilliseconds() / 60000);
                echo "  Early Access Duration: $minutes minutes\n";
            }
        }
    }
    
    if ($deal->getStartTime()) {
        echo "Start Time: " . $deal->getStartTime() . "\n";
    }
    
    if ($deal->getEndTime()) {
        echo "End Time: " . $deal->getEndTime() . "\n";
    }
    
    if ($deal->getPercentClaimed()) {
        echo "Percent Claimed: " . $deal->getPercentClaimed() . "%\n";
    }
    
    $merchant = $listing->getMerchantInfo();
    if ($merchant && $merchant->getName()) {
        echo "Merchant: " . $merchant->getName() . "\n";
    }
}
