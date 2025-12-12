# Amazon PA-API 5.0 OffersV2 Implementation

This implementation provides full support for Amazon's new **OffersV2** API, which offers improved reliability, data quality, and new features compared to the original Offers API.

## What's New in OffersV2?

OffersV2 is Amazon's recommended API for retrieving offer information. Key improvements include:

- **Better Data Quality**: More reliable and accurate offer information
- **Deal Information**: Access to Lightning Deals, Prime Exclusive Deals, and more
- **Enhanced Price Data**: Detailed savings information including basis prices
- **Merchant Details**: Full merchant ID and name information
- **Availability Types**: More granular availability status (IN_STOCK, IN_STOCK_SCARCE, PREORDER, etc.)
- **Prime Early Access**: Support for Prime Early Access deals with timing information

## Features

### Core Models

The implementation includes the following models in the `AmazonPaapi5\Models\OffersV2` namespace:

- **OffersV2**: Main container for offer listings
- **OfferListing**: Individual offer listing with all details
- **Price**: Buying price with savings and unit pricing
- **Money**: Common money representation (amount, currency, display)
- **Availability**: Stock status and order quantity limits
- **Condition**: Product condition information
- **DealDetails**: Lightning deals and special offer information
- **MerchantInfo**: Seller/merchant details
- **SavingBasis**: Reference pricing for savings calculations
- **Savings**: Savings amount and percentage
- **LoyaltyPoints**: Loyalty points (Japan marketplace only)

### Key Features

1. **BuyBox Winner Detection**: Easily identify the featured offer
2. **Deal Filtering**: Get only listings with active deals
3. **Rich Price Information**: Access to prices, savings, and unit pricing
4. **Deal Badges**: Get display-ready badge text for deals
5. **Prime Early Access**: Full support for Prime-exclusive deals
6. **Type-Safe**: Strongly typed PHP models with proper null handling

## Usage

### Basic Usage

```php
use AmazonPaapi5\Client;
use AmazonPaapi5\Config;
use AmazonPaapi5\Operations\GetItems;
use AmazonPaapi5\Models\Request\GetItemsRequest;
use AmazonPaapi5\Models\Item;

// Configure client
$config = new Config([
    'accessKey' => 'YOUR_ACCESS_KEY',
    'secretKey' => 'YOUR_SECRET_KEY',
    'partnerTag' => 'YOUR_PARTNER_TAG',
    'marketplace' => 'US'
]);

$client = new Client($config);

// Request with OffersV2 resources
$request = new GetItemsRequest([
    'ItemIds' => ['B00MNV8E0C'],
    'Resources' => [
        'OffersV2.Listings.Price.Money',
        'OffersV2.Listings.Availability.Type',
        'OffersV2.Listings.Condition.Value',
        'OffersV2.Listings.MerchantInfo.Name',
        'OffersV2.Listings.IsBuyBoxWinner',
        // Add more resources as needed
    ]
]);

$response = $client->getItems($request);
$item = new Item($response['ItemsResult']['Items'][0]);

// Access OffersV2 data
$offersV2 = $item->getOffersV2();
```

### Get BuyBox Winner

```php
$offersV2 = $item->getOffersV2();

if ($offersV2) {
    $buyBoxWinner = $offersV2->getBuyBoxWinner();
    
    if ($buyBoxWinner) {
        $price = $buyBoxWinner->getPrice();
        echo "Price: " . $price->getMoney()->getDisplayAmount();
        
        $merchant = $buyBoxWinner->getMerchantInfo();
        echo "Sold by: " . $merchant->getName();
    }
}
```

### Get All Listings

```php
$offersV2 = $item->getOffersV2();
$listings = $offersV2->getListings();

foreach ($listings as $listing) {
    $price = $listing->getPrice();
    $availability = $listing->getAvailability();
    
    echo "Price: " . $price->getMoney()->getDisplayAmount() . "\n";
    echo "Status: " . $availability->getType() . "\n";
    
    if ($listing->isBuyBoxWinner()) {
        echo "*** BuyBox Winner ***\n";
    }
}
```

### Check for Deals

```php
$offersV2 = $item->getOffersV2();
$dealListings = $offersV2->getDealListings();

foreach ($dealListings as $listing) {
    $deal = $listing->getDealDetails();
    
    echo "Deal Badge: " . $deal->getBadge() . "\n";
    echo "Access Type: " . $deal->getAccessType() . "\n";
    
    if ($deal->getEndTime()) {
        echo "Ends: " . $deal->getEndTime() . "\n";
    }
    
    if ($deal->getPercentClaimed()) {
        echo "Claimed: " . $deal->getPercentClaimed() . "%\n";
    }
}
```

### Get Price with Savings

```php
$listing = $offersV2->getBuyBoxWinner();
$price = $listing->getPrice();

// Current price
$currentPrice = $price->getMoney();
echo "Current: " . $currentPrice->getDisplayAmount() . "\n";

// Savings
$savings = $price->getSavings();
if ($savings) {
    echo "Save: " . $savings->getMoney()->getDisplayAmount();
    echo " (" . $savings->getPercentage() . "%)\n";
}

// Original price
$savingBasis = $price->getSavingBasis();
if ($savingBasis) {
    echo $savingBasis->getSavingBasisTypeLabel() . ": ";
    echo $savingBasis->getMoney()->getDisplayAmount() . "\n";
}

// Price per unit
$pricePerUnit = $price->getPricePerUnit();
if ($pricePerUnit) {
    echo "Unit Price: " . $pricePerUnit->getDisplayAmount() . "\n";
}
```

### Check Availability

