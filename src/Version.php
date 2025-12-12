<?php

declare(strict_types=1);

namespace AmazonPaapi5;

/**
 * Amazon PA-API 5.0 PHP SDK Version Information
 */
class Version
{
    /**
     * Current SDK version
     */
    public const VERSION = '1.1.1';

    /**
     * Release date
     */
    public const RELEASE_DATE = '2025-12-12';

    /**
     * SDK name
     */
    public const SDK_NAME = 'Amazon PA-API 5.0 PHP SDK';

    /**
     * Minimum PHP version required
     */
    public const MIN_PHP_VERSION = '8.0.0';

    /**
     * Get full version string
     */
    public static function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * Get full SDK information
     */
    public static function getInfo(): array
    {
        return [
            'sdk_name' => self::SDK_NAME,
            'version' => self::VERSION,
            'release_date' => self::RELEASE_DATE,
            'php_version' => PHP_VERSION,
            'min_php_version' => self::MIN_PHP_VERSION,
        ];
    }

    /**
     * Get version as string for User-Agent headers
     */
    public static function getUserAgent(): string
    {
        return sprintf(
            '%s/%s (PHP/%s)',
            str_replace(' ', '-', self::SDK_NAME),
            self::VERSION,
            PHP_VERSION
        );
    }
}
