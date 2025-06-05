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
    
    // Encryption methods
    private const ENCRYPTION_METHOD_SODIUM = 'sodium';
    private const ENCRYPTION_METHOD_OPENSSL = 'openssl';
    
    // OpenSSL configuration
    private const OPENSSL_CIPHER = 'aes-256-gcm';
    private const OPENSSL_IV_LENGTH = 16;
    private const OPENSSL_TAG_LENGTH = 16;
    
    // Sodium constants with fallbacks
    private const SODIUM_NONCE_BYTES = 24; // SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
    private const SODIUM_KEY_BYTES = 32;   // SODIUM_CRYPTO_SECRETBOX_KEYBYTES
    
    private string $activeEncryptionMethod;
    
    public function __construct(
        Config $config,
        ?LoggerInterface $logger = null
    ) {
        $this->config = $config;
        $this->logger = $logger ?? new NullLogger();
        $this->encryptionKey = $config->getEncryptionKey() ?? '';
        
        // Determine and initialize encryption method
        $this->initializeEncryptionMethod();
        
        $this->setCredentials(
            $config->getAccessKey(),
            $config->getSecretKey()
        );
    }

    /**
     * Initialize encryption method with fallback mechanism
     */
    private function initializeEncryptionMethod(): void
    {
        // Try Sodium first (preferred)
        if ($this->isSodiumAvailable()) {
            $this->activeEncryptionMethod = self::ENCRYPTION_METHOD_SODIUM;
            $this->logger->info('Using Sodium encryption method');
            return;
        }
        
        // Fallback to OpenSSL
        if ($this->isOpenSSLAvailable()) {
            $this->activeEncryptionMethod = self::ENCRYPTION_METHOD_OPENSSL;
            $this->logger->info('Sodium not available, falling back to OpenSSL encryption');
            return;
        }
        
        // No encryption available
        throw new SecurityException(
            'Neither Sodium nor OpenSSL encryption is available. ' .
            'Please install sodium extension or ensure OpenSSL is properly configured.'
        );
    }

    /**
     * Check if Sodium extension is available and functional
     */
    public function isSodiumAvailable(): bool
    {
        return extension_loaded('sodium') && 
               function_exists('sodium_crypto_secretbox') &&
               function_exists('sodium_crypto_secretbox_open') &&
               function_exists('sodium_crypto_secretbox_keygen');
    }

    /**
     * Check if OpenSSL is available and supports required cipher
     */
    private function isOpenSSLAvailable(): bool
    {
        return extension_loaded('openssl') && 
               function_exists('openssl_encrypt') &&
               function_exists('openssl_decrypt') &&
               in_array(self::OPENSSL_CIPHER, openssl_get_cipher_methods(), true);
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
            
            $this->logger->info('Credentials updated successfully', [
                'encryption_method' => $this->activeEncryptionMethod
            ]);
            
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

    /**
     * Main encryption method with fallback support
     */
    private function encrypt(string $data): string
    {
        switch ($this->activeEncryptionMethod) {
            case self::ENCRYPTION_METHOD_SODIUM:
                return $this->encryptWithSodium($data);
            
            case self::ENCRYPTION_METHOD_OPENSSL:
                return $this->encryptWithOpenSSL($data);
            
            default:
                throw new SecurityException('No encryption method available');
        }
    }

    /**
     * Main decryption method with fallback support
     */
    private function decrypt(string $data): string
    {
        // Try to detect encryption method from data format
        $detectedMethod = $this->detectEncryptionMethod($data);
        
        if ($detectedMethod) {
            $this->logger->debug('Detected encryption method', ['method' => $detectedMethod]);
            
            switch ($detectedMethod) {
                case self::ENCRYPTION_METHOD_SODIUM:
                    return $this->decryptWithSodium($data);
                
                case self::ENCRYPTION_METHOD_OPENSSL:
                    return $this->decryptWithOpenSSL($data);
            }
        }
        
        // Fallback: try current active method
        switch ($this->activeEncryptionMethod) {
            case self::ENCRYPTION_METHOD_SODIUM:
                return $this->decryptWithSodium($data);
            
            case self::ENCRYPTION_METHOD_OPENSSL:
                return $this->decryptWithOpenSSL($data);
            
            default:
                throw new SecurityException('No decryption method available');
        }
    }

    /**
     * Encrypt data using Sodium
     */
    private function encryptWithSodium(string $data): string
    {
        try {
            // Generate a random nonce
            $nonceBytes = $this->getSodiumNonceBytes();
            $nonce = random_bytes($nonceBytes);
            
            // Encrypt using libsodium
            $encrypted = sodium_crypto_secretbox(
                $data,
                $nonce,
                $this->getOrGenerateKey()
            );
            
            // Combine method identifier, nonce and encrypted data
            $combined = 'SODIUM:' . base64_encode($nonce . $encrypted);
            
            return base64_encode($combined);
            
        } catch (\Exception $e) {
            throw new SecurityException(
                'Sodium encryption failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Decrypt data using Sodium
     */
    private function decryptWithSodium(string $data): string
    {
        try {
            $decoded = base64_decode($data);
            
            if ($decoded === false) {
                throw new SecurityException('Invalid base64 data');
            }
            
            // Remove method identifier
            if (strpos($decoded, 'SODIUM:') === 0) {
                $decoded = base64_decode(substr($decoded, 7));
            }
            
            // Extract nonce and encrypted data
            $nonceBytes = $this->getSodiumNonceBytes();
            $nonce = mb_substr($decoded, 0, $nonceBytes, '8bit');
            $encrypted = mb_substr($decoded, $nonceBytes, null, '8bit');
            
            // Decrypt using libsodium
            $decrypted = sodium_crypto_secretbox_open(
                $encrypted,
                $nonce,
                $this->getOrGenerateKey()
            );
            
            if ($decrypted === false) {
                throw new SecurityException('Sodium decryption failed - invalid data or key');
            }
            
            return $decrypted;
            
        } catch (\Exception $e) {
            throw new SecurityException(
                'Sodium decryption failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Encrypt data using OpenSSL
     */
    private function encryptWithOpenSSL(string $data): string
    {
        try {
            // Generate random IV
            $iv = random_bytes(self::OPENSSL_IV_LENGTH);
            $key = $this->getOrGenerateKey();
            
            // Ensure key is proper length for AES-256
            $key = hash('sha256', $key, true);
            
            // Encrypt with authentication tag
            $tag = '';
            $encrypted = openssl_encrypt(
                $data,
                self::OPENSSL_CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
                '',
                self::OPENSSL_TAG_LENGTH
            );
            
            if ($encrypted === false) {
                throw new SecurityException('OpenSSL encryption failed');
            }
            
            // Combine method identifier, IV, tag and encrypted data
            $combined = 'OPENSSL:' . base64_encode($iv . $tag . $encrypted);
            
            return base64_encode($combined);
            
        } catch (\Exception $e) {
            throw new SecurityException(
                'OpenSSL encryption failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Decrypt data using OpenSSL
     */
    private function decryptWithOpenSSL(string $data): string
    {
        try {
            $decoded = base64_decode($data);
            
            if ($decoded === false) {
                throw new SecurityException('Invalid base64 data');
            }
            
            // Remove method identifier
            if (strpos($decoded, 'OPENSSL:') === 0) {
                $decoded = base64_decode(substr($decoded, 8));
            }
            
            // Extract IV, tag and encrypted data
            $iv = mb_substr($decoded, 0, self::OPENSSL_IV_LENGTH, '8bit');
            $tag = mb_substr($decoded, self::OPENSSL_IV_LENGTH, self::OPENSSL_TAG_LENGTH, '8bit');
            $encrypted = mb_substr($decoded, self::OPENSSL_IV_LENGTH + self::OPENSSL_TAG_LENGTH, null, '8bit');
            
            $key = $this->getOrGenerateKey();
            
            // Ensure key is proper length for AES-256
            $key = hash('sha256', $key, true);
            
            // Decrypt with authentication verification
            $decrypted = openssl_decrypt(
                $encrypted,
                self::OPENSSL_CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );
            
            if ($decrypted === false) {
                throw new SecurityException('OpenSSL decryption failed - invalid data or key');
            }
            
            return $decrypted;
            
        } catch (\Exception $e) {
            throw new SecurityException(
                'OpenSSL decryption failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Detect encryption method from encrypted data
     */
    private function detectEncryptionMethod(string $data): ?string
    {
        try {
            $decoded = base64_decode($data);
            
            if ($decoded === false) {
                return null;
            }
            
            if (strpos($decoded, 'SODIUM:') === 0) {
                return self::ENCRYPTION_METHOD_SODIUM;
            }
            
            if (strpos($decoded, 'OPENSSL:') === 0) {
                return self::ENCRYPTION_METHOD_OPENSSL;
            }
            
            // Legacy data without method identifier - assume it matches current method
            return null;
            
        } catch (\Exception $e) {
            $this->logger->warning('Failed to detect encryption method', ['error' => $e->getMessage()]);
            return null;
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
            switch ($this->activeEncryptionMethod) {
                case self::ENCRYPTION_METHOD_SODIUM:
                    if (function_exists('sodium_crypto_secretbox_keygen')) {
                        $this->encryptionKey = sodium_crypto_secretbox_keygen();
                    } else {
                        $keyBytes = $this->getSodiumKeyBytes();
                        $this->encryptionKey = random_bytes($keyBytes);
                    }
                    break;
                
                case self::ENCRYPTION_METHOD_OPENSSL:
                    // Generate 32-byte key for AES-256
                    $this->encryptionKey = random_bytes(32);
                    break;
                
                default:
                    throw new SecurityException('Cannot generate key: no encryption method available');
            }
            
            $this->logger->info('Generated new encryption key', [
                'method' => $this->activeEncryptionMethod
            ]);
        }
        
        return $this->encryptionKey;
    }

    public function rotateEncryptionKey(): void
    {
        try {
            // Get current credentials
            $currentAccessKey = $this->getAccessKey();
            $currentSecretKey = $this->getSecretKey();
            
            // Generate new key based on active method
            switch ($this->activeEncryptionMethod) {
                case self::ENCRYPTION_METHOD_SODIUM:
                    if (function_exists('sodium_crypto_secretbox_keygen')) {
                        $this->encryptionKey = sodium_crypto_secretbox_keygen();
                    } else {
                        $keyBytes = $this->getSodiumKeyBytes();
                        $this->encryptionKey = random_bytes($keyBytes);
                    }
                    break;
                
                case self::ENCRYPTION_METHOD_OPENSSL:
                    $this->encryptionKey = random_bytes(32);
                    break;
                
                default:
                    throw new SecurityException('Cannot rotate key: no encryption method available');
            }
            
            // Re-encrypt with new key
            $this->setCredentials($currentAccessKey, $currentSecretKey);
            
            $this->logger->info('Encryption key rotated successfully', [
                'method' => $this->activeEncryptionMethod
            ]);
            
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
     * Switch encryption method (useful for migration)
     */
    public function switchEncryptionMethod(string $method): void
    {
        $validMethods = [self::ENCRYPTION_METHOD_SODIUM, self::ENCRYPTION_METHOD_OPENSSL];
        
        if (!in_array($method, $validMethods, true)) {
            throw new SecurityException('Invalid encryption method: ' . $method);
        }
        
        // Check if requested method is available
        if ($method === self::ENCRYPTION_METHOD_SODIUM && !$this->isSodiumAvailable()) {
            throw new SecurityException('Sodium encryption is not available');
        }
        
        if ($method === self::ENCRYPTION_METHOD_OPENSSL && !$this->isOpenSSLAvailable()) {
            throw new SecurityException('OpenSSL encryption is not available');
        }
        
        // Get current credentials before switching
        $currentAccessKey = $this->getAccessKey();
        $currentSecretKey = $this->getSecretKey();
        
        // Switch method and clear encryption key to force regeneration
        $this->activeEncryptionMethod = $method;
        $this->encryptionKey = '';
        
        // Re-encrypt with new method
        $this->setCredentials($currentAccessKey, $currentSecretKey);
        
        $this->logger->info('Switched encryption method', ['new_method' => $method]);
    }

    /**
     * Get current encryption method
     */
    public function getActiveEncryptionMethod(): string
    {
        return $this->activeEncryptionMethod;
    }

    /**
     * Get comprehensive system information for debugging
     */
    public function getSystemInfo(): array
    {
        return [
            'active_encryption_method' => $this->activeEncryptionMethod,
            'sodium_extension_loaded' => extension_loaded('sodium'),
            'sodium_constants_available' => defined('SODIUM_CRYPTO_SECRETBOX_NONCEBYTES'),
            'sodium_functions_available' => function_exists('sodium_crypto_secretbox'),
            'sodium_fully_available' => $this->isSodiumAvailable(),
            'openssl_extension_loaded' => extension_loaded('openssl'),
            'openssl_cipher_available' => in_array(self::OPENSSL_CIPHER, openssl_get_cipher_methods(), true),
            'openssl_fully_available' => $this->isOpenSSLAvailable(),
            'php_version' => PHP_VERSION,
            'encryption_fallback_active' => $this->activeEncryptionMethod === self::ENCRYPTION_METHOD_OPENSSL
        ];
    }

    /**
     * Test encryption/decryption functionality
     */
    public function testEncryption(): bool
    {
        try {
            $testData = 'test_encryption_' . uniqid();
            $encrypted = $this->encrypt($testData);
            $decrypted = $this->decrypt($encrypted);
            
            $success = $testData === $decrypted;
            
            $this->logger->info('Encryption test completed', [
                'success' => $success,
                'method' => $this->activeEncryptionMethod
            ]);
            
            return $success;
            
        } catch (\Exception $e) {
            $this->logger->error('Encryption test failed', [
                'error' => $e->getMessage(),
                'method' => $this->activeEncryptionMethod
            ]);
            
            return false;
        }
    }
}