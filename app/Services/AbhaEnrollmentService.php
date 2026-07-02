<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class AbhaEnrollmentService
 *
 * Handles the ABHA number enrollment APIs under ABDM v3 Sandbox.
 */
class AbhaEnrollmentService
{
    protected GatewayTokenService $gatewayService;

    /**
     * AbhaEnrollmentService constructor.
     */
    public function __construct(GatewayTokenService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * Request an OTP for Aadhaar enrollment.
     *
     * Endpoint: POST /v3/enrollment/request/otp
     *
     * @param  string  $encryptedAadhaar  Base64 encrypted Aadhaar number (using OAEP padding).
     * @return array Contains txnId.
     *
     * @throws Exception If API call fails.
     */
    public function requestOtp(string $encryptedAadhaar): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('ABHA Enrollment Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.abha.base_url', 'https://abhasbx.abdm.gov.in/abha/api');
        $endpoint = rtrim($baseUrl, '/').'/v3/enrollment/request/otp';
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
            'scope' => ['abha-enrol', 'mobile-verify'],
            'loginHint' => 'aadhaar',
            'loginId' => $encryptedAadhaar,
            'otpSystem' => 'aadhaar',
        ];

        Log::info('ABHA Enrollment Request: Request OTP', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => array_merge($payload, ['loginId' => '[MASKED]']),
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('ABHA Enrollment Response: Request OTP', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return [
                    'txnId' => $body['txnId'] ?? null,
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Failed to send enrollment OTP.';
            throw new Exception("ABHA Enrollment OTP generation failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('ABHA Enrollment Service generate OTP error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Complete enrollment by verifying Aadhaar OTP.
     *
     * Endpoint: POST /v3/enrollment/enrol/byAadhaar
     *
     * @param  string  $txnId  Transaction ID from previous step.
     * @param  string  $encryptedOtp  Base64 encrypted OTP (using OAEP padding).
     * @return array Demographic & account details of newly created or existing ABHA.
     *
     * @throws Exception If API call fails.
     */
    public function enrolByAadhaar(string $txnId, string $encryptedOtp): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('ABHA Enrollment Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.abha.base_url', 'https://abhasbx.abdm.gov.in/abha/api');
        $endpoint = rtrim($baseUrl, '/').'/v3/enrollment/enrol/byAadhaar';
        $xCmId = config('services.nhpr.x_cm_id', 'sbx');

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'T-TOKEN' => $txnId,
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'authData' => [
                'authMethods' => ['otp'],
                'otp' => [
                    'txnId' => $txnId,
                    'otpValue' => $encryptedOtp,
                ],
            ],
            'consent' => [
                'code' => 'abha-enrollment',
                'version' => '1.4',
            ],
        ];

        Log::info('ABHA Enrollment Request: Enrol by Aadhaar', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => [
                'authData' => [
                    'authMethods' => ['otp'],
                    'otp' => [
                        'txnId' => $txnId,
                        'otpValue' => '[MASKED]',
                    ],
                ],
            ],
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(15) // enrollment can take slightly longer
                ->retry(3, 100, throw: false)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('ABHA Enrollment Response: Enrol by Aadhaar', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Verification of enrollment OTP failed.';
            throw new Exception("ABHA Enrollment verification failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('ABHA Enrollment Service enrolByAadhaar error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate a communication mobile OTP for enrollment (if different from Aadhaar-registered mobile).
     *
     * Endpoint: POST /v3/enrollment/request/otp
     *
     * @param  string  $txnId  Transaction ID from previous step.
     * @param  string  $encryptedMobile  Base64 encrypted mobile number.
     * @return array Contains txnId.
     *
     * @throws Exception If API call fails.
     */
    public function generateMobileOtp(string $txnId, string $encryptedMobile): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('ABHA Enrollment Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.abha.base_url', 'https://abhasbx.abdm.gov.in/abha/api');
        $endpoint = rtrim($baseUrl, '/').'/v3/enrollment/request/otp';
        $xCmId = config('services.nhpr.x_cm_id', 'sbx');

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'T-TOKEN' => $txnId,
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'scope' => ['mobile-verify'],
            'loginHint' => 'mobile',
            'loginId' => $encryptedMobile,
            'otpSystem' => 'abdm',
        ];

        Log::info('ABHA Enrollment Request: Request Mobile OTP', [
            'url' => $endpoint,
            'request_id' => $requestId,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('ABHA Enrollment Response: Request Mobile OTP', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return [
                    'txnId' => $body['txnId'] ?? null,
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Failed to send mobile OTP.';
            throw new Exception("Mobile OTP request failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('ABHA Enrollment Service generateMobileOtp error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify mobile OTP for enrollment.
     *
     * Endpoint: POST /v3/enrollment/enrol/byMobile
     *
     * @param  string  $txnId  Transaction ID from previous step.
     * @param  string  $encryptedOtp  Base64 encrypted OTP.
     * @return array Demographic & account details of newly created ABHA.
     *
     * @throws Exception If API call fails.
     */
    public function verifyMobileOtp(string $txnId, string $encryptedOtp): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('ABHA Enrollment Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.abha.base_url', 'https://abhasbx.abdm.gov.in/abha/api');
        $endpoint = rtrim($baseUrl, '/').'/v3/enrollment/enrol/byMobile';
        $xCmId = config('services.nhpr.x_cm_id', 'sbx');

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'T-TOKEN' => $txnId,
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'otp' => $encryptedOtp,
        ];

        Log::info('ABHA Enrollment Request: Verify Mobile OTP', [
            'url' => $endpoint,
            'request_id' => $requestId,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('ABHA Enrollment Response: Verify Mobile OTP', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Failed to verify mobile OTP.';
            throw new Exception("Mobile OTP verification failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('ABHA Enrollment Service verifyMobileOtp error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Download/Get the generated ABHA Card QR Code.
     *
     * Endpoint: GET /v3/profile/account/qrCode
     *
     * @param  string  $userToken  User specific session token (X-token).
     * @return string Base64 encoded PNG card image.
     *
     * @throws Exception If API call fails.
     */
    public function getAbhaCard(string $userToken): string
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('ABHA Enrollment Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.abha.base_url', 'https://abhasbx.abdm.gov.in/abha/api');
        $endpoint = rtrim($baseUrl, '/').'/v3/profile/account/qrCode';
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

        Log::info('ABHA Enrollment Request: Get QR Code Image', [
            'url' => $endpoint,
            'request_id' => $requestId,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->get($endpoint);

            $statusCode = $response->status();

            Log::info('ABHA Enrollment Response: Get QR Code Image', [
                'status' => $statusCode,
            ]);

            if ($response->successful()) {
                return base64_encode($response->body());
            }

            throw new Exception("ABHA Card QR Code download failed (HTTP {$statusCode})");
        } catch (Exception $e) {
            Log::error('ABHA Enrollment Service getAbhaCard error: '.$e->getMessage());
            throw $e;
        }
    }
}