```php
$availability = $listing->getAvailability();

echo "Status: " . $availability->getType() . "\n";
echo "Message: " . $availability->getMessage() . "\n";
echo "Min Order: " . $availability->getMinOrderQuantity() . "\n";
echo "Max Order: " . $availability->getMaxOrderQuantity() . "\n";
```

## Available Resources

When making requests, you can specify which OffersV2 resources to include:

### Listings Resources

- `OffersV2.Listings.Availability.MaxOrderQuantity`
- `OffersV2.Listings.Availability.Message`
- `OffersV2.Listings.Availability.MinOrderQuantity`
- `OffersV2.Listings.Availability.Type`

### Condition Resources

- `OffersV2.Listings.Condition.ConditionNote`
- `OffersV2.Listings.Condition.SubCondition`
- `OffersV2.Listings.Condition.Value`

### Deal Resources

- `OffersV2.Listings.DealDetails.AccessType`
- `OffersV2.Listings.DealDetails.Badge`
- `OffersV2.Listings.DealDetails.EarlyAccessDurationInMilliseconds`
- `OffersV2.Listings.DealDetails.EndTime`
- `OffersV2.Listings.DealDetails.PercentClaimed`
- `OffersV2.Listings.DealDetails.StartTime`

### Other Resources

- `OffersV2.Listings.IsBuyBoxWinner`
- `OffersV2.Listings.LoyaltyPoints.Points`
- `OffersV2.Listings.MerchantInfo.Id`
- `OffersV2.Listings.MerchantInfo.Name`
- `OffersV2.Listings.Type`
- `OffersV2.Listings.ViolatesMAP`

### Price Resources

- `OffersV2.Listings.Price.Money`
- `OffersV2.Listings.Price.PricePerUnit`
- `OffersV2.Listings.Price.SavingBasis.Money`
- `OffersV2.Listings.Price.SavingBasis.SavingBasisType`
- `OffersV2.Listings.Price.SavingBasis.SavingBasisTypeLabel`
- `OffersV2.Listings.Price.Savings.Money`
- `OffersV2.Listings.Price.Savings.Percentage`

## Supported Operations

OffersV2 is available in the following operations:

- **GetItems**: Get offers for specific ASINs
- **SearchItems**: Get offers in search results
- **GetVariations**: Get offers for product variations

## Important Notes

### Availability Types

The `Availability.Type` field can have the following values:

- `AVAILABLE_DATE`: Item available on a future date
- `IN_STOCK`: Item is in stock
- `IN_STOCK_SCARCE`: Item in stock but limited quantity
- `LEADTIME`: Item available after lead time
- `OUT_OF_STOCK`: Currently out of stock
- `PREORDER`: Available for pre-order
- `UNAVAILABLE`: Not available
- `UNKNOWN`: Unknown availability

### Deal Access Types

The `DealDetails.AccessType` field indicates who can claim the deal:

- `ALL`: Available to all customers
- `PRIME_EARLY_ACCESS`: Available to Prime members first, then all customers
- `PRIME_EXCLUSIVE`: Available only to Prime members

### Condition Values

Product condition can be:

- `New`: New product
- `Used`: Used product
- `Refurbished`: Refurbished product
- `Unknown`: Unknown condition

### SubCondition Values

For used items, subcondition provides more details:

- `LikeNew`: Like new condition
- `Good`: Good condition
- `VeryGood`: Very good condition
- `Acceptable`: Acceptable condition
- `Refurbished`: Refurbished
- `OEM`: OEM product
- `OpenBox`: Open box
- `Unknown`: Unknown subcondition

### Offer Types

The `Type` field distinguishes special offer types:

- `LIGHTNING_DEAL`: Lightning deal
- `SUBSCRIBE_AND_SAVE`: Subscribe and Save offer
- `null`: Regular listing (most common)

### Minimum Advertised Price (MAP)

If `ViolatesMAP` is `true`, the manufacturer doesn't allow the price to be displayed. Customers must add to cart or proceed to checkout to see the price.

## Examples

See the `examples/offersv2_example.php` file for a complete working example demonstrating all features.

## Migration from Offers V1

If you're currently using the old Offers API, here's how to migrate:

### Old Way (Offers V1)
```php
$offers = $item->getOffers();
// Raw array access
```

### New Way (OffersV2)
```php
$offersV2 = $item->getOffersV2();
$buyBoxWinner = $offersV2->getBuyBoxWinner();
$price = $buyBoxWinner->getPrice()->getMoney()->getDisplayAmount();
// Strongly typed objects with methods
```

## Benefits Over Offers V1

1. **Better Reliability**: More stable and consistent data
2. **More Features**: Deal information, enhanced pricing, merchant IDs
3. **Type Safety**: Strongly typed models instead of raw arrays
4. **Better Documentation**: Clear field meanings and valid values
5. **Future-Proof**: All new features will be added to OffersV2 only

## API Documentation

For the official Amazon PA-API 5.0 OffersV2 documentation, visit:
https://webservices.amazon.com/paapi5/documentation/offersv2.html

## Changelog

### Version 1.2.4 (November 2025)
- Added OffersV2 support for GetVariations

### Version 1.2.3 (November 2025)
- Added OffersV2 support for SearchItems

### Version 1.2.2 (February 2025)
- Added `DealDetails.Badge` field
- Initial OffersV2 SDK release

### Version Updates (February 2025)
- Added `Availability.Message` field
- Expanded `Availability.Type` values
- Added `MerchantInfo.Id` field
- Added Prime Early Access support in `DealDetails`

## Support

For issues or questions about this implementation, please open an issue on GitHub.

## License

This implementation follows the same license as the main amazon-paapi5-php-sdk package.
