# Migration Guide: Offers V1 → OffersV2

## ⚠️ IMPORTANT NOTICE

**Offers V1 is being deprecated!** Amazon PA-API will eventually remove the old Offers API completely. All new features are only being added to OffersV2.

**You should migrate to OffersV2 immediately.**

## Why Migrate?

| Feature | Offers V1 (Deprecated) | OffersV2 (Current) |
|---------|----------------------|-------------------|
| **Status** | ⚠️ Being Deprecated | ✅ Active & Recommended |
| **Reliability** | Lower | ✅ Higher |
| **Data Quality** | Basic | ✅ Enhanced |
| **Deal Information** | ❌ Limited | ✅ Full Support |
| **Prime Deals** | ❌ Basic | ✅ Prime Exclusive Support |
| **Merchant Details** | ❌ Name only | ✅ ID + Name |
| **Price Details** | ❌ Basic | ✅ Enhanced with Savings |
| **Availability** | ❌ Basic | ✅ Detailed with Message |
| **Future Features** | ❌ No new features | ✅ All new features |

## Quick Comparison

### OLD WAY (Offers V1) - ⚠️ DEPRECATED

```php
use AmazonPaapi5\Client;
use AmazonPaapi5\Config;
use AmazonPaapi5\Models\Request\GetItemsRequest;

$config = new Config([
    'accessKey' => 'YOUR_ACCESS_KEY',
    'secretKey' => 'YOUR_SECRET_KEY',
    'partnerTag' => 'YOUR_PARTNER_TAG',
    'marketplace' => 'US'
]);

$client = new Client($config);

// OLD - Using Offers V1
$request = new GetItemsRequest([
    'ItemIds' => ['B00MNV8E0C'],
    'Resources' => [
        'Offers.Listings.Condition',
        'Offers.Listings.Price',
        'Offers.Listings.DeliveryInfo.IsPrimeEligible',
        'Offers.Listings.MerchantInfo'
    ]
]);

$response = $client->getItems($request);
$item = new \AmazonPaapi5\Models\Item($response['ItemsResult']['Items'][0]);

// Accessing old Offers (raw array)
$offers = $item->getOffers();
$price = $offers['Listings'][0]['Price']['Amount'] ?? null;
```

### NEW WAY (OffersV2) - ✅ RECOMMENDED

```php
use AmazonPaapi5\Client;
use AmazonPaapi5\Config;
use AmazonPaapi5\Models\Request\GetItemsRequest;
use AmazonPaapi5\Models\Item;

$config = new Config([
    'accessKey' => 'YOUR_ACCESS_KEY',
    'secretKey' => 'YOUR_SECRET_KEY',
    'partnerTag' => 'YOUR_PARTNER_TAG',
    'marketplace' => 'US'
]);

$client = new Client($config);

// NEW - Using OffersV2
$request = new GetItemsRequest([
    'ItemIds' => ['B00MNV8E0C'],
    'Resources' => [
        'OffersV2.Listings.Condition',
        'OffersV2.Listings.Price',
        'OffersV2.Listings.Availability',
        'OffersV2.Listings.MerchantInfo',
        'OffersV2.Listings.DealDetails',
        'OffersV2.Listings.IsBuyBoxWinner'
    ]
]);

$response = $client->getItems($request);
$item = new Item($response['ItemsResult']['Items'][0]);

// Accessing new OffersV2 (strongly typed objects)
$offersV2 = $item->getOffersV2();
$buyBoxWinner = $offersV2->getBuyBoxWinner();
$price = $buyBoxWinner->getPrice()->getMoney()->getDisplayAmount();
```

## Step-by-Step Migration

### Step 1: Update Resource Names

Replace all `Offers.` with `OffersV2.` in your Resources array:

| Old (V1) | New (V2) |
|----------|----------|
| `Offers.Listings.Condition` | `OffersV2.Listings.Condition` |
| `Offers.Listings.Price` | `OffersV2.Listings.Price` |
| `Offers.Listings.MerchantInfo` | `OffersV2.Listings.MerchantInfo` |
| `Offers.Listings.IsBuyBoxWinner` | `OffersV2.Listings.IsBuyBoxWinner` |

### Step 2: Update Code to Use Typed Objects

**Before (V1 - Array Access):**
```php
$offers = $item->getOffers();
$price = $offers['Listings'][0]['Price']['Amount'];
$merchant = $offers['Listings'][0]['MerchantInfo']['Name'];
$condition = $offers['Listings'][0]['Condition']['Value'];
```

**After (V2 - Typed Objects):**
```php
$offersV2 = $item->getOffersV2();
$listing = $offersV2->getBuyBoxWinner();
$price = $listing->getPrice()->getMoney()->getAmount();
$merchant = $listing->getMerchantInfo()->getName();
$condition = $listing->getCondition()->getValue();
```

### Step 3: Update DeliveryInfo (NOT Available in V2)

⚠️ **IMPORTANT:** DeliveryInfo (IsPrimeEligible, IsAmazonFulfilled, etc.) is **NOT** available in OffersV2 yet.

If you need Prime eligibility:
- Continue using Offers V1 for now for this specific field
- Or use DealDetails.AccessType to identify Prime Exclusive deals

