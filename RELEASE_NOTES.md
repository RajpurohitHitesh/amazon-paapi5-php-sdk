# Release Notes - Amazon PA-API 5.0 PHP SDK

## Version 1.1.1 - December 12, 2025

### 🎉 Feature Release: OffersV2 Support & Enterprise Features

This major release brings full support for Amazon's new **OffersV2 API** along with enterprise-grade features including dual encryption, advanced caching, connection pooling, and comprehensive monitoring capabilities.

---

## 🚀 Major New Features

### 1. **OffersV2 API - Complete Implementation**

Amazon's next-generation Offers API with enhanced reliability and new features:

#### **New Model Classes (11 Total)**
- ✅ `OffersV2` - Main container with helper methods
- ✅ `OfferListing` - Individual offer with 9 properties
- ✅ `Money` - Currency representation (amount, currency, displayAmount)
- ✅ `Availability` - Stock status with min/max quantities
- ✅ `Condition` - Product condition (New, Used, Refurbished, etc.)
- ✅ `DealDetails` - Lightning Deals & Prime Exclusive Deals
- ✅ `LoyaltyPoints` - Japan marketplace loyalty points
- ✅ `MerchantInfo` - Seller ID and merchant details
- ✅ `Price` - Complete pricing with unit prices
- ✅ `SavingBasis` - Original price for savings calculation
- ✅ `Savings` - Discount information (amount & percentage)

#### **Key OffersV2 Features**
- 🎯 **BuyBox Winner Detection** - `getBuyBoxWinner()` helper method
- ⚡ **Lightning Deals** - Full deal information with start/end times
- 👑 **Prime Exclusive Deals** - Support for Prime-only offers
- 💰 **Enhanced Pricing** - Original price, savings, and unit pricing
- 🏪 **Merchant Information** - Track seller IDs
- 📊 **Deal Progress** - Percentage claimed for time-limited deals
- 🕐 **Early Access** - Prime early access duration tracking

#### **Resources Available**
```php
// All 33+ OffersV2.Listings.* resources supported:
'OffersV2.Listings.Availability.Type',
'OffersV2.Listings.Availability.Message',
'OffersV2.Listings.Condition.Value',
'OffersV2.Listings.DealDetails.AccessType',  // Prime Exclusive
'OffersV2.Listings.DealDetails.Badge',       // Lightning Deal
'OffersV2.Listings.Price.Amount',
'OffersV2.Listings.Price.Savings.Amount',
'OffersV2.Listings.MerchantInfo.Id',
// ... and 25+ more
```

#### **Migration Support**
- 📖 **Complete Migration Guide**: `MIGRATION_OFFERS_V1_TO_V2.md`
- 📚 **Full Documentation**: `OFFERSV2_README.md`
- 💡 **Working Examples**: `examples/offersv2_example.php`
- ⚠️ **Deprecation Warnings**: Added to README.md

---

### 2. **Dual Encryption System with Intelligent Fallback**

Enterprise-grade credential protection with automatic method selection:

#### **Primary: Sodium Encryption (ChaCha20-Poly1305)**
- 🔐 **Superior Security**: Authenticated encryption with libsodium
- ⚡ **High Performance**: Optimized for modern PHP 8.0+
- ✅ **Automatic Detection**: Used when ext-sodium available

#### **Fallback: OpenSSL Encryption (AES-256-GCM)**
- 🛡️ **Reliable Backup**: AES-256-GCM when Sodium unavailable
- 🌍 **Universal Support**: Works on all standard PHP installations
- 🔄 **Seamless Switch**: Automatic fallback without code changes

#### **Smart Features**
```php
// Automatic method detection
$credentialManager = new CredentialManager($config);
echo $credentialManager->getActiveEncryptionMethod(); // 'sodium' or 'openssl'

// Get system information
$systemInfo = $credentialManager->getSystemInfo();
// Returns: encryption method, PHP version, available extensions

// Test encryption functionality
$testResult = $credentialManager->testEncryption();
// Validates encryption/decryption cycle
```

#### **Key Benefits**
- 🏷️ **Method Tagging**: Each encrypted value tagged with its method
- 🔄 **Migration Support**: Smooth transition between encryption methods
- 🚨 **Error Handling**: Graceful degradation with detailed logging
- 📊 **Monitoring**: Track active encryption method in logs

---

### 3. **Advanced Caching System**

Enhanced PSR-6 compliant caching with `AdvancedCache`:

#### **Features**
- 📂 **File-Based Cache**: High-performance local cache
- ⏰ **TTL Support**: Configurable time-to-live (default: 1 hour)
- 🧹 **Auto Cleanup**: Expired cache item removal
- 🔍 **Cache Hit/Miss Tracking**: Detailed metrics
- 💾 **Efficient Storage**: Optimized serialization

