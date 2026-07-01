<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class MobileOTPService
 *
 * Manages mobile demographic verification and fallback OTP endpoints for HPR registration.
 */
class MobileOTPService
{
    protected GatewayTokenService $gatewayService;

    /**
     * MobileOTPService constructor.
     */
    public function __construct(GatewayTokenService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * Match onboarding mobile number with Aadhaar record via demographic authentication.
     *
     * @param  string  $txnId  Transaction ID.
     * @param  string  $encryptedMobile  Base64 encrypted mobile number.
     * @return bool True if verification succeeds, false if demographic verification failed.
     *
     * @throws Exception If API request fails.
     */
    public function verifyDemographicMobile(string $txnId, string $encryptedMobile): bool
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('Mobile OTP Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/v2/registration/aadhaar/demographicAuthViaMobile';

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
            'mobileNumber' => $encryptedMobile,
        ];

        Log::info('Mobile Auth Request: Demographic Check', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => ['txnId' => $txnId, 'mobileNumber' => '[MASKED]'],
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('Mobile Auth Response: Demographic Check', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return (bool) ($body['verified'] ?? false);
            }

            // We do not throw exceptions for failed demographic match (verified = false),
            // but we do throw for actual API/Handshake connection errors.
            $message = $body['error']['message'] ?? $body['message'] ?? 'Demographic mobile verification API error.';
            throw new Exception("Mobile demographic auth failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('Mobile OTP Service Exception in verifyDemographicMobile: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Fallback Mobile OTP generation.
     *
     * @param  string  $mobile  Raw mobile number.
     * @param  string  $txnId  Transaction ID.
     * @return array Contains txnId and mobileNumber.
     *
     * @throws Exception If API request fails.
     */
    public function generateMobileOtp(string $mobile, string $txnId): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('Mobile OTP Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/v1/registration/aadhaar/generateMobileOTP';

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
            'mobile' => $mobile,
            'txnId' => $txnId,
        ];

        Log::info('Mobile OTP Request: Send OTP', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => ['mobile' => '[MASKED]', 'txnId' => $txnId],
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('Mobile OTP Response: Send OTP', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return [
                    'txnId' => $body['txnId'] ?? null,
                    'mobileNumber' => $body['mobileNumber'] ?? null,
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Unable to send Mobile OTP.';
            throw new Exception("Mobile OTP request failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('Mobile OTP Service Exception in generateMobileOtp: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Fallback Mobile OTP verification.
     *
     * @param  string  $encryptedOtp  Base64 encrypted Mobile OTP.
     * @param  string  $txnId  Transaction ID.
     * @return array Contains txnId and mobileNumber.
     *
     * @throws Exception If API request fails.
     */
    public function verifyMobileOtp(string $encryptedOtp, string $txnId): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('Mobile OTP Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/v1/registration/aadhaar/verifyMobileOTP';

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

        Log::info('Mobile OTP Request: Verify OTP', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => ['otp' => '[MASKED]', 'txnId' => $txnId],
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('Mobile OTP Response: Verify OTP', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return [
                    'txnId' => $body['txnId'] ?? null,
                    'mobileNumber' => $body['mobileNumber'] ?? null,
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Invalid Mobile OTP.';
            throw new Exception("Mobile OTP verification failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('Mobile OTP Service Exception in verifyMobileOtp: '.$e->getMessage());
            throw $e;
        }
    }
}