```php
// Check if it's a Prime Exclusive Deal
$deal = $listing->getDealDetails();
if ($deal && $deal->getAccessType() === 'PRIME_EXCLUSIVE') {
    echo "This is a Prime Exclusive Deal!";
}
```

### Step 4: Use New Features

Take advantage of OffersV2 exclusive features:

#### Deal Information
```php
$dealListings = $offersV2->getDealListings();
foreach ($dealListings as $listing) {
    $deal = $listing->getDealDetails();
    echo "Deal Badge: " . $deal->getBadge() . "\n";
    echo "Access Type: " . $deal->getAccessType() . "\n";
    echo "End Time: " . $deal->getEndTime() . "\n";
}
```

#### Enhanced Savings
```php
$price = $listing->getPrice();
$savings = $price->getSavings();
if ($savings) {
    echo "Save: " . $savings->getMoney()->getDisplayAmount();
    echo " (" . $savings->getPercentage() . "%)\n";
}

$savingBasis = $price->getSavingBasis();
echo $savingBasis->getSavingBasisTypeLabel() . ": ";
echo $savingBasis->getMoney()->getDisplayAmount();
```

#### Merchant ID
```php
$merchant = $listing->getMerchantInfo();
echo "Merchant: " . $merchant->getName() . "\n";
echo "Merchant ID: " . $merchant->getId() . "\n";
```

#### Detailed Availability
```php
$availability = $listing->getAvailability();
echo "Status: " . $availability->getType() . "\n";
echo "Message: " . $availability->getMessage() . "\n";
echo "Min Order: " . $availability->getMinOrderQuantity() . "\n";
echo "Max Order: " . $availability->getMaxOrderQuantity() . "\n";
```

## Complete Resource List Comparison

### Offers V1 Resources (Deprecated)
```php
'Resources' => [
    'Offers.Listings.Availability.MaxOrderQuantity',
    'Offers.Listings.Availability.Message',
    'Offers.Listings.Availability.MinOrderQuantity',
    'Offers.Listings.Availability.Type',
    'Offers.Listings.Condition.Value',
    'Offers.Listings.Condition.ConditionNote',
    'Offers.Listings.Condition.SubCondition',
    'Offers.Listings.DeliveryInfo.IsAmazonFulfilled',      // Not in V2
    'Offers.Listings.DeliveryInfo.IsFreeShippingEligible', // Not in V2
    'Offers.Listings.DeliveryInfo.IsPrimeEligible',        // Not in V2
    'Offers.Listings.IsBuyBoxWinner',
    'Offers.Listings.LoyaltyPoints.Points',
    'Offers.Listings.MerchantInfo.Name',
    'Offers.Listings.Price',
    'Offers.Summaries.HighestPrice',
    'Offers.Summaries.LowestPrice',
    'Offers.Summaries.OfferCount'
]
```

### OffersV2 Resources (Current)
```php
'Resources' => [
    // Availability
    'OffersV2.Listings.Availability.MaxOrderQuantity',
    'OffersV2.Listings.Availability.Message',
    'OffersV2.Listings.Availability.MinOrderQuantity',
    'OffersV2.Listings.Availability.Type',
    
    // Condition
    'OffersV2.Listings.Condition.ConditionNote',
    'OffersV2.Listings.Condition.SubCondition',
    'OffersV2.Listings.Condition.Value',
    
    // Deal Details (NEW!)
    'OffersV2.Listings.DealDetails.AccessType',
    'OffersV2.Listings.DealDetails.Badge',
    'OffersV2.Listings.DealDetails.EarlyAccessDurationInMilliseconds',
    'OffersV2.Listings.DealDetails.EndTime',
    'OffersV2.Listings.DealDetails.PercentClaimed',
    'OffersV2.Listings.DealDetails.StartTime',
    
    // Other
    'OffersV2.Listings.IsBuyBoxWinner',
    'OffersV2.Listings.LoyaltyPoints.Points',
    
    // Merchant Info (Enhanced!)
    'OffersV2.Listings.MerchantInfo.Id',   // NEW!
    'OffersV2.Listings.MerchantInfo.Name',
    
    // Price (Enhanced!)
    'OffersV2.Listings.Price.Money',
    'OffersV2.Listings.Price.PricePerUnit',
    'OffersV2.Listings.Price.SavingBasis.Money',
    'OffersV2.Listings.Price.SavingBasis.SavingBasisType',
    'OffersV2.Listings.Price.SavingBasis.SavingBasisTypeLabel',
    'OffersV2.Listings.Price.Savings.Money',
    'OffersV2.Listings.Price.Savings.Percentage',
    
    // Type and MAP
    'OffersV2.Listings.Type',
    'OffersV2.Listings.ViolatesMAP'
]
```

## Common Migration Patterns

### Pattern 1: Get Best Price

**Before (V1):**
```php
$offers = $item->getOffers();
$bestPrice = PHP_FLOAT_MAX;
foreach ($offers['Listings'] as $listing) {
    $price = $listing['Price']['Amount'] ?? PHP_FLOAT_MAX;
    if ($price < $bestPrice) {
        $bestPrice = $price;
    }
}
```

