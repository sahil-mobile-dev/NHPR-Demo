<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class AadhaarOTPService
 *
 * Manages Aadhaar OTP generation and verification with the ABDM HPR Registry.
 */
class AadhaarOTPService
{
    protected GatewayTokenService $gatewayService;

    /**
     * AadhaarOTPService constructor.
     */
    public function __construct(GatewayTokenService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * Request an OTP to the user's Aadhaar-registered mobile number.
     *
     * @param  string  $encryptedAadhaar  Base64 encrypted Aadhaar number.
     * @return array Contains txnId and mobileNumber.
     *
     * @throws Exception If the API call fails.
     */
    public function generateOtp(string $encryptedAadhaar): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('Aadhaar OTP Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/v2/registration/aadhaar/generateOtp';

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
            'aadhaar' => $encryptedAadhaar,
        ];

        Log::info('Aadhaar OTP Request: Send OTP', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => ['aadhaar' => '[MASKED]'],
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('Aadhaar OTP Response: Send OTP', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return [
                    'txnId' => $body['txnId'] ?? null,
                    'mobileNumber' => $body['mobileNumber'] ?? null,
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Unable to send Aadhaar OTP.';
            throw new Exception("Aadhaar OTP handshaking failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('Aadhaar OTP Service Exception in generateOtp: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify the Aadhaar OTP to complete user demographic verification.
     *
     * @param  string  $encryptedOtp  Base64 encrypted OTP.
     * @param  string  $txnId  Transaction ID.
     * @return array Contains txnId and mobileNumber.
     *
     * @throws Exception If the API call fails.
     */
    public function verifyOtp(string $encryptedOtp, string $txnId): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('Aadhaar OTP Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/v2/registration/aadhaar/verifyOTP';

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
            'domainName' => '@hpr.abdm',
            'idType' => 'hpr_id',
            'otp' => $encryptedOtp,
            'restrictions' => '',
            'txnId' => $txnId,
        ];

        Log::info('Aadhaar OTP Request: Verify OTP', [
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

            Log::info('Aadhaar OTP Response: Verify OTP', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return [
                    'txnId' => $body['txnId'] ?? null,
                    'mobileNumber' => $body['mobileNumber'] ?? null,
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Invalid Aadhaar OTP.';
            throw new Exception("Aadhaar OTP verification failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('Aadhaar OTP Service Exception in verifyOtp: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate redirect link for Aadhaar authentication.
     *
     * @return array Contains txnId and redirect url.
     *
     * @throws Exception If API call fails.
     */
    public function generateLink(array $scopes = ['nhpr-register'], string $source = 'NHPR'): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('Aadhaar OTP Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/aadhaar/generateLink';

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
            'scopes' => $scopes,
            'source' => $source,
        ];

        Log::info('Aadhaar Link Request: Generate Link', [
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

            Log::info('Aadhaar Link Response: Generate Link', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return [
                    'txnId' => $body['txnId'] ?? null,
                    'url' => $body['url'] ?? null,
                    'status' => $body['status'] ?? null,
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Unable to generate Aadhaar link.';
            throw new Exception("Aadhaar link generation failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('Aadhaar OTP Service Exception in generateLink: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if the user has completed Aadhaar verification.
     *
     * @param  string  $txnId  Transaction ID.
     * @return bool True if authenticated successfully.
     *
     * @throws Exception If API call fails.
     */
    public function isAuthenticated(string $txnId): bool
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('Aadhaar OTP Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/aadhaar/isAuthenticated';

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
            'txnId' => $txnId,
        ];

        Log::info('Aadhaar Auth Request: Check IsAuthenticated', [
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
            $body = $response->body();

            Log::info('Aadhaar Auth Response: Check IsAuthenticated', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                // Response is raw boolean or json boolean "true" or "false"
                return trim(strtolower($body)) === 'true' || $response->json() === true;
            }

            $message = $response->json()['error']['message'] ?? $response->json()['message'] ?? 'Authentication check error.';
            throw new Exception("Aadhaar isAuthenticated failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('Aadhaar OTP Service Exception in isAuthenticated: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch authenticated user's demographics and details.
     *
     * @param  string  $txnId  Transaction ID.
     * @return array User demographic profile.
     *
     * @throws Exception If API call fails.
     */
    public function fetchUserDetails(string $txnId): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('Aadhaar OTP Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/v2/registration/aadhaar/verifyOTP';

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
            'txnId' => $txnId,
        ];

        Log::info('Aadhaar Details Request: verifyOTP fetch details', [
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

            Log::info('Aadhaar Details Response: verifyOTP fetch details', [
                'status' => $statusCode,
                'body' => array_merge($body ?: [], ['photo' => isset($body['photo']) ? '[IMAGE_BASE64_MASKED]' : null]),
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Aadhaar details retrieve error.';
            throw new Exception("Aadhaar details retrieve verifyOTP failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('Aadhaar OTP Service Exception in fetchUserDetails: '.$e->getMessage());
            throw $e;
        }
    }
}