#### **Usage**
```php
$config = new Config([
    'cache_dir' => '/path/to/cache',
    'cache_ttl' => 7200, // 2 hours
    // ...
]);

// Or use external cache (Redis, Memcached)
$redisCache = new RedisAdapter($connection);
$client = new Client($config, $redisCache);
```

---

### 4. **Connection Pool Management**

Efficient HTTP connection handling with `ConnectionPool`:

#### **Features**
- ♻️ **Connection Reuse**: Keep-alive for better performance
- 🔒 **TLS Configuration**: Customizable TLS version (default: TLS 1.2)
- ✅ **SSL Verification**: Configurable SSL certificate validation
- ⏱️ **Timeout Control**: Separate request and connection timeouts
- 🌐 **Gzip Compression**: Automatic response compression

#### **Configuration**
```php
$config = new Config([
    'tls_version' => 'TLS1.3',
    'verify_ssl' => true,
    'request_timeout' => 30,
    'connection_timeout' => 5,
    // ...
]);
```

---

### 5. **Batch Processing System**

Efficient handling of multiple operations with `BatchProcessor`:

#### **Features**
- 📦 **Batch Operations**: Process multiple requests together
- ⚡ **Parallel Execution**: Concurrent API calls via Guzzle promises
- 🔄 **Automatic Retry**: Built-in retry mechanism
- 📊 **Success Tracking**: Detailed batch results
- 🚦 **Throttle Integration**: Respects rate limits

#### **Usage**
```php
// Queue multiple operations
$client->queueRequest($searchOperation, $priority = 1);
$client->queueRequest($getItemsOperation, $priority = 2);

// Process entire queue
$results = $client->processQueue();
// Returns: ['SearchItems' => [...], 'GetItems' => [...]]
```

---

### 6. **Request Queue Optimizer**

Intelligent request management with `RequestQueueOptimizer`:

#### **Features**
- 🎯 **Priority-Based Queue**: High-priority requests first
- 🔀 **Operation Grouping**: Similar operations batched together
- 📈 **Performance Optimization**: Reduces API calls
- 🧠 **Smart Scheduling**: Optimal request ordering
- 📊 **Queue Analytics**: Track queue performance

---

### 7. **Comprehensive Monitoring System**

Production-ready monitoring with `Monitor`:

#### **Tracking Metrics**
- ⏱️ **Request Duration**: Track API call performance
- ✅ **Success/Failure Rates**: Monitor API reliability
- 🎯 **Cache Hit Ratio**: Measure caching effectiveness
- 🚨 **Error Tracking**: Categorized error reporting
- 📊 **Request Statistics**: Detailed performance data

#### **Integration**
```php
// Automatic monitoring with PSR-3 logger
$logger = new Logger('AmazonAPI');
$client = new Client($config, $cache, $logger);

// Monitor tracks:
// - Request start/end times
// - Cache hits/misses
// - API errors with context
// - Network issues
```

---

### 8. **Enhanced Throttle Management**

Sophisticated rate limiting with `ThrottleManager`:

#### **Features**
- ⏱️ **Configurable Delay**: Default 1.0 second between requests
- 🎯 **Per-Marketplace Throttling**: Different limits per region
- 🔄 **Automatic Queuing**: Requests queued when limit reached
- 📊 **Throttle Metrics**: Track throttle events
- 🚦 **Graceful Degradation**: Smooth handling of rate limits

```php
$config = new Config([
    'throttle_delay' => 1.5, // 1.5 seconds between requests
    'max_retries' => 3,
    // ...
]);
```

---

## 🔧 Core Improvements

### **Client Class Enhancements**
- ✅ Asynchronous request support via `sendAsync()`
- ✅ Synchronous execution with `->wait()`
- ✅ Promise-based architecture for concurrent calls
- ✅ Automatic cache integration
- ✅ Built-in throttling
- ✅ Comprehensive error handling

### **Configuration System**
- ✅ Strict type checking for all config values
- ✅ Sensible defaults for all optional settings
- ✅ Required field validation with clear error messages
- ✅ Support for 18+ Amazon marketplaces
- ✅ Automatic region detection from marketplace

### **Request/Response Models**
- ✅ Fully typed PHP 8.0+ models
- ✅ Null-safe property access
- ✅ IDE-friendly autocomplete
- ✅ Consistent API across all models
- ✅ Helper methods for common tasks

---

## 🌍 Marketplace Support

