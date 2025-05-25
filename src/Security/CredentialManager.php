<?php

declare(strict_types=1);

namespace AmazonPaapi5\Security;

use AmazonPaapi5\Exceptions\SecurityException;

class CredentialManager
{
    private string $encryptionKey;
    private array $credentials = [];
    
    public function __construct(string $encryptionKey)
    {
        if (strlen($encryptionKey) < 32) {
            throw new SecurityException('Encryption key must be at least 32 characters long');
        }
        $this->encryptionKey = $encryptionKey;
    }

    public function setCredentials(string $accessKey, string $secretKey): void
    {
        $this->validateCredentials($accessKey, $secretKey);
        
        $this->credentials = [
            'access_key' => $this->encrypt($accessKey),
            'secret_key' => $this->encrypt($secretKey)
        ];
    }

    public function getAccessKey(): string
    {
        return $this->decrypt($this->credentials['access_key']);
    }

    public function getSecretKey(): string
    {
        return $this->decrypt($this->credentials['secret_key']);
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
        
        return base64_encode($iv . $encrypted);
    }

    private function decrypt(string $data): string
    {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    private function validateCredentials(string $accessKey, string $secretKey): void
    {
        if (empty($accessKey) || strlen($accessKey) < 16) {
            throw new SecurityException('Invalid access key format');
        }
        
        if (empty($secretKey) || strlen($secretKey) < 32) {
            throw new SecurityException('Invalid secret key format');
        }
    }
}