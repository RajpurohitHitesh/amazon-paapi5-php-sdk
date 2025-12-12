# Amazon Product Advertising API 5.0 PHP SDK

![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-purple)
[![Amazon API](https://img.shields.io/badge/Amazon%20API-5.0-%23FD9B15)](https://webservices.amazon.com/paapi5/documentation/)
[![Latest Version](https://img.shields.io/packagist/v/rajpurohithitesh/amazon-paapi5-php-sdk)](https://packagist.org/packages/rajpurohithitesh/amazon-paapi5-php-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/rajpurohithitesh/amazon-paapi5-php-sdk.svg?style=flat)](https://packagist.org/packages/rajpurohithitesh/amazon-paapi5-php-sdk)
[![License](https://img.shields.io/badge/License-Apache_2.0-blue)](LICENSE.txt)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=RajpurohitHitesh_amazon-paapi5-php-sdk&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=RajpurohitHitesh_amazon-paapi5-php-sdk)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=RajpurohitHitesh_amazon-paapi5-php-sdk&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=RajpurohitHitesh_amazon-paapi5-php-sdk)
[![CI](https://github.com/RajpurohitHitesh/amazon-paapi5-php-sdk/actions/workflows/ci.yml/badge.svg)](https://github.com/RajpurohitHitesh/amazon-paapi5-php-sdk/actions/workflows/ci.yml)
[![PHP Composer](https://github.com/RajpurohitHitesh/amazon-paapi5-php-sdk/actions/workflows/php.yml/badge.svg)](https://github.com/RajpurohitHitesh/amazon-paapi5-php-sdk/actions/workflows/php.yml)

This repository provides a lightweight, modern, and feature-rich PHP SDK for the Amazon Product Advertising API 5.0 (PAAPI5). It is designed to simplify the integration of Amazon product data into your PHP applications, offering robust error handling, caching, and support for all PAAPI5 operations.

## ⚠️ IMPORTANT: Migrate to OffersV2

**Offers V1 is being deprecated!** Amazon PA-API will eventually remove the old Offers API. All new features are only being added to OffersV2.

**➡️ [Read Migration Guide: Offers V1 → OffersV2](MIGRATION_OFFERS_V1_TO_V2.md)**

**Use OffersV2 for all new projects:**
- ✅ Better reliability and data quality
- ✅ Full deal information (Lightning Deals, Prime Exclusive)
- ✅ Enhanced pricing with savings details
- ✅ Merchant ID support
- ✅ All future features

See [OffersV2 Documentation](OFFERSV2_README.md) for complete details.

## Table of Contents

1.  [Introduction](#introduction)
    *   [What is this SDK?](#what-is-this-sdk)
    *   [Who is it for?](#who-is-it-for)
    *   [Benefits of using this SDK](#benefits-of-using-this-sdk)
2.  [Key Features](#key-features)
3.  [Requirements](#requirements)
4.  [Installation](#installation)
    *   [Using Composer](#using-composer)
    *   [External Cache Packages (Optional)](#external-cache-packages-optional)
5.  [Getting Started: Basic Setup](#getting-started-basic-setup)
    *   [Prerequisites: Amazon PAAPI Credentials](#prerequisites-amazon-paapi-credentials)
    *   [Including the Autoloader](#including-the-autoloader)
    *   [Initializing Configuration (`Config` class)](#initializing-configuration-config-class)
    *   [Initializing the Client (`Client` class)](#initializing-the-client-client-class)
6.  [Core Concepts](#core-concepts)
    *   [Operations](#operations)
    *   [Request Objects](#request-objects)
    *   [Response Objects](#response-objects)
7.  [Making API Calls: Supported Operations](#making-api-calls-supported-operations)
    *   [7.1. Searching for Items (`SearchItems`)](#71-searching-for-items-searchitems)
    *   [7.2. Getting Item Details (`GetItems`)](#72-getting-item-details-getitems)
    *   [7.3. Getting Product Variations (`GetVariations`)](#73-getting-product-variations-getvariations)
    *   [7.4. Getting Browse Nodes (`GetBrowseNodes`)](#74-getting-browse-nodes-getbrowsenodes)
8.  [Advanced Usage](#advanced-usage)
    *   [8.1. Asynchronous Requests](#81-asynchronous-requests)
    *   [8.2. Batch Operations (Conceptual)](#82-batch-operations-conceptual)
9.  [Configuration In-Depth](#configuration-in-depth)
    *   [9.1. Marketplace Configuration](#91-marketplace-configuration)
    *   [9.2. Throttling and Rate Limiting](#92-throttling-and-rate-limiting)
    *   [9.3. Caching Strategy](#93-caching-strategy)
    *   [9.4. Credential Management and Encryption](#94-credential-management-and-encryption)
    *   [9.5. Logging (PSR-3)](#95-logging-psr-3)
10. [Error Handling and Exceptions](#error-handling-and-exceptions)
    *   [Custom Exception Hierarchy](#custom-exception-hierarchy)
    *   [Handling Exceptions](#handling-exceptions)
11. [Security Best Practices](#security-best-practices)
12. [Performance Considerations](#performance-considerations)
13. [Contributing](#contributing)
14. [Official Amazon PAAPI Documentation](#official-amazon-paapi-documentation)

## 1. Introduction

### What is this SDK?
The Amazon Product Advertising API 5.0 PHP SDK is a powerful library that allows PHP developers to easily access Amazon's vast product catalog and advertising functionalities. It handles the complexities of API requests, authentication, and response parsing, letting you focus on building features for your application.

### Who is it for?
This SDK is for PHP developers who want to:
*   Display Amazon product information (details, prices, images, reviews) on their websites or applications.
*   Search for Amazon products based on various criteria.
*   Retrieve information about product variations (e.g., different sizes or colors).
*   Explore Amazon's product category structure (browse nodes).
*   Build affiliate marketing solutions by leveraging Amazon's product data.

### Benefits of using this SDK
*   **Simplified API Interaction:** Abstracts the low-level details of HTTP requests, signing, and XML/JSON parsing.
*   **Modern PHP Practices:** Utilizes modern PHP features, PSR standards, and a clean architecture.
*   **Time-Saving:** Reduces development time with pre-built functionalities for all PAAPI5 operations.
*   **Robust and Reliable:** Includes features like smart throttling, caching, and comprehensive error handling.
*   **Secure:** Implements AWS Signature V4 and credential encryption.

## 2. Key Features

This SDK is packed with features to make your development experience smooth and efficient:

*   **Full PAAPI5 Operation Support:**
    *   `SearchItems`: Find products based on keywords, category, and other filters.
    *   `GetItems`: Retrieve detailed information for specific products using their ASINs or other identifiers.
    *   `GetVariations`: Fetch available variations (like size, color) for a given product.
    *   `GetBrowseNodes`: Access Amazon's category hierarchy.
*   **Smart Throttling & Rate Limiting:**
    *   Configurable delay between requests (default: 1 second).
    *   Automatic request queueing and exponential backoff (conceptual, primarily managed by `ThrottleManager` delay) to respect Amazon's API rate limits.
*   **PSR-6 Compliant Caching:**
    *   Includes a built-in `FileCache` and `AdvancedCache` implementation.
    *   Easily integrate external caching solutions like Redis or Memcached that implement `Psr\Cache\CacheItemPoolInterface`.
    *   Configurable cache Time-To-Live (TTL).
*   **Asynchronous Operations:**
    *   Leverages Guzzle promises for non-blocking API calls, enabling parallel execution and efficient resource utilization.
*   **Type-Safe Request/Response Models:**
    *   Strictly typed PHP objects for building requests and handling responses.
    *   Improves code reliability and enables better autocompletion in IDEs. (Located in `src/Models/Request/` and `src/Models/Response/`)
*   **Comprehensive Marketplace Support:**
    *   Supports all major Amazon marketplaces (e.g., US, UK, DE, JP, IN, CA, AU).
    *   Automatic detection and configuration of regional API endpoints via the `Marketplace` class.
*   **Robust Security:**
    *   **Credential Encryption:** Uses OpenSSL (AES-256-CBC) to encrypt your AWS Access Key and Secret Key when an `encryption_key` is provided in the configuration. Managed by `Security\CredentialManager`.
    *   **Advanced Encryption with Intelligent Fallback:**
    *   **Primary Method - Sodium:** High-performance libsodium encryption with ChaCha20-Poly1305 authenticated encryption
    *   **Fallback Method - OpenSSL:** AES-256-GCM encryption when Sodium is unavailable
    *   **Automatic Detection:** Seamlessly switches between encryption methods based on server capabilities
    *   **Cross-Platform Compatibility:** Works on any server configuration
    *   **Method Migration Support:** Smooth transition between encryption methods without data loss
    *   **Encryption Method Tagging:** Each encrypted credential is tagged with its encryption method for proper decryption
    *   **AWS Signature Version 4:** All API requests are securely signed. Handled by `Auth\AwsV4Signer`.
    *   **HTTPS Enforcement:** All communication with the API is over HTTPS.
*   **Performance Optimizations:**
    *   **Connection Reuse:** GuzzleHttp client is configured for connection reuse (Keep-Alive).
    *   **Gzip Compression:** Supports Gzip for request and response bodies to reduce network latency (handled by Guzzle).
    *   **Batch Processing for `GetItems`:** Retrieve data for up to 10 ASINs in a single `GetItems` request.
    *   **Memory-Efficient Parsing:** Optimized object hydration from API responses.
*   **Detailed Error Handling:**
    *   A clear hierarchy of custom exceptions (e.g., `AuthenticationException`, `ThrottleException`, `RequestException`) for easier debugging and error management. (Located in `src/Exceptions/`)
*   **Lightweight & PSR-12 Compliant:**
    *   Minimal external dependencies (primarily GuzzleHttp).
    *   Adheres to PSR-12 coding standards for clean and maintainable code.
*   **PSR-3 Logging Support:**
    *   Allows integration with any PSR-3 compatible logger (like Monolog) for detailed logging of API interactions.

## 3. Requirements

*   **PHP:** 8.0 or higher
*   **Required Extensions:**
    *   `curl` (usually enabled by default)
    *   `json` (usually enabled by default)
    *   `openssl` (for encryption fallback, usually enabled by default)
    *   `sodium` (recommended for optimal security and performance)
*   **Encryption Method Priority:**
    1. **Sodium** (preferred) - If extension is available
    2. **OpenSSL** (fallback) - If Sodium is not available
    3. **Installation fails** - If neither is available
*   **Composer:** For managing dependencies.
*   **GuzzleHttp:** `^7.0` (automatically installed as a dependency).
*   **PSR-6 Cache Implementation (Optional):** If you plan to use an external cache like Redis or Memcached, you'll need a corresponding PSR-6 adapter (e.g., `symfony/cache`).

### Installation Options

**Standard Installation (Recommended):**
```sh
composer require rajpurohithitesh/amazon-paapi5-php-sdk

## 4. Installation

### Using Composer

The recommended way to install the SDK is via [Composer](https://getcomposer.org/). Run the following command in your project's root directory:

```sh
composer require rajpurohithitesh/amazon-paapi5-php-sdk
```

This will download the SDK and its dependencies into your project's `vendor` directory.

### External Cache Packages (Optional)

If you wish to use an external caching mechanism (like Redis or Memcached) instead of the built-in file cache, you'll need to install a PSR-6 compatible cache adapter. For example, to use Symfony Cache with Redis:

```sh
composer require symfony/cache symfony/redis-adapter
```

## 5. Getting Started: Basic Setup

### Prerequisites: Amazon PAAPI Credentials

Before you can use the SDK, you need to have valid Amazon Product Advertising API credentials:

1.  **Access Key ID**
2.  **Secret Access Key**
3.  **Partner Tag (Associate Tag)**

You can obtain these by registering for the Amazon Associates Program and then for the Product Advertising API. Ensure your account has been approved and has API access.

### Including the Autoloader

If you're using Composer, include the Composer-generated autoloader file at the beginning of your PHP script:

```php
<?php
require_once 'vendor/autoload.php';
```

### Initializing Configuration (`Config` class)

The SDK's behavior is controlled by the `AmazonPaapi5\Config` class. You need to create an instance of this class and provide your API credentials and other settings.

The `Config` constructor accepts an associative array:

```php
<?php
use AmazonPaapi5\Config;

// All available configuration options with explanations:
$sdkConfig = [
    // REQUIRED: Your Amazon PAAPI Access Key.
    'access_key' => '<YOUR_ACCESS_KEY>',

    // REQUIRED: Your Amazon PAAPI Secret Key.
    'secret_key' => '<YOUR_SECRET_KEY>',

    // REQUIRED: Your Amazon Associates Partner Tag for the target marketplace.
    'partner_tag' => '<YOUR_PARTNER_TAG>',

    // REQUIRED: The Amazon marketplace you are targeting (e.g., 'www.amazon.com', 'www.amazon.co.uk').
    // This determines the API endpoint and region.
    'marketplace' => 'www.amazon.com', // Example: US marketplace

    // REQUIRED (derived from marketplace, but good to understand): The AWS region for the PAAPI endpoint.
    // The SDK can often determine this from the marketplace, but you can be explicit.
    // See `AmazonPaapi5\Marketplace` for mappings or refer to Amazon's documentation.
    'region' => 'us-east-1', // Example: Region for www.amazon.com

    // OPTIONAL but STRONGLY RECOMMENDED for security:
    // A secret key (at least 16 characters long, ideally 32) used to encrypt your
    // Access Key and Secret Key if they are stored. If not provided or empty,
    // credentials will be used directly without an extra layer of SDK-managed encryption.
    // For production, generate a strong, unique key and store it securely (e.g., env variable).
    'encryption_key' => '<YOUR_STRONG_ENCRYPTION_KEY_32_CHARS>',

    // OPTIONAL: Directory for the built-in file cache.
    // Defaults to the system's temporary directory (e.g., /tmp/amazon-paapi5-cache).
    'cache_dir' => sys_get_temp_dir() . '/my-app-amazon-cache',

    // OPTIONAL: Cache Time-To-Live in seconds for API responses.
    // Defaults to 3600 seconds (1 hour).
    'cache_ttl' => 7200, // Example: 2 hours

    // OPTIONAL: Delay in seconds between consecutive API requests to manage throttling.
    // Amazon's default limit is 1 request per second (TPS) per account.
    // Adjust based on your account's TPS limit. Defaults to 1.0 second.
    'throttle_delay' => 1.5, // Example: 1.5 seconds

    // OPTIONAL: Maximum number of retries for failed requests (e.g., due to throttling).
    // Defaults to 3. (Note: Retry logic might need custom implementation around the client call)
    'max_retries' => 3,
];

try {
    $config = new Config($sdkConfig);
} catch (\AmazonPaapi5\Exceptions\ConfigException $e) {
    // Handle missing or invalid required configuration fields
    echo "Configuration Error: " . $e->getMessage();
    exit;
}

```

**Important Notes on `encryption_key`:**
*   If you provide an `encryption_key`, the SDK's `CredentialManager` will use it to encrypt your `access_key` and `secret_key` in memory. This adds an extra layer of protection.
*   The `encryption_key` itself must be kept secure. Do **not** hardcode it directly in version-controlled files for production environments. Use environment variables or a secure secrets management system.
*   If `encryption_key` is empty or not provided, your `access_key` and `secret_key` will be used as-is for signing requests (which is standard for AWS SDKs), but they won't have the additional SDK-level encryption wrapper.

### Encryption System Configuration

The SDK features an intelligent dual-encryption system:

```php
// Basic configuration - SDK automatically chooses best encryption method
$sdkConfig = [
    'access_key' => 'YOUR_AWS_ACCESS_KEY',
    'secret_key' => 'YOUR_AWS_SECRET_KEY',
    'partner_tag' => 'YOUR_PARTNER_TAG',
    'marketplace' => 'www.amazon.com',
    'encryption_key' => getenv('AMAZON_SDK_ENCRYPTION_KEY') ?: 'your-secure-32-char-key-here-123456',
];

$config = new Config($sdkConfig);
$credentialManager = new CredentialManager($config);

// Check active encryption method
echo "Using encryption: " . $credentialManager->getActiveEncryptionMethod() . "\n";

// Get detailed system information
$systemInfo = $credentialManager->getSystemInfo();
print_r($systemInfo);
```

### Initializing the Client (`Client` class)

Once you have your `Config` object, create an instance of the `AmazonPaapi5\Client`. This is the main class you'll use to send requests to the API.

```php
<?php
use AmazonPaapi5\Client;
use AmazonPaapi5\Config;
// Assuming $config is already initialized as shown above

// Basic client initialization
$client = new Client($config);

// Client with a custom PSR-6 cache implementation (e.g., Symfony Cache with Redis)
/*
use Symfony\Component\Cache\Adapter\RedisAdapter;
$redisConnection = RedisAdapter::createConnection('redis://localhost');
$customCache = new RedisAdapter($redisConnection, 'amazon_paapi_namespace', $config->getCacheTtl());
$clientWithCustomCache = new Client($config, $customCache);
*/

// Client with a custom PSR-3 logger (e.g., Monolog)
/*
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
$logger = new Logger('AmazonPAAPI');
$logger->pushHandler(new StreamHandler('path/to/your/amazon-paapi.log', Logger::DEBUG));
$clientWithLogger = new Client($config, null, $logger); // null for default cache
*/
```

Now your `$client` is ready to make API calls!

## 6. Core Concepts

Understanding these core concepts will help you use the SDK effectively:

### Operations
Operations represent the actions you can perform with the PAAPI5, such as searching for items or getting item details. Each operation has a dedicated class in the `AmazonPaapi5\Operations` namespace (e.g., `SearchItems`, `GetItems`).

### Request Objects
For each operation, there's a corresponding request object (e.g., `AmazonPaapi5\Models\Request\SearchItemsRequest`). You use these objects to specify the parameters for your API call. They provide type-hinted setter methods for a better development experience. All request objects ultimately implement or are used by `AmazonPaapi5\AbstractOperation`.

### Response Objects
When an API call is successful, the SDK parses the JSON response from Amazon into a corresponding response object (e.g., `AmazonPaapi5\Models\Response\SearchItemsResponse`). These objects provide convenient methods to access the data returned by the API.

## 7. Making API Calls: Supported Operations

All API calls are made using the `$client->sendAsync($operation)->wait()` pattern for synchronous execution, or by handling the promise directly for asynchronous execution.

The general workflow is:
1.  Create a Request object (e.g., `SearchItemsRequest`).
2.  Populate the Request object with necessary parameters (PartnerTag, Keywords, ASINs, Resources, etc.).
3.  Create an Operation object, passing the Request object to its constructor (e.g., `new SearchItems($searchItemsRequest)`).
4.  Send the operation using the `Client`:
    *   For synchronous execution (waits for the response): `$response = $client->sendAsync($operation)->wait();`
    *   For asynchronous execution: `$promise = $client->sendAsync($operation);` (then use `$promise->then(...)`)
5.  Process the Response object or handle any exceptions.

### 7.1. Searching for Items (`SearchItems`)

Use `SearchItems` to find products based on keywords, search index (category), and other criteria.

**Request Parameters (`SearchItemsRequest`):**
*   `setPartnerTag(string $partnerTag)`: Your Associate Partner Tag. **Required.**
*   `setKeywords(string $keywords)`: The search term(s).
*   `setSearchIndex(string $searchIndex)`: The category to search in (e.g., "All", "Electronics", "Books"). Defaults to "All".
*   `setItemCount(int $itemCount)`: Number of items to return per page (max 10 for most resources).
*   `setItemPage(int $itemPage)`: The page number of results to return.
*   `setResources(array $resources)`: Specifies which data groups to return (e.g., `['ItemInfo.Title', 'Offers.Listings.Price']`). See PAAPI documentation for all available resources. **Required.**
*   And many more filters like `Actor`, `Artist`, `Author`, `Brand`, `BrowseNodeId`, `Condition`, `CurrencyOfPreference`, `DeliveryFlags`, `LanguagesOfPreference`, `MaxPrice`, `MinPrice`, `MinReviewsRating`, `MinSavingPercent`, `OfferCount`, `Properties`, `SortBy`.

**Example:**

```php
<?php
use AmazonPaapi5\Operations\SearchItems;
use AmazonPaapi5\Models\Request\SearchItemsRequest;
// Assuming $client and $config are initialized

$searchRequest = (new SearchItemsRequest())
    ->setPartnerTag($config->getPartnerTag()) // Use partner tag from config
    ->setKeywords('PHP Programming Laptop')
    ->setSearchIndex('Electronics')
    ->setItemCount(3) // Request 3 items
    ->setResources([
        'ItemInfo.Title',
        'ItemInfo.Features',
        'Offers.Listings.Price',
        'Images.Primary.Medium',
        'BrowseNodeInfo.BrowseNodes'
    ]);

$operation = new SearchItems($searchRequest);

try {
    $response = $client->sendAsync($operation)->wait(); // Synchronous execution

    if ($response && $response->getSearchResult() && $response->getSearchResult()->getItems()) {
        echo "<h3>Search Results for 'PHP Programming Laptop':</h3>";
        foreach ($response->getSearchResult()->getItems() as $item) {
            echo "<h4>" . ($item->getItemInfo()->getTitle()->getDisplayValue() ?? 'N/A') . "</h4>";
            echo "ASIN: " . $item->getAsin() . "<br>";
            echo "Price: " . ($item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount() ?? 'N/A') . "<br>";
            echo "<img src='" . ($item->getImages()->getPrimary()->getMedium()->getURL() ?? '#') . "' alt='Product Image'><br>";

            if ($item->getItemInfo()->getFeatures()) {
                echo "Features: <ul>";
                foreach($item->getItemInfo()->getFeatures()->getDisplayValues() as $feature) {
                    echo "<li>" . $feature . "</li>";
                }
                echo "</ul>";
            }
            echo "<hr>";
        }
    } else {
        echo "No items found or error in response structure.<br>";
        if ($response && $response->getErrors()) {
            // Handle API errors
            foreach ($response->getErrors() as $error) {
                echo "API Error Code: " . $error->getCode() . " - Message: " . $error->getMessage() . "<br>";
            }
        }
    }

} catch (\AmazonPaapi5\Exceptions\ApiException $e) {
    echo "API Exception: " . $e->getMessage() . "<br>";
    echo "HTTP Status Code: " . $e->getCode() . "<br>";
    if ($e->getResponseErrors()) {
        echo "Specific PAAPI Errors: <pre>" . print_r($e->getResponseErrors(), true) . "</pre><br>";
    }
} catch (\Exception $e) {
    echo "Generic Exception: " . $e->getMessage() . "<br>";
}
```

### 7.2. Getting Item Details (`GetItems`)

Use `GetItems` to retrieve detailed information about one or more specific products using their ASINs (Amazon Standard Identification Number) or other Item IDs. You can request up to 10 items per call.

**Request Parameters (`GetItemsRequest`):**
*   `setPartnerTag(string $partnerTag)`: Your Associate Partner Tag. **Required.**
*   `setItemIds(array $itemIds)`: An array of Item IDs (usually ASINs). **Required.**
*   `setResources(array $resources)`: Specifies which data groups to return. **Required.**
*   `setCondition(string $condition)`: Item condition (e.g., "New", "Used").
*   `setCurrencyOfPreference(string $currency)`
*   `setLanguagesOfPreference(array $languages)`
*   `setOfferCount(int $offerCount)`

**Example:**

```php
<?php
use AmazonPaapi5\Operations\GetItems;
use AmazonPaapi5\Models\Request\GetItemsRequest;
// Assuming $client and $config are initialized

$itemIds = ['B08X4N3DW1', 'B09F3T2K7P']; // Example ASINs

$getItemsRequest = (new GetItemsRequest())
    ->setPartnerTag($config->getPartnerTag())
    ->setItemIds($itemIds)
    ->setResources([
        'ItemInfo.Title',
        'Offers.Listings.Price',
        'Images.Primary.Large',
        'CustomerReviews.Count',
        'CustomerReviews.StarRating'
    ]);

$operation = new GetItems($getItemsRequest);

try {
    $response = $client->sendAsync($operation)->wait();

    if ($response && $response->getItemsResult() && $response->getItemsResult()->getItems()) {
        echo "<h3>Product Details:</h3>";
        foreach ($response->getItemsResult()->getItems() as $item) {
            echo "<h4>" . ($item->getItemInfo()->getTitle()->getDisplayValue() ?? 'N/A') . "</h4>";
            echo "ASIN: " . $item->getAsin() . "<br>";
            echo "Price: " . ($item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount() ?? 'N/A') . "<br>";
            echo "Reviews: " . ($item->getCustomerReviews()->getCount() ?? '0') . " | Rating: " . ($item->getCustomerReviews()->getStarRating() ?? 'N/A') . " stars<br>";
            echo "<img src='" . ($item->getImages()->getPrimary()->getLarge()->getURL() ?? '#') . "' alt='Product Image' style='max-width: 200px;'><br>";
            echo "<hr>";
        }
    } else {
        echo "No items found or error in response structure for the given ASINs.<br>";
         if ($response && $response->getErrors()) {
            foreach ($response->getErrors() as $error) {
                echo "API Error Code: " . $error->getCode() . " - Message: " . $error->getMessage() . "<br>";
            }
        }
    }

} catch (\AmazonPaapi5\Exceptions\ApiException $e) {
    echo "API Exception: " . $e->getMessage() . "<br>";
} catch (\Exception $e) {
    echo "Generic Exception: " . $e->getMessage() . "<br>";
}
```

### 7.3. Getting Product Variations (`GetVariations`)

Use `GetVariations` to find different versions of a product, such as items that vary by size, color, pattern, etc.

**Request Parameters (`GetVariationsRequest`):**
*   `setPartnerTag(string $partnerTag)`: Your Associate Partner Tag. **Required.**
*   `setAsin(string $asin)`: The ASIN of the parent or a child variation item. **Required.**
*   `setResources(array $resources)`: Specifies which data groups to return for the variations. **Required.**
*   `setVariationCount(int $variationCount)`: Number of variations to return per page.
*   `setVariationPage(int $variationPage)`: Page number of variations.
*   `setCondition(string $condition)`
*   `setCurrencyOfPreference(string $currency)`
*   `setLanguagesOfPreference(array $languages)`
*   `setOfferCount(int $offerCount)`

**Example:**

```php
<?php
use AmazonPaapi5\Operations\GetVariations;
use AmazonPaapi5\Models\Request\GetVariationsRequest;
// Assuming $client and $config are initialized

$productAsin = 'B08X4N3DW1'; // ASIN of a product that has variations

$getVariationsRequest = (new GetVariationsRequest())
    ->setPartnerTag($config->getPartnerTag())
    ->setAsin($productAsin)
    ->setResources([
        'ItemInfo.Title',
        'VariationsResult.Items.ItemInfo.ContentInfo', // Example: To get variation attributes like color, size
        'VariationsResult.Items.Offers.Listings.Price',
        'VariationsResult.Items.Images.Primary.Medium',
        'VariationsResult.Items.VariationAttributes' // Key resource for variation details
    ]);

$operation = new GetVariations($getVariationsRequest);

try {
    $response = $client->sendAsync($operation)->wait();

    if ($response && $response->getVariationsResult() && $response->getVariationsResult()->getItems()) {
        echo "<h3>Product Variations for ASIN: $productAsin</h3>";
        foreach ($response->getVariationsResult()->getItems() as $variationItem) {
            echo "<h4>" . ($variationItem->getItemInfo()->getTitle()->getDisplayValue() ?? 'N/A') . "</h4>";
            echo "Variation ASIN: " . $variationItem->getAsin() . "<br>";
            echo "Price: " . ($variationItem->getOffers()->getListings()[0]->getPrice()->getDisplayAmount() ?? 'N/A') . "<br>";

            if ($variationItem->getVariationAttributes()) {
                echo "Attributes: <ul>";
                foreach ($variationItem->getVariationAttributes() as $attribute) {
                    echo "<li>" . $attribute->getName() . ": " . $attribute->getValue() . "</li>";
                }
                echo "</ul>";
            }
            echo "<img src='" . ($variationItem->getImages()->getPrimary()->getMedium()->getURL() ?? '#') . "' alt='Variation Image'><br>";
            echo "<hr>";
        }
    } else {
        echo "No variations found or error in response structure for ASIN: $productAsin.<br>";
        if ($response && $response->getErrors()) {
            foreach ($response->getErrors() as $error) {
                echo "API Error Code: " . $error->getCode() . " - Message: " . $error->getMessage() . "<br>";
            }
        }
    }

} catch (\AmazonPaapi5\Exceptions\ApiException $e) {
    echo "API Exception: " . $e->getMessage() . "<br>";
} catch (\Exception $e) {
    echo "Generic Exception: " . $e->getMessage() . "<br>";
}
```

### 7.4. Getting Browse Nodes (`GetBrowseNodes`)

Use `GetBrowseNodes` to retrieve information about Amazon's product categories (Browse Nodes). This is useful for building category-based navigation or understanding product hierarchy.

**Request Parameters (`GetBrowseNodesRequest`):**
*   `setPartnerTag(string $partnerTag)`: Your Associate Partner Tag. **Required.**
*   `setBrowseNodeIds(array $browseNodeIds)`: An array of Browse Node IDs. **Required.**
*   `setResources(array $resources)`: Specifies which data groups to return (e.g., `['BrowseNodes.Ancestor', 'BrowseNodes.Children']`). **Required.**
*   `setLanguagesOfPreference(array $languages)`

**Example:**

```php
<?php
use AmazonPaapi5\Operations\GetBrowseNodes;
use AmazonPaapi5\Models\Request\GetBrowseNodesRequest;
// Assuming $client and $config are initialized

$browseNodeIds = ['172282']; // Example Browse Node ID for "Electronics" in some marketplaces

$getBrowseNodesRequest = (new GetBrowseNodesRequest())
    ->setPartnerTag($config->getPartnerTag())
    ->setBrowseNodeIds($browseNodeIds)
    ->setResources([
        'BrowseNodes.Ancestor',
        'BrowseNodes.Children',
        'BrowseNodes.BrowseNodeInfo' // Provides DisplayName, ContextFreeName etc.
    ]);

$operation = new GetBrowseNodes($getBrowseNodesRequest);

try {
    $response = $client->sendAsync($operation)->wait();

    if ($response && $response->getBrowseNodesResult() && $response->getBrowseNodesResult()->getBrowseNodes()) {
        echo "<h3>Browse Node Details:</h3>";
        foreach ($response->getBrowseNodesResult()->getBrowseNodes() as $node) {
            echo "<h4>Node: " . ($node->getDisplayName() ?? 'N/A') . " (ID: " . $node->getId() . ")</h4>";
            echo "Is Root: " . ($node->getIsRoot() ? 'Yes' : 'No') . "<br>";
            echo "Context Free Name: " . ($node->getContextFreeName() ?? 'N/A') . "<br>";

            if ($node->getAncestor()) {
                echo "Ancestor: " . ($node->getAncestor()->getDisplayName() ?? 'N/A') . " (ID: " . $node->getAncestor()->getId() . ")<br>";
            }

            if ($node->getChildren()) {
                echo "Children: <ul>";
                foreach ($node->getChildren() as $childNode) {
                    echo "<li>" . ($childNode->getDisplayName() ?? 'N/A') . " (ID: " . $childNode->getId() . ")</li>";
                }
                echo "</ul>";
            }
            echo "<hr>";
        }
    } else {
        echo "No browse node information found or error in response structure.<br>";
        if ($response && $response->getErrors()) {
            foreach ($response->getErrors() as $error) {
                echo "API Error Code: " . $error->getCode() . " - Message: " . $error->getMessage() . "<br>";
            }
        }
    }

} catch (\AmazonPaapi5\Exceptions\ApiException $e) {
    echo "API Exception: " . $e->getMessage() . "<br>";
} catch (\Exception $e) {
    echo "Generic Exception: " . $e->getMessage() . "<br>";
}
```

## 8. Advanced Usage

### 8.1. Asynchronous Requests

The SDK uses Guzzle promises for all API calls, allowing for asynchronous (non-blocking) execution. This is beneficial when you need to make multiple API calls concurrently without waiting for each one to complete sequentially.

The `Client::sendAsync(AbstractOperation $operation)` method returns a `GuzzleHttp\Promise\PromiseInterface`.

```php
<?php
// Assuming $client, $searchOperation1, $getItemsOperation2 are initialized operations

$promise1 = $client->sendAsync($searchOperation1);
$promise2 = $client->sendAsync($getItemsOperation2);

$promise1->then(
    function ($response) { // onFulfilled
        echo "SearchItems call 1 completed successfully!\n";
        // Process $response for searchOperation1
        if ($response && $response->getSearchResult() && $response->getSearchResult()->getItems()) {
            foreach ($response->getSearchResult()->getItems() as $item) {
                // ... process item
            }
        }
    },
    function ($exception) { // onRejected
        echo "SearchItems call 1 failed: " . $exception->getMessage() . "\n";
        // Handle $exception for searchOperation1
    }
);

$promise2->then(
    function ($response) { // onFulfilled
        echo "GetItems call 2 completed successfully!\n";
        // Process $response for getItemsOperation2
        if ($response && $response->getItemsResult() && $response->getItemsResult()->getItems()) {
            foreach ($response->getItemsResult()->getItems() as $item) {
                // ... process item
            }
        }
    },
    function ($exception) { // onRejected
        echo "GetItems call 2 failed: " . $exception->getMessage() . "\n";
        // Handle $exception for getItemsOperation2
    }
);

// If you need to wait for all promises to complete:
use GuzzleHttp\Promise;
$allPromises = [$promise1, $promise2];
$results = Promise\Utils::settle($allPromises)->wait(); // `settle` waits for all, regardless of success/failure

foreach ($results as $i => $result) {
    if ($result['state'] === 'fulfilled') {
        echo "Promise " . ($i+1) . " was fulfilled with value: \n";
        // $result['value'] is the response object
    } else {
        echo "Promise " . ($i+1) . " was rejected with reason: " . $result['reason']->getMessage() . "\n";
        // $result['reason'] is the exception object
    }
}

// IMPORTANT: In a typical web server environment (like Apache with mod_php or PHP-FPM),
// the script usually runs to completion for each request. True asynchronous behavior
// often requires an event loop (e.g., ReactPHP, Swoole) or running tasks in background workers.
// However, using promises can still be beneficial for making multiple API calls within a single script execution.
```

### 8.2. Batch Operations (Conceptual)

While the PAAPI5 itself has specific batch capabilities (like `GetItems` accepting multiple ASINs), the SDK's `Client` class as shown in the old README (`$client->executeBatch($operations)`) is not a standard feature in the current code structure provided (`src/Client.php`).

To achieve a similar "batch" effect for different types of operations (e.g., one `SearchItems` and one `GetVariations`), you would use asynchronous requests as shown above and manage the promises.

For batching within a single operation type that supports it (like `GetItems`), simply provide multiple IDs to the request object:
```php
$getItemsRequest->setItemIds(['ASIN1', 'ASIN2', 'ASIN3']); // Up to 10 for GetItems
// Then send this single GetItems operation.
```

## 9. Configuration In-Depth

### 9.1. Marketplace Configuration

The SDK supports various Amazon marketplaces. You configure the target marketplace in the `Config` object. The `AmazonPaapi5\Marketplace` class internally maps marketplace hostnames to their respective API regions and hosts.

```php
$sdkConfig = [
    // ... other keys
    'marketplace' => 'www.amazon.co.uk', // Targets the UK marketplace
    // The SDK will use Marketplace::getRegion('www.amazon.co.uk') -> 'eu-west-1'
    // and Marketplace::getHost('www.amazon.co.uk') -> 'webservices.amazon.co.uk'
];
$config = new Config($sdkConfig);
```

**Supported Marketplaces (and their typical regions/hosts):**
(Refer to `AmazonPaapi5\Marketplace::getSupportedMarketplaces()` and Amazon's official documentation for the most current list and details)

*   `www.amazon.com` (US, us-east-1)
*   `www.amazon.co.uk` (UK, eu-west-1)
*   `www.amazon.de` (Germany, eu-west-1)
*   `www.amazon.fr` (France, eu-west-1)
*   `www.amazon.co.jp` (Japan, us-west-2)
*   `www.amazon.ca` (Canada, us-east-1)
*   `www.amazon.com.au` (Australia, us-west-2)
*   `www.amazon.in` (India, us-east-1)
*   `www.amazon.com.br` (Brazil, us-east-1)
*   `www.amazon.it` (Italy, eu-west-1)
*   `www.amazon.es` (Spain, eu-west-1)
*   `www.amazon.com.mx` (Mexico, us-east-1)
*   And more...

The `Config` object will use the `marketplace` setting to determine the correct `region` and API `host` for requests.

### 9.2. Throttling and Rate Limiting

Amazon PAAPI5 enforces request rate limits (typically 1 request per second per account, but this can vary). The SDK helps manage this with a configurable delay.

```php
$sdkConfig = [
    // ... other keys
    'throttle_delay' => 1.0, // Wait 1.0 second between requests.
                            // Increase if you encounter frequent throttling errors.
];
$config = new Config($sdkConfig);
```
The `AmazonPaapi5\Cache\ThrottleManager` (used internally by the `Client`) enforces this delay before making an API call if the time since the last call is less than the configured `throttle_delay`.

If you consistently hit throttling limits, you might need to:
*   Increase the `throttle_delay`.
*   Optimize your application to make fewer API calls (e.g., by improving caching).
*   Request a higher TPS limit from Amazon if your use case justifies it.

### 9.3. Caching Strategy

Caching API responses is crucial for performance and to stay within rate limits. The SDK supports PSR-6 caching.

**Built-in File Cache:**
The SDK provides `AmazonPaapi5\Cache\FileCache` (or `AdvancedCache` which might be a more refined version) by default.
```php
$sdkConfig = [
    // ... other keys
    'cache_dir' => __DIR__ . '/amazon_cache', // Custom cache directory
    'cache_ttl' => 3600, // Cache responses for 1 hour (3600 seconds)
];
$config = new Config($sdkConfig);
$client = new Client($config); // Uses built-in file cache by default
```

**External PSR-6 Cache (e.g., Redis with Symfony Cache):**
```php
use AmazonPaapi5\Client;
use AmazonPaapi5\Config;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Psr\Cache\CacheItemPoolInterface;

// Assuming $config is initialized

// Create a Redis connection (this might vary based on your Redis setup)
$redisConnection = RedisAdapter::createConnection(
    'redis://localhost:6379',
    [
        'timeout' => 3,
        'read_timeout' => 3,
        'retry_interval' => 0,
    ]
);

$psr6CachePool = new RedisAdapter(
    $redisConnection,
    'amazon_paapi_sdk_namespace', // A namespace for your cache keys
    $config->getCacheTtl() // Use TTL from your SDK config
);

$client = new Client($config, $psr6CachePool);
```
When a request is made, the `Client` first checks the cache. If a valid (non-expired) cached response exists for the same request parameters, it's returned immediately, avoiding an API call. Otherwise, the API is called, and the successful response is stored in the cache for future use.

### 9.4. Credential Management and Encryption

The `AmazonPaapi5\Security\CredentialManager` is responsible for handling your API keys. If you provide an `encryption_key` in the `Config`, the `CredentialManager` will encrypt your `access_key` and `secret_key` using AES-256-CBC.

```php
$sdkConfig = [
    'access_key' => 'YOUR_AWS_ACCESS_KEY',
    'secret_key' => 'YOUR_AWS_SECRET_KEY',
    'partner_tag' => 'YOUR_PARTNER_TAG',
    'marketplace' => 'www.amazon.com',
    'region' => 'us-east-1', // Or let the SDK derive it
    // Provide a strong, unique key (e.g., from an environment variable)
    'encryption_key' => getenv('MY_APP_AMAZON_SDK_ENCRYPTION_KEY') ?: 'a_default_fallback_key_min_16_chars',
];
$config = new Config($sdkConfig);
// The CredentialManager (used internally by Client) will now use the encryption_key.
```
**Security Note:** The `encryption_key` itself becomes a critical secret. Store it securely (e.g., environment variables, HashiCorp Vault, AWS Secrets Manager) and **never commit it to your repository.**

If `encryption_key` is empty, credentials are used directly for signing, which is still secure due to AWS Signature V4, but lacks the SDK's additional in-memory encryption layer.

### 9.5. Logging (PSR-3)

The SDK allows you to inject a PSR-3 compatible logger (like Monolog) into the `Client` to record its activities. This is very helpful for debugging and monitoring.

```php
<?php
use AmazonPaapi5\Client;
use AmazonPaapi5\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

// Assuming $config is initialized

// Create a logger instance
$logger = new Logger('AmazonPAAPI_SDK');
$logFile = __DIR__ . '/logs/amazon_paapi.log'; // Ensure this directory is writable
$logger->pushHandler(new StreamHandler($logFile, Logger::INFO)); // Log INFO level and above

// You can also log DEBUG messages for more verbosity
// $logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));

$client = new Client(
    $config,
    null,     // Pass null to use the default cache, or your PSR-6 cache instance
    $logger   // Pass your PSR-3 logger instance
);

// Now the client will log information about requests, cache hits/misses, errors, etc.
// Example log output might include:
// [timestamp] AmazonPAAPI_SDK.DEBUG: Cache miss {"operation":"AmazonPaapi5\\Operations\\SearchItems"} []
// [timestamp] AmazonPAAPI_SDK.INFO: Sending request {"operation":"AmazonPaapi5\\Operations\\SearchItems","path":"/paapi5/searchitems"} []
// [timestamp] AmazonPAAPI_SDK.DEBUG: Cache saved {"operation":"AmazonPaapi5\\Operations\\SearchItems"} []

try {
    // Make an API call
    $searchRequest = new \AmazonPaapi5\Models\Request\SearchItemsRequest();
    // ... configure request ...
    $searchRequest->setPartnerTag($config->getPartnerTag())->setKeywords('test')->setResources(['ItemInfo.Title']);
    $operation = new \AmazonPaapi5\Operations\SearchItems($searchRequest);
    $response = $client->sendAsync($operation)->wait();
    // ...
} catch (\AmazonPaapi5\Exceptions\ApiException $e) {
    $logger->error('PAAPI ApiException', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'response_errors' => $e->getResponseErrors()
    ]);
}
```

## 10. Error Handling and Exceptions

The SDK uses a hierarchy of custom exceptions, all extending `\Exception` or a more specific base like `\RuntimeException`. This allows for granular error handling.

### Custom Exception Hierarchy

Located in `src/Exceptions/`:

*   `AmazonPaapi5\Exceptions\ApiException`: Base class for errors returned by the Amazon PAAPI itself (e.g., invalid parameters, authentication failure reported by API, throttling).
    *   `getMessage()`: General error message.
    *   `getCode()`: Typically the HTTP status code of the error response.
    *   `getResponseErrors()`: An array of specific error objects returned by the PAAPI within the JSON response body. Each error object usually has `Code` and `Message` properties.
*   `AmazonPaapi5\Exceptions\AuthenticationException`: For issues related to client-side authentication setup or credential validation before an API call is made (e.g., invalid key format detected by `CredentialManager`).
*   `AmazonPaapi5\Exceptions\ConfigException`: For errors in the SDK configuration (e.g., missing required config fields).
*   `AmazonPaapi5\Exceptions\RequestException`: For client-side issues with the request construction before it's sent (though many parameter validations are done by the API and result in `ApiException`). The message often includes a suggestion.
*   `AmazonPaapi5\Exceptions\SecurityException`: For errors related to encryption/decryption within the `CredentialManager` (e.g., OpenSSL failure).
*   `AmazonPaapi5\Exceptions\ThrottleException`: Specifically for throttling errors (HTTP 429). This might be a subclass of `ApiException` or a distinct exception if the SDK handles retries for throttling internally (check its specific implementation). The old README mentioned it, so it's good to be aware of.

Additionally, Guzzle exceptions (like `GuzzleHttp\Exception\ConnectException` for network issues) might be thrown if the error occurs before a response is received from Amazon.

### Handling Exceptions

Use `try-catch` blocks to handle these exceptions gracefully.

```php
<?php
// Assuming $client, $operation are initialized

try {
    $response = $client->sendAsync($operation)->wait();
    // Process successful response
    // ...

} catch (\AmazonPaapi5\Exceptions\ThrottleException $e) {
    // Specific handling for throttling
    echo "Throttling Error: " . $e->getMessage() . "\n";
    echo "Suggestion: Increase throttle_delay in config or reduce request frequency.\n";
    // Optionally log $e->getResponseErrors() if available
    // You might implement a retry mechanism here with a longer delay.

} catch (\AmazonPaapi5\Exceptions\AuthenticationException $e) {
    // Errors related to AWS credentials as configured or validated by the SDK
    echo "Authentication Setup Error: " . $e->getMessage() . "\n";
    echo "Suggestion: Verify your Access Key, Secret Key, and Marketplace/Region settings in the SDK configuration.\n";

} catch (\AmazonPaapi5\Exceptions\ApiException $e) {
    // General errors from the Amazon PAAPI
    echo "Amazon PAAPI Error: " . $e->getMessage() . "\n";
    echo "HTTP Status Code: " . $e->getCode() . "\n";
    if ($e->getResponseErrors()) {
        echo "Specific API Errors:\n";
        foreach ($e->getResponseErrors() as $apiError) {
            echo " - Code: " . $apiError->getCode() . ", Message: " . $apiError->getMessage() . "\n";
        }
    }
    // Example: Check for common error codes
    // if ($e->getResponseErrors() && $e->getResponseErrors()[0]->getCode() === 'InvalidParameterValue') { ... }

} catch (\AmazonPaapi5\Exceptions\ConfigException $e) {
    echo "SDK Configuration Error: " . $e->getMessage() . "\n";
    // Fix your Config object initialization.

} catch (\AmazonPaapi5\Exceptions\RequestException $e) {
    echo "SDK Request Error: " . $e->getMessage() . "\n";
    // Check how you built the request object.

} catch (\GuzzleHttp\Exception\ConnectException $e) {
    // Network connectivity issues
    echo "Network Error: Could not connect to Amazon API. " . $e->getMessage() . "\n";

} catch (\Exception $e) {
    // Catch-all for any other unexpected exceptions
    echo "An unexpected error occurred: " . $e->getMessage() . "\n";
    // Log the full exception details for debugging
    // error_log($e->getTraceAsString());
}
```

## 11. Security Best Practices

*   **Protect Your Credentials:**
    *   Never hardcode your AWS Access Key, Secret Key, or SDK `encryption_key` directly into your source code, especially if it's version-controlled.
    *   Use environment variables, `.env` files (with a library like `vlucas/phpdotenv`), or a dedicated secrets management service (like AWS Secrets Manager, HashiCorp Vault).
*   **Use IAM Roles (If Running on AWS):** If your application runs on AWS infrastructure (e.g., EC2, Lambda), prefer using IAM roles to grant temporary credentials to your application instead of long-lived access keys. Guzzle and the AWS SDK for PHP (if used as a dependency, though this SDK seems to implement signing directly) can often pick these up automatically.
*   **Principle of Least Privilege:** Ensure the IAM user associated with your Access Key has only the necessary permissions for PAAPI5 (`ProductAdvertisingAPI`). Do not use root account credentials.
*   **Secure `encryption_key`:** If you use the SDK's credential encryption feature, the `encryption_key` is vital. Keep it as secure as your primary AWS credentials.
*   **HTTPS:** The SDK enforces HTTPS for all API calls, ensuring data is encrypted in transit.
*   **Regularly Rotate Keys:** Periodically rotate your AWS Access Keys as a security best practice.
*   **Validate Inputs:** Sanitize and validate any user-provided input that might be used in API requests to prevent injection-style attacks (though PAAPI is generally less susceptible to typical web vulnerabilities like XSS/SQLi in its direct use).
*   **Keep SDK Updated:** Regularly update the SDK to the latest version to benefit from security patches and improvements.
*   **Encryption Method Best Practices:**
    *   **Prefer Sodium:** Always use Sodium for new deployments (better security + performance)
    *   **Monitor Method:** Use logging to track which encryption method is active
    *   **Plan Migrations:** Test encryption method switches in staging before production
    *   **Fallback Strategy:** Ensure OpenSSL is properly configured as backup
    *   **Method Testing:** Regularly test encryption functionality with `testEncryption()`
    *   **Key Rotation:** Implement periodic encryption key rotation for enhanced security

## 12. Performance Considerations

*   **Caching is Key:** Aggressively cache API responses. Use appropriate TTLs based on how frequently the data changes. This reduces API calls, improves response times, and helps stay within rate limits.
*   **Request Only Necessary Resources:** Use the `setResources()` method in your request objects to fetch only the data fields you actually need. Requesting fewer resources can lead to smaller response payloads and faster processing.
*   **Asynchronous Operations:** For pages or processes that require multiple independent API calls, use asynchronous requests (`sendAsync()` and promise handling) to perform them concurrently.
*   **Batch `GetItems`:** When fetching details for multiple products, use a single `GetItems` call with an array of up to 10 ASINs instead of making individual calls for each.
*   **Efficient Data Handling:** Once you get the response, process it efficiently. Avoid unnecessary loops or data transformations if possible.
*   **Monitor Performance:** Use logging and application performance monitoring (APM) tools to identify bottlenecks related to API calls.
*   **Connection Reuse & Gzip:** The SDK (via Guzzle) handles these automatically, contributing to better performance.

## 13. Contributing

We welcome contributions to enhance this SDK! If you'd like to contribute, please:

1.  **Fork the repository.**
2.  **Create a new branch** for your feature or bug fix (e.g., `feature/new-operation` or `fix/caching-issue`).
3.  **Make your changes.** Ensure your code adheres to PSR-12 coding standards.
4.  **Add unit tests** for any new functionality or bug fixes.
5.  **Ensure all tests pass.**
6.  **Submit a pull request** to the `main` (or `develop`) branch of the original repository.
7.  Clearly describe your changes and the problem they solve in the pull request description.

Please also check if there's a `CONTRIBUTING.md` file in the repository for more specific guidelines.

## 14. Official Amazon PAAPI Documentation

For complete and authoritative details on the Amazon Product Advertising API 5.0, including available resources, parameters, error codes, and policies, please refer to the official documentation:

*   [Amazon Product Advertising API 5.0 Documentation Home](https://webservices.amazon.com/paapi5/documentation/)
*   Operations:
    *   [SearchItems](https://webservices.amazon.com/paapi5/documentation/search-items.html)
    *   [GetItems](https://webservices.amazon.com/paapi5/documentation/get-items.html)
    *   [GetVariations](https://webservices.amazon.com/paapi5/documentation/get-variations.html)
    *   [GetBrowseNodes](https://webservices.amazon.com/paapi5/documentation/get-browse-nodes.html)
*   [Common Request Parameters](https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html)
*   [Resources Specification](https://webservices.amazon.com/paapi5/documentation/resources-specification.html)
*   [Error Messages](https://webservices.amazon.com/paapi5/documentation/error-messages.html)