### **Supported Regions (18 Total)**
```php
// North America
'www.amazon.com'    // United States (us-east-1)
'www.amazon.ca'     // Canada (us-east-1)
'www.amazon.com.mx' // Mexico (us-east-1)
'www.amazon.com.br' // Brazil (us-east-1)

// Europe
'www.amazon.co.uk'  // United Kingdom (eu-west-1)
'www.amazon.de'     // Germany (eu-west-1)
'www.amazon.fr'     // France (eu-west-1)
'www.amazon.it'     // Italy (eu-west-1)
'www.amazon.es'     // Spain (eu-west-1)
'www.amazon.nl'     // Netherlands (eu-west-1)
'www.amazon.se'     // Sweden (eu-west-1)
'www.amazon.com.tr' // Turkey (eu-west-1)

// Middle East & Asia
'www.amazon.ae'     // UAE (eu-west-1)
'www.amazon.sa'     // Saudi Arabia (eu-west-1)
'www.amazon.in'     // India (us-east-1)
'www.amazon.co.jp'  // Japan (us-west-2)
'www.amazon.sg'     // Singapore (us-west-2)
'www.amazon.com.au' // Australia (us-west-2)
```

---

## 🎯 All Supported Operations

### **1. SearchItems**
Search Amazon's product catalog with advanced filters:
- Keywords, search index, brand, price range
- Sort options, condition filters
- Browse node targeting
- Pagination support (up to 10 pages)

### **2. GetItems**
Retrieve detailed product information:
- Up to 10 ASINs per request
- Complete product details
- **OffersV2** pricing & availability
- Customer reviews, images

### **3. GetVariations**
Get product variations (size, color, etc.):
- Parent/child variation mapping
- Variation-specific pricing
- Variation attributes
- Images for each variation

### **4. GetBrowseNodes**
Access Amazon's category structure:
- Category hierarchy
- Parent/child relationships
- Category names and IDs
- Navigation breadcrumbs

---

## 📚 Documentation Updates

### **New Documentation Files**
1. ✅ `OFFERSV2_README.md` - Complete OffersV2 guide (500+ lines)
2. ✅ `MIGRATION_OFFERS_V1_TO_V2.md` - Migration guide (600+ lines)
3. ✅ `examples/offersv2_example.php` - Working code examples

### **Updated Documentation**
1. ✅ `README.md` - Updated with OffersV2 warnings and examples
2. ✅ All response object methods corrected
3. ✅ Encryption system documentation
4. ✅ Configuration examples updated
5. ✅ Exception handling examples

---

## 🔐 Security Enhancements

### **Credential Protection**
- ✅ Dual encryption (Sodium + OpenSSL)
- ✅ Automatic encryption method selection
- ✅ Encrypted credential storage option
- ✅ Secure key rotation support

### **Network Security**
- ✅ TLS 1.2+ enforcement
- ✅ SSL certificate verification
- ✅ HTTPS-only communication
- ✅ AWS Signature V4 signing

### **Best Practices**
- ✅ Environment variable support
- ✅ No credentials in version control
- ✅ Secure encryption key management
- ✅ Regular security audits

---

## 🐛 Bug Fixes

### **Response Handling**
- ✅ Fixed `SearchItemsResponse` API (direct `getItems()`)
- ✅ Fixed `GetItemsResponse` API (direct `getItems()`)
- ✅ Fixed `GetVariationsResponse` API (direct `getItems()`)
- ✅ Fixed `GetBrowseNodesResponse` API (direct `getBrowseNodes()`)
- ✅ Removed non-existent `getErrors()` methods (use exceptions)

### **Error Handling**
- ✅ Improved null response handling (404 errors)
- ✅ Better error messages with suggestions
- ✅ Proper exception hierarchy
- ✅ Detailed error context in logs

### **Configuration**
- ✅ Fixed marketplace region auto-detection
- ✅ Proper default value handling
- ✅ Required field validation improvements

---

## 📦 Dependencies

### **Required**
- PHP: `^8.0`
- ext-sodium: `*` (primary encryption)
- ext-openssl: `*` (fallback encryption)
- ext-json: `*` (JSON handling)
- ext-curl: `*` (HTTP requests)
- guzzlehttp/guzzle: `^7.0` (HTTP client)
- psr/cache: `^1.0 || ^2.0 || ^3.0` (PSR-6)
- psr/log: `^1.1 || ^2.0 || ^3.0` (PSR-3)

### **Development**
- phpunit/phpunit: `^9.5`
- squizlabs/php_codesniffer: `^3.6`
- phpstan/phpstan: `^1.0`

### **Suggested**
- symfony/cache: For Redis/Memcached support

---

## ⚡ Performance Improvements

### **Optimization Features**
1. ✅ **Connection Pooling** - Reuse HTTP connections (Keep-Alive)
2. ✅ **Gzip Compression** - Reduce response size by ~70%
3. ✅ **Smart Caching** - Aggressive response caching
4. ✅ **Batch Processing** - Up to 10 items per GetItems call
5. ✅ **Async Operations** - Concurrent API calls
6. ✅ **Memory Optimization** - Efficient object hydration

### **Benchmarks**
- 📊 **Cache Hit**: <1ms response time
- 📊 **Cache Miss**: 200-500ms (network dependent)
- 📊 **Batch GetItems**: 70% faster than individual calls
- 📊 **Gzip Compression**: 65-75% size reduction

