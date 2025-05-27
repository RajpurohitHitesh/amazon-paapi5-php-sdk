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
    
    public function __construct(
        Config $config,
        ?LoggerInterface $logger = null
    ) {
        $this->config = $config;
        $this->logger = $logger ?? new NullLogger();
        $this->encryptionKey = $config->getEncryptionKey();
        
        $this->setCredentials(
            $config->getAccessKey(),
            $config->getSecretKey()
        );
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
            // Generate a random nonce
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            
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
            
            // Extract nonce and encrypted data
            $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
            $encrypted = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
            
            // Decrypt using libsodium
            $decrypted = sodium_crypto_secretbox_open(
                $encrypted,
                $nonce,
                $this->getOrGenerateKey()
            );
            
            if ($decrypted === false) {
                throw new SecurityException('Decryption failed');
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
            $this->encryptionKey = sodium_crypto_secretbox_keygen();
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
            $this->encryptionKey = sodium_crypto_secretbox_keygen();
            
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
}