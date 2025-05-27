<?php

declare(strict_types=1);

namespace AmazonPaapi5\Security;

use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class EnhancedRequestSigner
{
    private string $accessKey;
    private string $secretKey;
    private string $region;
    private LoggerInterface $logger;
    private array $securityHeaders;

    public function __construct(
        string $accessKey,
        string $secretKey,
        string $region,
        ?LoggerInterface $logger = null,
        array $securityHeaders = []
    ) {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->region = $region;
        $this->logger = $logger ?? new NullLogger();
        $this->securityHeaders = array_merge(
            [
                'X-Security-Version' => '2.0',
                'X-Security-Protocol' => 'TLS1.2',
            ],
            $securityHeaders
        );
    }

    public function signRequest(RequestInterface $request): RequestInterface
    {
        // Generate timestamp and nonce
        $timestamp = time();
        $nonce = $this->generateNonce();
        
        // Create base string to sign
        $stringToSign = $this->createStringToSign($request, $timestamp, $nonce);
        
        // Generate signature
        $signature = $this->generateSignature($stringToSign);
        
        // Add security headers
        $request = $this->addSecurityHeaders($request, $timestamp, $nonce, $signature);
        
        $this->logger->debug('Request signed successfully', [
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'timestamp' => $timestamp
        ]);
        
        return $request;
    }

    private function generateNonce(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Exception $e) {
            $this->logger->warning('Failed to generate secure nonce, using fallback');
            return md5(uniqid((string)mt_rand(), true));
        }
    }

    private function createStringToSign(
        RequestInterface $request,
        int $timestamp,
        string $nonce
    ): string {
        $components = [
            $request->getMethod(),
            $request->getUri()->getPath(),
            $timestamp,
            $nonce,
            (string)$request->getBody()
        ];
        
        return implode("\n", $components);
    }

    private function generateSignature(string $stringToSign): string
    {
        // Create signing key
        $dateKey = hash_hmac('sha256', gmdate('Ymd'), 'AWS4' . $this->secretKey, true);
        $regionKey = hash_hmac('sha256', $this->region, $dateKey, true);
        $serviceKey = hash_hmac('sha256', 'execute-api', $regionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $serviceKey, true);
        
        // Generate final signature
        return hash_hmac('sha256', $stringToSign, $signingKey);
    }

    private function addSecurityHeaders(
        RequestInterface $request,
        int $timestamp,
        string $nonce,
        string $signature
    ): RequestInterface {
        foreach ($this->securityHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        
        return $request
            ->withHeader('X-Timestamp', $timestamp)
            ->withHeader('X-Nonce', $nonce)
            ->withHeader('X-Signature', $signature)
            ->withHeader('X-Access-Key', $this->accessKey)
            ->withHeader('X-Region', $this->region);
    }

    public function validateIncomingRequest(RequestInterface $request): bool
    {
        try {
            // Get headers
            $timestamp = (int)$request->getHeaderLine('X-Timestamp');
            $nonce = $request->getHeaderLine('X-Nonce');
            $receivedSignature = $request->getHeaderLine('X-Signature');
            
            // Check timestamp (within 5 minutes)
            if (abs(time() - $timestamp) > 300) {
                $this->logger->warning('Request timestamp too old');
                return false;
            }
            
            // Verify signature
            $stringToSign = $this->createStringToSign($request, $timestamp, $nonce);
            $expectedSignature = $this->generateSignature($stringToSign);
            
            return hash_equals($expectedSignature, $receivedSignature);
            
        } catch (\Exception $e) {
            $this->logger->error('Request validation failed: ' . $e->getMessage());
            return false;
        }
    }
}