---

## ℹ️ Important Changes

### **Offers V1 Deprecation Notice**
- ⚠️ Offers V1 still works but marked as deprecated
- ✅ Use OffersV2 for all new development
- 📖 Migration guide provided for existing code
- 🔮 Amazon will eventually remove Offers V1

### **Response Object Improvements**
- ✨ Simplified response methods:
  - `$response->getItems()` instead of `->getSearchResult()->getItems()`
  - `$response->getBrowseNodes()` instead of `->getBrowseNodesResult()->getBrowseNodes()`
- ✨ Error handling via exceptions only (no `getErrors()` method)

### **Configuration Enhancements**
- ✅ ext-sodium now required for better security
- ✅ Stricter type checking in Config class
- ✅ Required fields strictly validated

---

## 📋 Upgrade Guide

### **To Version 1.1.1**

#### **Step 1: Update Dependencies**
```bash
composer require rajpurohithitesh/amazon-paapi5-php-sdk:^1.1
composer update
```

#### **Step 2: Check PHP Extensions**
```bash
php -m | grep -E '(sodium|openssl)'
```

#### **Step 3: Update Response Handling**
```php
// OLD (previous version)
$items = $response->getSearchResult()->getItems();

// NEW (1.1.1)
$items = $response->getItems();
```

#### **Step 4: Migrate to OffersV2**
See `MIGRATION_OFFERS_V1_TO_V2.md` for detailed guide.

#### **Step 5: Update Error Handling**
```php
// Remove getErrors() calls - use exceptions instead
try {
    $response = $client->sendAsync($operation)->wait();
} catch (\AmazonPaapi5\Exceptions\ApiException $e) {
    // Handle errors
}
```

---

## 🎓 Code Examples

### **Basic OffersV2 Usage**
```php
use AmazonPaapi5\Client;
use AmazonPaapi5\Config;
use AmazonPaapi5\Operations\GetItems;
use AmazonPaapi5\Models\Request\GetItemsRequest;

$config = new Config([
    'access_key' => 'YOUR_KEY',
    'secret_key' => 'YOUR_SECRET',
    'partner_tag' => 'YOUR_TAG',
    'marketplace' => 'www.amazon.com',
    'encryption_key' => getenv('ENCRYPTION_KEY'),
]);

$client = new Client($config);

$request = (new GetItemsRequest())
    ->setPartnerTag($config->getPartnerTag())
    ->setItemIds(['B08X4N3DW1'])
    ->setResources([
        'ItemInfo.Title',
        'OffersV2.Listings.Price.Amount',
        'OffersV2.Listings.DealDetails.Badge',
        'OffersV2.Listings.Availability.Type',
    ]);

$operation = new GetItems($request);
$response = $client->sendAsync($operation)->wait();

foreach ($response->getItems() as $item) {
    $offersV2 = $item->getOffersV2();
    
    // Get BuyBox winner
    $buyBox = $offersV2->getBuyBoxWinner();
    echo "Price: " . $buyBox->getPrice()->getMoney()->getDisplayAmount() . "\n";
    
    // Check for deals
    $deals = $offersV2->getDealListings();
    foreach ($deals as $deal) {
        $dealDetails = $deal->getDealDetails();
        echo "Deal: " . $dealDetails->getBadge() . "\n";
    }
}
```

### **Async Batch Processing**
```php
// Queue multiple operations
$client->queueRequest($searchOp1, 1);
$client->queueRequest($searchOp2, 1);
$client->queueRequest($getItemsOp, 2);

// Process all at once
$results = $client->processQueue();

foreach ($results as $type => $responses) {
    echo "Operation: $type\n";
    // Process responses
}
```

---

## 🤝 Contributing

We welcome contributions! Please:
1. Fork the repository
2. Create a feature branch
3. Follow PSR-12 coding standards
4. Add tests for new features
5. Submit a pull request

---

## 📝 License

Apache License 2.0 - See [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- Amazon Product Advertising API Team
- PHP Community
- All Contributors

---

## 📞 Support

- 📖 Documentation: [README.md](README.md)
- 🐛 Issues: [GitHub Issues](https://github.com/RajpurohitHitesh/amazon-paapi5-php-sdk/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/RajpurohitHitesh/amazon-paapi5-php-sdk/discussions)

---

## 🗺️ Roadmap

### **Coming Soon**
- 🔜 Redis cache adapter documentation
- 🔜 GraphQL-style query builder
- 🔜 Advanced filtering helpers
- 🔜 Response validation utilities
- 🔜 Performance profiling tools

---

**🎉 Thank you for using Amazon PA-API 5.0 PHP SDK!**

**⭐ If you find this SDK useful, please star the repository on GitHub!**
