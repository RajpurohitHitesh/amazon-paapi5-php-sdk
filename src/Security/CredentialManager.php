<?php

declare(strict_types=1);

namespace AmazonPaapi5\Security;

use AmazonPaapi5\Config;
use AmazonPaapi5\Exceptions\SecurityException;
use AmazonPaapi5\Exceptions\AuthenticationException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CredentialManager
{
    private Config $config;
    private LoggerInterface $logger;
    private string $encryptionKey;
    private string $accessKey;
    private string $secretKey;
    private array $cachedCredentials = [];
    private const ENCRYPTION_ALGO = 'aes-256-gcm';
    
    // Sodium constants with fallbacks
    private const SODIUM_NONCE_BYTES = 24; // SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
    private const SODIUM_KEY_BYTES = 32;   // SODIUM_CRYPTO_SECRETBOX_KEYBYTES
    
    public function __construct(
        Config $config,
        ?LoggerInterface $logger = null
    ) {
        $this->config = $config;
        $this->logger = $logger ?? new NullLogger();
        $this->encryptionKey = $config->getEncryptionKey();
        
        // Check sodium availability
        $this->checkSodiumSupport();
        
        $this->setCredentials(
            $config->getAccessKey(),
            $config->getSecretKey()
        );
    }

    private function checkSodiumSupport(): void
    {
        if (!extension_loaded('sodium')) {
            throw new SecurityException(
                'Sodium extension is required for encryption but not available'
            );
        }

        // Check if constants are available
        if (!defined('SODIUM_CRYPTO_SECRETBOX_NONCEBYTES')) {
            $this->logger->warning('SODIUM_CRYPTO_SECRETBOX_NONCEBYTES constant not defined, using fallback');
        }
    }

    private function getSodiumNonceBytes(): int
    {
        return defined('SODIUM_CRYPTO_SECRETBOX_NONCEBYTES') 
            ? SODIUM_CRYPTO_SECRETBOX_NONCEBYTES 
            : self::SODIUM_NONCE_BYTES;
    }

    private function getSodiumKeyBytes(): int
    {
        return defined('SODIUM_CRYPTO_SECRETBOX_KEYBYTES') 
            ? SODIUM_CRYPTO_SECRETBOX_KEYBYTES 
            : self::SODIUM_KEY_BYTES;
    }

    public function setCredentials(string $accessKey, string $secretKey): void
    {
        $this->validateCredentials($accessKey, $secretKey);
        
        try {
            $this->accessKey = $this->encrypt($accessKey);
            $this->secretKey = $this->encrypt($secretKey);
            
            // Clear cache after updating credentials
            $this->cachedCredentials = [];
            
            $this->logger->info('Credentials updated successfully');
            
        } catch (\Exception $e) {
            throw new SecurityException(
                'Failed to set credentials: ' . $e->getMessage()
            );
        }
    }

    public function getAccessKey(): string
    {
        // Check cache first
        if (isset($this->cachedCredentials['access_key'])) {
            return $this->cachedCredentials['access_key'];
        }
        
        $decrypted = $this->decrypt($this->accessKey);
        $this->cachedCredentials['access_key'] = $decrypted;
        
        return $decrypted;
    }

    public function getSecretKey(): string
    {
        // Check cache first
        if (isset($this->cachedCredentials['secret_key'])) {
            return $this->cachedCredentials['secret_key'];
        }
        
        $decrypted = $this->decrypt($this->secretKey);
        $this->cachedCredentials['secret_key'] = $decrypted;
        
        return $decrypted;
    }

    private function encrypt(string $data): string
    {
        try {
            // Generate a random nonce with fallback
            $nonceBytes = $this->getSodiumNonceBytes();
            $nonce = random_bytes($nonceBytes);
            
            // Check if sodium functions are available
            if (!function_exists('sodium_crypto_secretbox')) {
                throw new SecurityException('Sodium encryption functions not available');
            }
            
            // Encrypt using libsodium (more secure than openssl)
            $encrypted = sodium_crypto_secretbox(
                $data,
                $nonce,
                $this->getOrGenerateKey()
            );
            
            // Combine nonce and encrypted data
            $combined = $nonce . $encrypted;
            
            return base64_encode($combined);
            
        } catch (\Exception $e) {
            throw new SecurityException(
                'Encryption failed: ' . $e->getMessage()
            );
        }
    }

    private function decrypt(string $data): string
    {
        try {
            $decoded = base64_decode($data);
            
            if ($decoded === false) {
                throw new SecurityException('Invalid base64 data');
            }
            
            // Extract nonce and encrypted data with fallback
            $nonceBytes = $this->getSodiumNonceBytes();
            $nonce = mb_substr($decoded, 0, $nonceBytes, '8bit');
            $encrypted = mb_substr($decoded, $nonceBytes, null, '8bit');
            
            // Check if sodium functions are available
            if (!function_exists('sodium_crypto_secretbox_open')) {
                throw new SecurityException('Sodium decryption functions not available');
            }
            
            // Decrypt using libsodium
            $decrypted = sodium_crypto_secretbox_open(
                $encrypted,
                $nonce,
                $this->getOrGenerateKey()
            );
            
            if ($decrypted === false) {
                throw new SecurityException('Decryption failed - invalid data or key');
            }
            
            return $decrypted;
            
        } catch (\Exception $e) {
            throw new SecurityException(
                'Decryption failed: ' . $e->getMessage()
            );
        }
    }

    private function validateCredentials(string $accessKey, string $secretKey): void
    {
        if (empty($accessKey) || strlen($accessKey) < 16) {
            throw new AuthenticationException('Invalid access key format');
        }
        
        if (empty($secretKey) || strlen($secretKey) < 32) {
            throw new AuthenticationException('Invalid secret key format');
        }
    }

    private function getOrGenerateKey(): string
    {
        if (!$this->encryptionKey) {
            // Check if sodium_crypto_secretbox_keygen is available
            if (function_exists('sodium_crypto_secretbox_keygen')) {
                $this->encryptionKey = sodium_crypto_secretbox_keygen();
            } else {
                // Fallback key generation
                $keyBytes = $this->getSodiumKeyBytes();
                $this->encryptionKey = random_bytes($keyBytes);
            }
            $this->logger->info('Generated new encryption key');
        }
        return $this->encryptionKey;
    }

    public function rotateEncryptionKey(): void
    {
        try {
            // Get current credentials
            $currentAccessKey = $this->getAccessKey();
            $currentSecretKey = $this->getSecretKey();
            
            // Generate new key
            if (function_exists('sodium_crypto_secretbox_keygen')) {
                $this->encryptionKey = sodium_crypto_secretbox_keygen();
            } else {
                $keyBytes = $this->getSodiumKeyBytes();
                $this->encryptionKey = random_bytes($keyBytes);
            }
            
            // Re-encrypt with new key
            $this->setCredentials($currentAccessKey, $currentSecretKey);
            
            $this->logger->info('Encryption key rotated successfully');
            
        } catch (\Exception $e) {
            throw new SecurityException(
                'Failed to rotate encryption key: ' . $e->getMessage()
            );
        }
    }

    public function clearCredentials(): void
    {
        $this->accessKey = '';
        $this->secretKey = '';
        $this->cachedCredentials = [];
        
        $this->logger->info('Credentials cleared successfully');
    }

    /**
     * Check if Sodium extension is properly configured
     */
    public function isSodiumAvailable(): bool
    {
        return extension_loaded('sodium') && 
               function_exists('sodium_crypto_secretbox') &&
               function_exists('sodium_crypto_secretbox_open');
    }

    /**
     * Get system information for debugging
     */
    public function getSystemInfo(): array
    {
        return [
            'sodium_extension_loaded' => extension_loaded('sodium'),
            'sodium_constants_available' => defined('SODIUM_CRYPTO_SECRETBOX_NONCEBYTES'),
            'sodium_functions_available' => function_exists('sodium_crypto_secretbox'),
            'php_version' => PHP_VERSION,
            'using_fallback_constants' => !defined('SODIUM_CRYPTO_SECRETBOX_NONCEBYTES')
        ];
    }
}