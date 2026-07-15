<?php

namespace App\Services;

use App\Helpers\AbdmEncryptionHelper;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class HprAccountService
 *
 * Manages HPID exist checks, username suggestions, and pre-verified profile creation in the HPR registry.
 */
class HprAccountService
{
    protected GatewayTokenService $gatewayService;

    /**
     * HprAccountService constructor.
     */
    public function __construct(GatewayTokenService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * Check if HPR ID already exists for the verified Aadhaar transaction.
     *
     * @param  string  $txnId  Transaction ID.
     * @return array Contains profile details if exists, or ['new' => true].
     *
     * @throws Exception If API request fails.
     */
    public function checkHprIdExists(string $txnId): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HPR Account Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/v1/registration/aadhaar/checkHpIdAccountExist';

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

        Log::info('HPR Account Request: Check Exist by Aadhaar', [
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

            Log::info('HPR Account Response: Check Exist by Aadhaar', [
                'status' => $statusCode,
                'body' => array_merge($body ?: [], ['profilePhoto' => isset($body['profilePhoto']) ? '[IMAGE_BASE64_MASKED]' : null]),
            ]);

            if ($response->successful()) {
                // If existing account exists, API returns account payload with hprIdNumber.
                // If it is a new user, it typically returns new = true or similar details.
                if (! empty($body['hprIdNumber'] ?? null)) {
                    return array_merge($body, ['new' => false]);
                }

                return array_merge($body ?: [], ['new' => true]);
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Account check error.';
            throw new Exception("HPR ID check account failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HPR Account Service Exception in checkHprIdExists: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Get suggested usernames based on verified Aadhaar profile.
     *
     * @param  string  $txnId  Transaction ID.
     * @return array Array of suggested usernames.
     *
     * @throws Exception If API request fails.
     */
    public function getUsernameSuggestions(string $txnId): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HPR Account Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/v1/registration/aadhaar/hpid/suggestion';

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

        Log::info('HPR Account Request: Get Username Suggestions', [
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

            Log::info('HPR Account Response: Username Suggestions', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return is_array($body) ? $body : [];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Unable to retrieve suggestions.';
            throw new Exception("HPR username suggestions failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HPR Account Service Exception in getUsernameSuggestions: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Create HPR ID for the pre-verified user.
     *
     * @param  array  $data  Pre-verified demographic registration data.
     * @return array Successful creation details including HPR Token and HPR ID number.
     *
     * @throws Exception If API request fails.
     */
    public function createHprId(array $data): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HPR Account Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/v2/registration/aadhaar/createHprIdWithPreVerified';

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        // Mask sensitive parameters for logging
        $maskedPayload = $data;
        $maskedPayload['email'] = '[MASKED]';
        $maskedPayload['password'] = '[MASKED]';
        if (isset($maskedPayload['profilePhoto'])) {
            $maskedPayload['profilePhoto'] = '[IMAGE_BASE64_MASKED]';
        }

        Log::info('HPR Account Request: Create HPR ID', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => $maskedPayload,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->post($endpoint, $data);

            $statusCode = $response->status();
            $body = $response->json();

            // Mask response token for logging
            $maskedBody = $body;
            if (is_array($maskedBody) && isset($maskedBody['token'])) {
                $maskedBody['token'] = substr($maskedBody['token'], 0, 10).'...[PROTECTED_HPR_TOKEN]...'.substr($maskedBody['token'], -10);
            }

            Log::info('HPR Account Response: Create HPR ID', [
                'status' => $statusCode,
                'body' => $maskedBody,
            ]);

            if ($response->successful()) {
                return [
                    'hprToken' => $body['token'] ?? null,
                    'hprIdNumber' => $body['hprIdNumber'] ?? null,
                    'hprId' => $body['hprId'] ?? null,
                    'name' => $body['name'] ?? null,
                    'gender' => $body['gender'] ?? null,
                    'yearOfBirth' => $body['yearOfBirth'] ?? null,
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Creation of HPR ID profile failed.';
            throw new Exception("HPR ID creation failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HPR Account Service Exception in createHprId: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch registered details of a healthcare professional (ABDM tracking status).
     *
     * @param  string  $hprId  HPR ID (e.g. 71-XXXX-XXXX-XXXX).
     * @return array Status check details from ABDM index.
     *
     * @throws Exception If API request fails.
     */
    public function fetchProfessionalDetails(string $hprId): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HPR Account Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/apis/v1/doctors/fetch-professional-info';

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
            'practitioner' => [
                'id' => $hprId,
                'name' => '',
                'contactNumber' => '',
                'state' => '',
                'registrationNumber' => '',
            ],
        ];

        Log::info('HPR Account Request: Fetch Professional Details', [
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

            Log::info('HPR Account Response: Fetch Professional Details', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Unable to retrieve HPR details.';
            throw new Exception("HPR Fetch professional details failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HPR Account Service Exception in fetchProfessionalDetails: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Initiate HPR ID authentication to receive a transaction ID for OTP verification.
     *
     * @param  string  $hprId  HPR ID.
     * @param  string  $authMethod  E.g., MOBILE_OTP.
     * @return string Transaction ID (txnId).
     *
     * @throws Exception If API request fails.
     */
    public function initiateHprAuth(string $hprId, string $authMethod = 'MOBILE_OTP'): string
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HPR Account Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url', 'https://apihspsbx.abdm.gov.in');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/api/v1/auth/init';

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => config('services.nhpr.x_cm_id', 'sbx'),
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'authMethod' => $authMethod,
            'hprId' => $hprId,
        ];

        Log::info('HPR Auth Request: Initiate Auth', [
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

            Log::info('HPR Auth Response: Initiate Auth', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                $retTxnId = $body['transactionId'] ?? $body['txnId'] ?? null;
                if (empty($retTxnId)) {
                    throw new Exception('ABDM HPR auth init succeeded but txnId/transactionId was empty.');
                }

                return $retTxnId;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Unable to initiate HPR authentication.';
            throw new Exception("HPR Auth initiate failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HPR Account Service Exception in initiateHprAuth: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Confirm HPR authentication using the sent OTP to get a real HPR Token.
     *
     * @param  string  $txnId  Transaction ID from initiate step.
     * @param  string  $otp  Raw 6-digit OTP to be encrypted.
     * @return array Authentication response details (containing token).
     *
     * @throws Exception If API request fails.
     */
    public function confirmHprAuthWithOtp(string $txnId, string $otp): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HPR Account Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url', 'https://apihspsbx.abdm.gov.in');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/api/v1/auth/confirmWithMobileOTP';

        $encryptedOtp = AbdmEncryptionHelper::encrypt($otp);

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => config('services.nhpr.x_cm_id', 'sbx'),
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'otp' => $encryptedOtp,
            'transactionId' => $txnId,
            'txnId' => $txnId,
        ];

        Log::info('HPR Auth Request: Confirm Mobile OTP', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => [
                'otp' => '[ENCRYPTED_MASKED]',
                'txnId' => $txnId,
            ],
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            // Mask returned JWT token for logging
            $maskedBody = $body;
            if (is_array($maskedBody) && ! empty($maskedBody['token'])) {
                $maskedBody['token'] = substr($maskedBody['token'], 0, 10).'...[PROTECTED_HPR_TOKEN]...'.substr($maskedBody['token'], -10);
            }

            Log::info('HPR Auth Response: Confirm Mobile OTP', [
                'status' => $statusCode,
                'body' => $maskedBody,
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'OTP verification failed.';
            throw new Exception("HPR Auth confirm OTP failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HPR Account Service Exception in confirmHprAuthWithOtp: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Authenticate HPR ID using Password to get a real HPR Token.
     *
     * @param  string  $hprId  HPR ID.
     * @param  string  $password  Raw password to be encrypted.
     * @return array Authentication response details (containing token).
     *
     * @throws Exception If API request fails.
     */
    public function confirmHprAuthWithPassword(string $hprId, string $password): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HPR Account Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url', 'https://apihspsbx.abdm.gov.in');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/api/v1/auth/authPassword';

        $encryptedPassword = AbdmEncryptionHelper::encrypt($password);

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => config('services.nhpr.x_cm_id', 'sbx'),
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'hprId' => $hprId,
            'password' => $encryptedPassword,
        ];

        Log::info('HPR Auth Request: Password Auth', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => [
                'hprId' => $hprId,
                'password' => '[ENCRYPTED_MASKED]',
            ],
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            // Mask returned JWT token for logging
            $maskedBody = $body;
            if (is_array($maskedBody) && ! empty($maskedBody['token'])) {
                $maskedBody['token'] = substr($maskedBody['token'], 0, 10).'...[PROTECTED_HPR_TOKEN]...'.substr($maskedBody['token'], -10);
            }

            Log::info('HPR Auth Response: Password Auth', [
                'status' => $statusCode,
                'body' => $maskedBody,
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Password authentication failed.';
            throw new Exception("HPR Auth password failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HPR Account Service Exception in confirmHprAuthWithPassword: '.$e->getMessage());
            throw $e;
        }
    }
}
