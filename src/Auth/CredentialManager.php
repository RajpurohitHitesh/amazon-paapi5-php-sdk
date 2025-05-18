<?php

declare(strict_types=1);

namespace AmazonPaapi5\Auth;

class CredentialManager
{
    private string $encryptedAccessKey;
    private string $encryptedSecretKey;
    private string $encryptionKey;

    public function __construct(string $accessKey, string $secretKey, string $encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;
        $this->encryptedAccessKey = $this->encrypt($accessKey);
        $this->encryptedSecretKey = $this->encrypt($secretKey);
    }

    public function getAccessKey(): string
    {
        return $this->decrypt($this->encryptedAccessKey);
    }

    public function getSecretKey(): string
    {
        return $this->decrypt($this->encryptedSecretKey);
    }

    private function encrypt(string $data): string
    {
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    private function decrypt(string $data): string
    {
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $this->encryptionKey, 0, $iv);
    }
}