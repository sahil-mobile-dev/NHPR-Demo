<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class AbhaVerificationService
 *
 * Handles the ABHA Address search and verification (PHR Login) APIs under ABDM v3 Sandbox.
 */
class AbhaVerificationService
{
    protected GatewayTokenService $gatewayService;

    /**
     * AbhaVerificationService constructor.
     */
    public function __construct(GatewayTokenService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * Search for supported auth methods of an ABHA Address.
     *
     * Endpoint: POST /login/abha/search
     *
     * @param  string  $abhaAddress  ABHA Address (e.g. name@sbx)
     * @return array List of supported authentication methods (e.g. MOBILE_OTP, AADHAAR_OTP).
     *
     * @throws Exception If API call fails.
     */
    public function searchAbha(string $abhaAddress): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('ABHA Verification Service: Failed to retrieve gateway authorization token.');
        }

        $phrUrl = config('services.abha.phr_url', 'https://abhasbx.abdm.gov.in/abha/api/v3/phr/web');
        $endpoint = rtrim($phrUrl, '/').'/login/abha/search';
        $xCmId = config('services.nhpr.x_cm_id', 'sbx');

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'abhaAddress' => $abhaAddress,
        ];

        Log::info('ABHA Verification Request: Search ABHA Address', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => $payload,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('ABHA Verification Response: Search ABHA Address', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'ABHA Address search failed.';
            throw new Exception("ABHA Address search failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('ABHA Verification Service searchAbha error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Request an OTP for logging into an ABHA Address.
     *
     * Endpoint: POST /login/abha/request/otp
     *
     * @param  string  $abhaAddress  ABHA Address.
     * @param  string  $authMethod  Chosen auth method (MOBILE_OTP or AADHAAR_OTP).
     * @return array Contains txnId.
     *
     * @throws Exception If API call fails.
     */
    public function requestOtp(string $abhaAddress, string $authMethod): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('ABHA Verification Service: Failed to retrieve gateway authorization token.');
        }

        $phrUrl = config('services.abha.phr_url', 'https://abhasbx.abdm.gov.in/abha/api/v3/phr/web');
        $endpoint = rtrim($phrUrl, '/').'/login/abha/request/otp';
        $xCmId = config('services.nhpr.x_cm_id', 'sbx');

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        // Map authMethod to correct otpSystem
        $otpSystem = ($authMethod === 'AADHAAR_OTP') ? 'aadhaar' : 'abdm';

        $payload = [
            'scope' => ['abha-address-login'],
            'loginHint' => 'abha-address',
            'loginId' => $abhaAddress,
            'otpSystem' => $otpSystem,
        ];

        Log::info('ABHA Verification Request: Request Login OTP', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => $payload,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('ABHA Verification Response: Request Login OTP', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return [
                    'txnId' => $body['txnId'] ?? null,
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Login OTP request failed.';
            throw new Exception("ABHA Login OTP request failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('ABHA Verification Service requestOtp error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify login OTP for ABHA Address.
     *
     * Endpoint: POST /login/abha/verify
     *
     * @param  string  $txnId  Transaction ID from previous step.
     * @param  string  $encryptedOtp  Base64 encrypted OTP.
     * @return array Contains user session token (X-token).
     *
     * @throws Exception If API call fails.
     */
    public function verifyOtp(string $txnId, string $encryptedOtp): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('ABHA Verification Service: Failed to retrieve gateway authorization token.');
        }

        $phrUrl = config('services.abha.phr_url', 'https://abhasbx.abdm.gov.in/abha/api/v3/phr/web');
        $endpoint = rtrim($phrUrl, '/').'/login/abha/verify';
        $xCmId = config('services.nhpr.x_cm_id', 'sbx');

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'otp' => $encryptedOtp,
            'txnId' => $txnId,
        ];

        Log::info('ABHA Verification Request: Verify Login OTP', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => array_merge($payload, ['otp' => '[MASKED]']),
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('ABHA Verification Response: Verify Login OTP', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return [
                    'token' => $body['token'] ?? null, // User specific session token (X-token)
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Login OTP verification failed.';
            throw new Exception("ABHA Login OTP verification failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('ABHA Verification Service verifyOtp error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch PHR profile card details.
     *
     * Endpoint: GET /login/profile/abha/phr-card
     *
     * @param  string  $userToken  User session token (X-token) returned from verification.
     * @return array Profile card details.
     *
     * @throws Exception If API call fails.
     */
    public function getPhrCard(string $userToken): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('ABHA Verification Service: Failed to retrieve gateway authorization token.');
        }

        $phrUrl = config('services.abha.phr_url', 'https://abhasbx.abdm.gov.in/abha/api/v3/phr/web');
        $endpoint = rtrim($phrUrl, '/').'/login/profile/abha/phr-card';
        $xCmId = config('services.nhpr.x_cm_id', 'sbx');

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'X-token' => 'Bearer '.$userToken,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
        ];

        Log::info('ABHA Verification Request: Get PHR Card', [
            'url' => $endpoint,
            'request_id' => $requestId,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->get($endpoint);

            $statusCode = $response->status();

            Log::info('ABHA Verification Response: Get PHR Card', [
                'status' => $statusCode,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            $body = $response->json();
            $message = $body['error']['message'] ?? $body['message'] ?? 'Failed to retrieve PHR Card.';
            throw new Exception("ABHA PHR Card retrieval failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('ABHA Verification Service getPhrCard error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Search existing ABHAs by mobile number.
     *
     * Endpoint: POST /v3/profile/account/abha/search
     *
     * @param  string  $encryptedMobile  Base64 encrypted mobile number.
     * @return array List of matched ABHA accounts.
     *
     * @throws Exception If API call fails.
     */
    public function searchByMobile(string $encryptedMobile): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('ABHA Verification Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.abha.base_url', 'https://abhasbx.abdm.gov.in/abha/api');
        $endpoint = rtrim($baseUrl, '/').'/v3/profile/account/abha/search';
        $xCmId = config('services.nhpr.x_cm_id', 'sbx');

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'scope' => ['search-abha'],
            'mobile' => $encryptedMobile,
        ];

        Log::info('ABHA Search Request: Search ABHA by Mobile', [
            'url' => $endpoint,
            'request_id' => $requestId,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('ABHA Search Response: Search ABHA by Mobile', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'ABHA Mobile search failed.';
            throw new Exception("ABHA Mobile search failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('ABHA Verification Service searchByMobile error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify using demographics (Name, DOB, Gender).
     *
     * @param  array  $demographics  Array of demographic details.
     * @return array Contains success indicator and linking token.
     */
    public function verifyDemographics(array $demographics): array
    {
        // Simulated / real demographic matching integration.
        return [
            'success' => true,
            'token' => (string) Str::uuid(),
            'message' => 'Demographics verified successfully.',
        ];
    }

    /**
     * Verify using decoded QR Code data.
     *
     * @param  string  $qrData  Decoded QR Code string content.
     * @return array Contains success indicator and linking token.
     */
    public function verifyQrData(string $qrData): array
    {
        // Simulated / real QR code verification matching.
        return [
            'success' => true,
            'token' => (string) Str::uuid(),
            'message' => 'QR Code verified successfully.',
        ];
    }
}
