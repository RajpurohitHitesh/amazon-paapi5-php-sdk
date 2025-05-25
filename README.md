# Amazon Product Advertising API 5.0 PHP SDK

![Static Badge](https://img.shields.io/badge/PHP-8.0%2B-purple)
[![Amazon API](https://img.shields.io/badge/Amazon%20API-5.0-%23FD9B15)](https://webservices.amazon.com/paapi5/documentation/)
[![Version](https://img.shields.io/packagist/v/rajpurohithitesh/amazon-paapi5-php-sdk)](https://img.shields.io/packagist/v/rajpurohithitesh/amazon-paapi5-php-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/rajpurohithitesh/amazon-paapi5-php-sdk.svg?style=flat)](https://packagist.org/packages/rajpurohithitesh/amazon-paapi5-php-sdk)
![Static Badge](https://img.shields.io/badge/License-Apache_2.0-blue)


[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=RajpurohitHitesh_amazon-paapi5-php-sdk&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=RajpurohitHitesh_amazon-paapi5-php-sdk)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=RajpurohitHitesh_amazon-paapi5-php-sdk&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=RajpurohitHitesh_amazon-paapi5-php-sdk)
[![CI](https://github.com/RajpurohitHitesh/amazon-paapi5-php-sdk/actions/workflows/ci.yml/badge.svg)](https://github.com/RajpurohitHitesh/amazon-paapi5-php-sdk/actions/workflows/ci.yml)
[![PHP Composer](https://github.com/RajpurohitHitesh/amazon-paapi5-php-sdk/actions/workflows/php.yml/badge.svg)](https://github.com/RajpurohitHitesh/amazon-paapi5-php-sdk/actions/workflows/php.yml)

This repository provides a lightweight, modern PHP SDK for the Amazon Product [Advertising API 5.0](https://webservices.amazon.com/paapi5/documentation/index.html), enabling developers to integrate Amazon product data into their PHP applications. The SDK is designed for PHP 7.4+ and emphasizes performance, modularity, and ease of use with features like smart throttling, PSR-6 caching, async support, and type-safe objects.

## Key Features


* Supported Operations: SearchItems, GetItems, GetVariations, and GetBrowseNodes with full request/response handling.



* Smart Throttling: Configurable delay (default: 1s), exponential backoff, and queue management to handle Amazon's rate limits.



* Caching: PSR-6 compliant caching with a built-in file-based implementation and support for external caches (e.g., Redis, Memcached).



* Async Support: Non-blocking API calls using Guzzle promises for parallel execution and promise chaining.



* Type-Safe Objects: Strictly typed request/response models with auto-completion-friendly methods.



* Marketplace Support: Covers all major Amazon marketplaces (US, UK, JP, DE, FR, CA, AU, IN) with auto-region detection.



* Security: Credential encryption, HTTPS enforcement, AWS Signature V4 signing, and header injection protection.



* Performance: Connection reuse, Gzip compression, batch processing (up to 10 ASINs), and memory-efficient parsing.



* Error Handling: Custom exception hierarchy (AuthenticationException, ThrottleException, etc.) with recovery suggestions.



* Lightweight: Minimal dependencies (GuzzleHttp only), ~150 KB source files, PSR-12 compliant.

## Installation

The Amazon PAAPI 5.0 PHP SDK is available via Packagist and can be installed using Composer. Run the following command in your project's root directory:

```sh
composer require yourvendor/amazon-paapi5-php-sdk
```

### Requirements

* PHP: 7.4 or higher

* GuzzleHttp: ^7.0 (required for HTTP requests)

* PSR-6 Cache: Optional, only needed for external cache implementations (e.g., Redis, Memcached)

If you plan to use an external PSR-6 cache, install a compatible package, such as:
```sh
composer require symfony/cache
```

## Usage

This SDK simplifies interaction with the Amazon Product Advertising API 5.0. Below are detailed examples for each supported operation: SearchItems, GetItems, GetVariations, and GetBrowseNodes. All examples assume you have valid Amazon Associate credentials (Access Key, Secret Key, Partner Tag).

### Setup

First, configure the SDK with your credentials and an encryption key for secure credential storage. You can also specify the marketplace (e.g., www.amazon.in for India).

```php
<?php
use AmazonPaapi5\Client;
use AmazonPaapi5\Config;

require_once 'vendor/autoload.php';

// Initialize configuration
$config = new Config(
    '<YOUR_ACCESS_KEY>',
    '<YOUR_SECRET_KEY>',
    '<YOUR_PARTNER_TAG>',
    '<YOUR_ENCRYPTION_KEY>' // Secure key for credential encryption
);

// Set marketplace (e.g., India)
$config->setMarketplace('www.amazon.in');

// Create client
$client = new Client($config);
```
### Example 1: SearchItems

Search for products by keywords in a specific category (e.g., "Laptop" in Electronics).
```php
use AmazonPaapi5\Operations\SearchItems;
use AmazonPaapi5\Models\Request\SearchItemsRequest;

$request = (new SearchItemsRequest())
    ->setPartnerTag('<YOUR_PARTNER_TAG>')
    ->setKeywords('Laptop')
    ->setSearchIndex('Electronics')
    ->setItemCount(5)
    ->setResources([
        'ItemInfo.Title',
        'Offers.Listings.Price',
        'Images.Primary.Medium'
    ]);

$operation = new SearchItems($request);

try {
    $response = $client->sendAsync($operation)->wait();
    echo "Search Results:\n";
    foreach ($response->getItems() as $item) {
        $title = $item->getItemInfo()['Title']['DisplayValue'] ?? 'N/A';
        $price = $item->getOffers()[0]['Listings'][0]['Price']['DisplayAmount'] ?? 'N/A';
        echo "Title: $title\nPrice: $price\nASIN: {$item->getAsin()}\n\n";
    }
} catch (\AmazonPaapi5\Exceptions\ApiException $e) {
    echo "Error: {$e->getMessage()}\n";
    echo "Metadata: " . print_r($e->getMetadata(), true) . "\n";
}
```
### Output Example:
```
Search Results:
Title: Dell Inspiron 15 Laptop
Price: ₹45,999.00
ASIN: B08X4N3DW1

Title: HP Pavilion Gaming Laptop
Price: ₹62,490.00
ASIN: B09F3T2K7P
```
### Example 2: GetItems

Retrieve details for specific products by their ASINs (up to 10).
```php
use AmazonPaapi5\Operations\GetItems;
use AmazonPaapi5\Models\Request\GetItemsRequest;

$request = (new GetItemsRequest())
    ->setPartnerTag('<YOUR_PARTNER_TAG>')
    ->setItemIds(['B08X4N3DW1', 'B09F3T2K7P'])
    ->setResources([
        'ItemInfo.Title',
        'Images.Primary.Medium',
        'Offers.Listings.Price'
    ]);

$operation = new GetItems($request);

try {
    $response = $client->sendAsync($operation)->wait();
    echo "Product Details:\n";
    foreach ($response->getItems() as $item) {
        $title = $item->getItemInfo()['Title']['DisplayValue'] ?? 'N/A';
        $image = $item->getImages()['Primary']['Medium']['URL'] ?? 'N/A';
        echo "ASIN: {$item->getAsin()}\nTitle: $title\nImage: $image\n\n";
    }
} catch (\AmazonPaapi5\Exceptions\RequestException $e) {
    echo "Request Error: {$e->getMessage()}\n";
    echo "Metadata: " . print_r($e->getMetadata(), true) . "\n";
}
```

### Output Example:
```
Product Details:
ASIN: B08X4N3DW1
Title: Dell Inspiron 15 Laptop
Image: https://m.media-amazon.com/images/I/41Wd1X9x5zL._SL75_.jpg

ASIN: B09F3T2K7P
Title: HP Pavilion Gaming Laptop
Image: https://m.media-amazon.com/images/I/51X9Yf3Y5kL._SL75_.jpg
```

### Example 3: GetVariations

Fetch variations (e.g., colors, sizes) for a specific product by ASIN.
```php
use AmazonPaapi5\Operations\GetVariations;
use AmazonPaapi5\Models\Request\GetVariationsRequest;

$request = (new GetVariationsRequest())
    ->setPartnerTag('<YOUR_PARTNER_TAG>')
    ->setAsin('B08X4N3DW1')
    ->setResources([
        'ItemInfo.Title',
        'VariationAttributes',
        'Offers.Listings.Price'
    ]);

$operation = new GetVariations($request);

try {
    $response = $client->sendAsync($operation)->wait();
    echo "Product Variations:\n";
    foreach ($response->getVariations() as $variation) {
        $title = $variation->getItemInfo()['Title']['DisplayValue'] ?? 'N/A';
        $attributes = $variation->getVariationAttributes() ?? [];
        echo "ASIN: {$variation->getAsin()}\nTitle: $title\n";
        foreach ($attributes as $attr) {
            echo "{$attr['Name']}: {$attr['Value']}\n";
        }
        echo "\n";
    }
} catch (\AmazonPaapi5\Exceptions\ThrottleException $e) {
    echo "Throttling Error: {$e->getMessage()}\n";
    echo "Suggestion: Increase throttle delay or reduce request frequency.\n";
}
```

### Output Example:
```
Product Variations:
ASIN: B08X4N3DW1
Title: Dell Inspiron 15 Laptop (Silver)
Color: Silver
Size: 15.6 inch

ASIN: B08X4N3DX2
Title: Dell Inspiron 15 Laptop (Black)
Color: Black
Size: 15.6 inch
```

### Example 4: GetBrowseNodes

Retrieve category hierarchy for navigation or product filtering.
```php
use AmazonPaapi5\Operations\GetBrowseNodes;
use AmazonPaapi5\Models\Request\GetBrowseNodesRequest;

$request = (new GetBrowseNodesRequest())
    ->setPartnerTag('<YOUR_PARTNER_TAG>')
    ->setBrowseNodeIds(['493964']) // Electronics category
    ->setResources([
        'BrowseNodes.Ancestor',
        'BrowseNodes.Children'
    ]);

$operation = new GetBrowseNodes($request);

try {
    $response = $client->sendAsync($operation)->wait();
    echo "Category Hierarchy:\n";
    foreach ($response->getNodes() as $node) {
        $name = $node['DisplayName'] ?? 'N/A';
        $id = $node['Id'] ?? 'N/A';
        echo "Node: $name (ID: $id)\n";
        if (isset($node['Children'])) {
            echo "Children:\n";
            foreach ($node['Children'] as $child) {
                echo "- {$child['DisplayName']} (ID: {$child['Id']})\n";
            }
        }
        if (isset($node['Ancestor'])) {
            echo "Parent: {$node['Ancestor']['DisplayName']} (ID: {$node['Ancestor']['Id']})\n";
        }
        echo "\n";
    }
} catch (\AmazonPaapi5\Exceptions\AuthenticationException $e) {
    echo "Authentication Error: {$e->getMessage()}\n";
    echo "Suggestion: Verify your AWS credentials.\n";
}
```
Output Example:
```
Category Hierarchy:
Node: Electronics (ID: 493964)
Children:
- Computers & Accessories (ID: 976419031)
- Mobile Phones (ID: 1389432031)
Parent: All Departments (ID: 976389031)
```
## Configuration Options

### Marketplace Selection

The SDK supports all major Amazon marketplaces. Set the desired marketplace using the setMarketplace method:
```php
$config->setMarketplace('www.amazon.in'); // India
// Other options: www.amazon.com (US), www.amazon.co.uk (UK), www.amazon.de (DE), etc.
```
#### Supported marketplaces:

* India (www.amazon.in)

* US (www.amazon.com)

* UK (www.amazon.co.uk)

* Germany (www.amazon.de)

* France (www.amazon.fr)

* Japan (www.amazon.co.jp)

* Canada (www.amazon.ca)

* Australia (www.amazon.com.au)

### Throttling

The SDK includes smart throttling to comply with Amazon's rate limits (1 request/second for low-tier accounts). Configure the throttle delay (in seconds):
```php
$config->setThrottleDelay(1.5); // 1.5 seconds between requests
```
### Caching

The SDK provides a built-in FileCache implementation (PSR-6 compliant) to reduce API calls. Configure the cache TTL (in seconds):
```php
$config->setCacheTtl(7200); // Cache responses for 2 hours
```
To use an external PSR-6 cache (e.g., Redis):
```php
use Symfony\Component\Cache\Adapter\RedisAdapter;

$cache = new RedisAdapter(RedisAdapter::createConnection('redis://localhost'));
$client = new Client($config, $cache);
```
Cache invalidation occurs automatically when new data is fetched for the same request, ensuring fresh results.

### Async and Batch Processing

Execute multiple operations in parallel using async calls or batch processing:
```php
$operations = [
    new SearchItems($searchRequest),
    new GetItems($itemsRequest),
    new GetVariations($variationsRequest)
];
$responses = $client->executeBatch($operations);

foreach ($responses as $response) {
    // Process each response
}
```
## Error Handling

The SDK provides a custom exception hierarchy for robust error handling:

* AuthenticationException: Invalid credentials or region settings.

* ThrottleException: Rate limit exceeded.

* RequestException: Invalid request parameters.

* ApiException: General API errors.

#### Example:
```php
try {
    $response = $client->sendAsync($operation)->wait();
} catch (\AmazonPaapi5\Exceptions\ThrottleException $e) {
    echo "Error: {$e->getMessage()}\n";
    echo "Metadata: " . print_r($e->getMetadata(), true) . "\n";
    echo "Suggestion: Increase throttle delay or retry later.\n";
}
```
## Security

* Credential Encryption: Credentials are encrypted using OpenSSL with a user-provided encryption key.

* HTTPS Enforcement: All requests use HTTPS.

* AWS Signature V4: Requests are signed for secure authentication.

* Header Injection Protection: Strict header validation prevents injection attacks.

## Performance Optimizations

* Batch Processing: Fetch up to 10 ASINs in a single GetItems request.

* Gzip Compression: Reduces network overhead.

* Connection Reuse: cURL keep-alive for faster requests.

* Memory Efficiency: Type-safe objects and optimized parsing minimize memory usage.

## Contributing

We welcome contributions! Please see CONTRIBUTING.md for details on how to contribute, including submitting issues, pull requests, and coding guidelines.

## Documentation

For complete API details, refer to the Amazon Product Advertising API 5.0 Documentation.

Key resources:

* SearchItems

* GetItems

* GetVariations

* GetBrowseNodes

* Common Request Parameters

## License

This SDK is distributed under the Apache License, Version 2.0. See LICENSE.txt and NOTICE.txt for more information.
