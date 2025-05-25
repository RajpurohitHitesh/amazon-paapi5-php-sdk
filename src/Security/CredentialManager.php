<?php

declare(strict_types=1);

namespace AmazonPaapi5\Security;

use AmazonPaapi5\Config;
use AmazonPaapi5\Exceptions\SecurityException;

class CredentialManager
{
    private string $encryptionKey;
    private string $accessKey;
    private string $secretKey;
    
    public function __construct(Config $config)
    {
        $this->encryptionKey = $config->getEncryptionKey();
        $this->setCredentials(
            $config->getAccessKey(),
            $config->getSecretKey()
        );
    }

    public function setCredentials(string $accessKey, string $secretKey): void
    {
        $this->validateCredentials($accessKey, $secretKey);
        
        $this->accessKey = $this->encrypt($accessKey);
        $this->secretKey = $this->encrypt($secretKey);
    }

    public function getAccessKey(): string
    {
        return $this->decrypt($this->accessKey);
    }

    public function getSecretKey(): string
    {
        return $this->decrypt($this->secretKey);
    }

    private function encrypt(string $data): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            $data,
            'AES-256-CBC',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        if ($encrypted === false) {
            throw new SecurityException('Failed to encrypt data');
        }
        
        return base64_encode($iv . $encrypted);
    }

    private function decrypt(string $data): string
    {
        $decoded = base64_decode($data);
        $iv = substr($decoded, 0, 16);
        $encrypted = substr($decoded, 16);
        
        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        if ($decrypted === false) {
            throw new SecurityException('Failed to decrypt data');
        }
        
        return $decrypted;
    }

    private function validateCredentials(string $accessKey, string $secretKey): void
    {
        if (empty($accessKey) || strlen($accessKey) < 16) {
            throw new AuthenticationException('Invalid access key format');  // Changed from SecurityException
        }
        
        if (empty($secretKey) || strlen($secretKey) < 32) {
            throw new AuthenticationException('Invalid secret key format');  // Changed from SecurityException
        }
    }
}