**After (V2):**
```php
$offersV2 = $item->getOffersV2();
$buyBoxWinner = $offersV2->getBuyBoxWinner();
$bestPrice = $buyBoxWinner ? 
    $buyBoxWinner->getPrice()->getMoney()->getAmount() : 
    null;
```

### Pattern 2: Check Prime Eligibility

**Before (V1):**
```php
$offers = $item->getOffers();
$isPrime = $offers['Listings'][0]['DeliveryInfo']['IsPrimeEligible'] ?? false;
```

**After (V2):**
```php
// Option 1: Check for Prime Exclusive Deal
$offersV2 = $item->getOffersV2();
$buyBoxWinner = $offersV2->getBuyBoxWinner();
$deal = $buyBoxWinner->getDealDetails();
$isPrimeExclusive = $deal && $deal->getAccessType() === 'PRIME_EXCLUSIVE';

// Option 2: Continue using Offers V1 for DeliveryInfo (until V2 supports it)
$offers = $item->getOffers();
$isPrime = $offers['Listings'][0]['DeliveryInfo']['IsPrimeEligible'] ?? false;
```

### Pattern 3: Get All Available Prices

**Before (V1):**
```php
$offers = $item->getOffers();
$prices = [];
foreach ($offers['Listings'] as $listing) {
    $prices[] = $listing['Price']['DisplayAmount'] ?? 'N/A';
}
```

**After (V2):**
```php
$offersV2 = $item->getOffersV2();
$prices = [];
foreach ($offersV2->getListings() as $listing) {
    $money = $listing->getPrice()?->getMoney();
    if ($money) {
        $prices[] = $money->getDisplayAmount();
    }
}
```

### Pattern 4: Find Lightning Deals

**V1 - Not Available**

**V2 - Built-in Support:**
```php
$offersV2 = $item->getOffersV2();
$dealListings = $offersV2->getDealListings();

foreach ($dealListings as $listing) {
    if ($listing->getType() === 'LIGHTNING_DEAL') {
        $deal = $listing->getDealDetails();
        echo "Lightning Deal: " . $deal->getBadge() . "\n";
        echo "Ends: " . $deal->getEndTime() . "\n";
    }
}
```

## Testing Your Migration

Create a test to ensure both V1 and V2 return similar data:

```php
<?php

use AmazonPaapi5\Client;
use AmazonPaapi5\Config;
use AmazonPaapi5\Models\Request\GetItemsRequest;
use AmazonPaapi5\Models\Item;

$config = new Config([...]);
$client = new Client($config);

// Test with both
$asin = 'B00MNV8E0C';

// V1 Request
$requestV1 = new GetItemsRequest([
    'ItemIds' => [$asin],
    'Resources' => ['Offers.Listings.Price', 'Offers.Listings.Condition']
]);

// V2 Request
$requestV2 = new GetItemsRequest([
    'ItemIds' => [$asin],
    'Resources' => ['OffersV2.Listings.Price', 'OffersV2.Listings.Condition']
]);

$responseV1 = $client->getItems($requestV1);
$responseV2 = $client->getItems($requestV2);

$itemV1 = new Item($responseV1['ItemsResult']['Items'][0]);
$itemV2 = new Item($responseV2['ItemsResult']['Items'][0]);

// Compare
$offersV1 = $itemV1->getOffers();
$offersV2 = $itemV2->getOffersV2();

echo "V1 Price: " . ($offersV1['Listings'][0]['Price']['DisplayAmount'] ?? 'N/A') . "\n";
echo "V2 Price: " . ($offersV2->getBuyBoxWinner()?->getPrice()?->getMoney()?->getDisplayAmount() ?? 'N/A') . "\n";
```

## Timeline & Recommendations

| Timeline | Action |
|----------|--------|
| **Now** | ✅ Start using OffersV2 for all new code |
| **Q1 2026** | ⚠️ Plan migration for existing code |
| **Q2-Q3 2026** | ⚠️ Test thoroughly with OffersV2 |
| **Q4 2026+** | ❌ Offers V1 may be removed by Amazon |

## Need Help?

- See [OFFERSV2_README.md](OFFERSV2_README.md) for complete OffersV2 documentation
- Check [examples/offersv2_example.php](examples/offersv2_example.php) for working examples
- Visit [Amazon PA-API OffersV2 Documentation](https://webservices.amazon.com/paapi5/documentation/offersV2.html)

## Checklist

- [ ] Updated all `Offers.` resources to `OffersV2.`
- [ ] Replaced array access with typed object methods
- [ ] Updated code to use `getOffersV2()` instead of `getOffers()`
- [ ] Handled DeliveryInfo migration (if needed)
- [ ] Tested with OffersV2 helper methods (getBuyBoxWinner, getDealListings)
- [ ] Updated deal detection to use DealDetails
- [ ] Tested Prime Exclusive deal handling
- [ ] Verified all price and savings calculations
- [ ] Updated merchant info access (added ID support)
- [ ] Tested availability messages and types

**Start migrating today!** OffersV2 is the future of Amazon PA-API offers. 🚀
