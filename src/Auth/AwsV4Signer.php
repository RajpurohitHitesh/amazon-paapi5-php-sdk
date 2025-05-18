<?php

declare(strict_types=1);

namespace AmazonPaapi5\Auth;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class AwsV4Signer
{
    private string $accessKey;
    private string $secretKey;
    private string $region;
    private string $service = 'ProductAdvertisingAPI';

    public function __construct(string $accessKey, string $secretKey, string $region)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->region = $region;
    }

    public function signRequest(RequestInterface $request, string $path, string $payload): RequestInterface
    {
        $timestamp = gmdate('Ymd\THis\Z');
        $date = gmdate('Ymd');

        $headers = [
            'content-encoding' => 'amz-1.0',
            'content-type' => 'application/json; charset=utf-8',
            'host' => $request->getUri()->getHost(),
            'x-amz-date' => $timestamp,
            'x-amz-target' => 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.' . $this->getOperationName($path),
        ];

        $canonicalRequest = $this->createCanonicalRequest($request, $headers, $payload);
        $stringToSign = $this->createStringToSign($timestamp, $date, $canonicalRequest);
        $signature = $this->calculateSignature($date, $stringToSign);

        $headers['Authorization'] = $this->buildAuthorizationHeader($timestamp, $date, $signature);

        return new Request(
            $request->getMethod(),
            $request->getUri(),
            $headers,
            $payload
        );
    }

    private function createCanonicalRequest(RequestInterface $request, array $headers, string $payload): string
    {
        $canonicalHeaders = '';
        ksort($headers);
        foreach ($headers as $key => $value) {
            $canonicalHeaders .= strtolower($key) . ':' . trim($value) . "\n";
        }

        $signedHeaders = implode(';', array_map('strtolower', array_keys($headers)));

        return implode("\n", [
            $request->getMethod(),
            $request->getUri()->getPath(),
            '',
            $canonicalHeaders,
            $signedHeaders,
            hash('sha256', $payload)
        ]);
    }

    private function createStringToSign(string $timestamp, string $date, string $canonicalRequest): string
    {
        return implode("\n", [
            'AWS4-HMAC-SHA256',
            $timestamp,
            "{$date}/{$this->region}/{$this->service}/aws4_request",
            hash('sha256', $canonicalRequest)
        ]);
    }

    private function calculateSignature(string $date, string $stringToSign): string
    {
        $kDate = hash_hmac('sha256', $date, 'AWS4' . $this->secretKey, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', $this->service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        return hash_hmac('sha256', $stringToSign, $kSigning);
    }

    private function buildAuthorizationHeader(string $timestamp, string $date, string $signature): string
    {
        $signedHeaders = 'content-encoding;content-type;host;x-amz-date;x-amz-target';
        return "AWS4-HMAC-SHA256 Credential={$this->accessKey}/{$date}/{$this->region}/{$this->service}/aws4_request, " .
               "SignedHeaders={$signedHeaders}, Signature={$signature}";
    }

    private function getOperationName(string $path): string
    {
        $map = [
            '/paapi5/searchitems' => 'SearchItems',
            '/paapi5/getitems' => 'GetItems',
            '/paapi5/getvariations' => 'GetVariations',
            '/paapi5/getbrowsenodes' => 'GetBrowseNodes',
        ];
        return $map[$path] ?? 'Unknown';
    }
}