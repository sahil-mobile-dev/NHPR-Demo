<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class HiuConsentService
 *
 * Handles outbound consent management requests from HIU to ABDM Gateway.
 */
class HiuConsentService
{
    protected GatewayTokenService $gatewayService;

    public function __construct(GatewayTokenService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * Create a consent request on ABDM Gateway.
     *
     * Endpoint: POST /v3/consent/request
     */
    public function createConsentRequest(array $consentData): array
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        if (! $realApiMode) {
            Log::info('HIU Consent: Simulating Create Consent Request', $consentData);

            return [
                'requestId' => (string) Str::uuid(),
                'status' => 'ACCEPTED',
            ];
        }

        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HIU Consent Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.nhpr.base_url', 'https://dev.abdm.gov.in');
        $endpoint = rtrim($baseUrl, '/').'/api/hiecm/gateway/v3/consent/request';
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
            'consent' => $consentData,
        ];

        Log::info('HIU Request: Create Consent Request', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => $payload,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('HIU Response: Create Consent Request', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Create consent request failed.';
            throw new Exception("ABDM Create Consent Request failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HIU Consent Service createConsentRequest error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch Consent Artefact details from ABDM Gateway.
     *
     * Endpoint: POST /v3/consent/fetch
     */
    public function fetchConsentArtefact(string $consentId): array
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        if (! $realApiMode) {
            Log::info('HIU Consent: Simulating Fetch Consent Artefact', ['consentId' => $consentId]);

            return [
                'consentId' => $consentId,
                'status' => 'GRANTED',
            ];
        }

        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HIU Consent Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.nhpr.base_url', 'https://dev.abdm.gov.in');
        $endpoint = rtrim($baseUrl, '/').'/api/hiecm/gateway/v3/consent/fetch';
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
            'consentId' => $consentId,
        ];

        Log::info('HIU Request: Fetch Consent Artefact', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => $payload,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('HIU Response: Fetch Consent Artefact', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Fetch consent artefact failed.';
            throw new Exception("ABDM Fetch Consent Artefact failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HIU Consent Service fetchConsentArtefact error: '.$e->getMessage());
            throw $e;
        }
    }
}
