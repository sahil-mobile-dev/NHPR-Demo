<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class GatewayTokenService
 *
 * Responsible for generating and managing the NHPR Gateway Access Token.
 * Handles the session API requests, caching of the generated token,
 * error handling, and secure logging of API transactions.
 */
class GatewayTokenService
{
    /**
     * Cache key for the raw access token string.
     */
    public const CACHE_KEY_TOKEN = 'nhpr_gateway_token';

    /**
     * Cache key for the detailed token response metadata.
     */
    public const CACHE_KEY_METADATA = 'nhpr_gateway_token_metadata';

    /**
     * Get a valid Gateway Access Token, retrieving from cache if available
     * or generating a fresh one if expired.
     *
     * @return string|null The active access token, or null if generation fails.
     */
    public function getValidToken(): ?string
    {
        $token = Cache::get(self::CACHE_KEY_TOKEN);

        if ($token) {
            return $token;
        }

        try {
            $response = $this->generateToken();

            return $response['accessToken'] ?? null;
        } catch (Exception $e) {
            Log::error('NHPR Gateway: Failed to retrieve valid cached or new token: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get the cached token metadata for UI display.
     *
     * @return array|null Detailed token information, or null if none exists.
     */
    public function getCachedMetadata(): ?array
    {
        return Cache::get(self::CACHE_KEY_METADATA);
    }

    /**
     * Clear cached token and metadata.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_TOKEN);
        Cache::forget(self::CACHE_KEY_METADATA);
    }

    /**
     * Generate a new NHPR Gateway Access Token by calling the Gateway Session API.
     *
     * @param  bool  $forceRefresh  If true, skips cache check and forces a new API request.
     * @return array The successful session response details.
     *
     * @throws Exception If request validation, connection, or API returns a failure status.
     */
    public function generateToken(bool $forceRefresh = false): array
    {
        if (! $forceRefresh) {
            $metadata = Cache::get(self::CACHE_KEY_METADATA);
            $token = Cache::get(self::CACHE_KEY_TOKEN);
            if ($metadata && $token) {
                return $metadata;
            }
        }

        $baseUrl = session('nhpr_credential_base_url', config('services.nhpr.base_url'));
        $clientId = session('nhpr_credential_client_id', config('services.nhpr.client_id'));
        $clientSecret = session('nhpr_credential_client_secret', config('services.nhpr.client_secret'));
        $xCmId = session('nhpr_credential_x_cm_id', config('services.nhpr.x_cm_id'));

        if (empty($baseUrl) || empty($clientId) || empty($clientSecret) || empty($xCmId)) {
            throw new Exception('NHPR Gateway: Missing required configuration variables in config/services.php.');
        }

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();
        $endpoint = rtrim($baseUrl, '/').'/api/hiecm/gateway/v3/sessions';

        $headers = [
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'grantType' => 'client_credentials',
        ];

        // Mask secrets for secure logging (Rule 9)
        $maskedPayload = array_merge($payload, ['clientSecret' => '[MASKED]']);
        Log::info('NHPR Gateway Request', [
            'url' => $endpoint,
            'headers' => array_merge($headers, ['REQUEST-ID' => $requestId]),
            'body' => $maskedPayload,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10) // 10 seconds timeout
                ->retry(3, 100, throw: false) // 3 retries, 100ms delay, don't throw automatically on 4xx/5xx
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            // Mask response secrets for logging
            $maskedBody = $body;
            if (is_array($maskedBody)) {
                if (isset($maskedBody['accessToken'])) {
                    $maskedBody['accessToken'] = substr($maskedBody['accessToken'], 0, 10).'...'.substr($maskedBody['accessToken'], -10);
                }
                if (isset($maskedBody['refreshToken'])) {
                    $maskedBody['refreshToken'] = '[MASKED]';
                }
            }

            Log::info('NHPR Gateway Response', [
                'status' => $statusCode,
                'body' => $maskedBody,
            ]);

            if ($response->successful()) {
                $accessToken = $body['accessToken'] ?? null;
                $expiresIn = (int) ($body['expiresIn'] ?? 36000);
                $refreshToken = $body['refreshToken'] ?? null;
                $tokenType = $body['tokenType'] ?? 'bearer';

                if (empty($accessToken)) {
                    throw new Exception('NHPR Gateway: Successful response, but accessToken is missing.');
                }

                $tokenData = [
                    'accessToken' => $accessToken,
                    'refreshToken' => $refreshToken,
                    'expiresIn' => $expiresIn,
                    'tokenType' => $tokenType,
                    'generatedAt' => now()->toIso8601String(),
                ];

                // Reuse token until expiration. Keep a safety buffer of 60 seconds.
                $cacheTtl = max(10, $expiresIn - 60);

                Cache::put(self::CACHE_KEY_TOKEN, $accessToken, $cacheTtl);
                Cache::put(self::CACHE_KEY_METADATA, $tokenData, $cacheTtl);

                return $tokenData;
            }

            // Handle API error statuses cleanly (Rule 16)
            $errorMessage = $this->getFriendlyErrorMessage($statusCode, $body);
            throw new Exception("NHPR Gateway (HTTP {$statusCode}): {$errorMessage}");
        } catch (ConnectionException $e) {
            Log::error('NHPR Gateway connection failed or timed out: '.$e->getMessage(), [
                'request_id' => $requestId,
            ]);
            throw new Exception('NHPR Gateway Connection Failure: The request timed out or server is unreachable. Please verify your connection.');
        } catch (RequestException $e) {
            Log::error('NHPR Gateway HTTP error occurred: '.$e->getMessage(), [
                'request_id' => $requestId,
            ]);
            throw new Exception('NHPR Gateway Request Failure: An HTTP error occurred during the session handshake.');
        } catch (Exception $e) {
            Log::error('NHPR Gateway Exception: '.$e->getMessage(), [
                'request_id' => $requestId,
            ]);
            throw $e;
        }
    }

    /**
     * Translate HTTP error status code into a user-friendly error message.
     *
     * @param  int  $statusCode  The HTTP response status code.
     * @param  mixed  $body  The parsed JSON body of the error response.
     * @return string Friendly error message.
     */
    protected function getFriendlyErrorMessage(int $statusCode, mixed $body): string
    {
        $apiError = '';
        if (is_array($body) && isset($body['error']['message'])) {
            $apiError = ' - '.$body['error']['message'];
        } elseif (is_array($body) && isset($body['message'])) {
            $apiError = ' - '.$body['message'];
        }

        return match ($statusCode) {
            400 => 'Bad Request. The request format or parameters are invalid'.$apiError.'.',
            401 => 'Unauthorized. Please check that your Client ID and Client Secret are correct'.$apiError.'.',
            403 => 'Forbidden. The requested action is not allowed for your credentials'.$apiError.'.',
            404 => 'API Endpoint not found. Please verify the base URL configuration'.$apiError.'.',
            422 => 'Unprocessable Entity. The request failed validation on the gateway'.$apiError.'.',
            429 => 'Rate limit exceeded. Too many requests have been sent to the gateway. Please try again later.',
            500 => 'Internal Server Error on the ABDM Gateway'.$apiError.'.',
            503 => 'ABDM Gateway is temporarily unavailable or down for maintenance. Please try again later.',
            default => 'Unknown error occurred on ABDM Gateway'.$apiError.'.',
        };
    }
}
