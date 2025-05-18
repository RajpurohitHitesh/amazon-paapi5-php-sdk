<?php

declare(strict_types=1);

namespace AmazonPaapi5;

class Marketplace
{
    private static array $marketplaces = [
        'www.amazon.com' => ['region' => 'us-east-1', 'host' => 'webservices.amazon.com'],
        'www.amazon.co.uk' => ['region' => 'eu-west-1', 'host' => 'webservices.amazon.co.uk'],
        'www.amazon.de' => ['region' => 'eu-west-1', 'host' => 'webservices.amazon.de'],
        'www.amazon.fr' => ['region' => 'eu-west-1', 'host' => 'webservices.amazon.fr'],
        'www.amazon.co.jp' => ['region' => 'us-west-2', 'host' => 'webservices.amazon.co.jp'],
        'www.amazon.ca' => ['region' => 'us-east-1', 'host' => 'webservices.amazon.ca'],
        'www.amazon.com.au' => ['region' => 'us-west-2', 'host' => 'webservices.amazon.com.au'],
        'www.amazon.in' => ['region' => 'us-east-1', 'host' => 'webservices.amazon.in'],
        'www.amazon.com.br' => ['region' => 'us-east-1', 'host' => 'webservices.amazon.com.br'],
        'www.amazon.it' => ['region' => 'eu-west-1', 'host' => 'webservices.amazon.it'],
        'www.amazon.es' => ['region' => 'eu-west-1', 'host' => 'webservices.amazon.es'],
        'www.amazon.com.mx' => ['region' => 'us-east-1', 'host' => 'webservices.amazon.com.mx'],
        'www.amazon.nl' => ['region' => 'eu-west-1', 'host' => 'webservices.amazon.nl'],
        'www.amazon.sg' => ['region' => 'us-west-2', 'host' => 'webservices.amazon.sg'],
        'www.amazon.ae' => ['region' => 'eu-west-1', 'host' => 'webservices.amazon.ae'],
        'www.amazon.sa' => ['region' => 'eu-west-1', 'host' => 'webservices.amazon.sa'],
        'www.amazon.com.tr' => ['region' => 'eu-west-1', 'host' => 'webservices.amazon.com.tr'],
        'www.amazon.se' => ['region' => 'eu-west-1', 'host' => 'webservices.amazon.se'],
    ];

    public static function getHost(string $marketplace): string
    {
        return self::$marketplaces[$marketplace]['host'] ?? 'webservices.amazon.com';
    }

    public static function getRegion(string $marketplace): string
    {
        return self::$marketplaces[$marketplace]['region'] ?? 'us-east-1';
    }

    public static function getSupportedMarketplaces(): array
    {
        return array_keys(self::$marketplaces);
    }
}