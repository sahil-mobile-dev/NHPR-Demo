<?php

namespace App\Services;

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
}
