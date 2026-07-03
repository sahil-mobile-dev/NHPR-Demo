<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class HiuHealthInformationService
 *
 * Handles outbound health data requests from HIU to ABDM Gateway.
 */
class HiuHealthInformationService
{
    protected GatewayTokenService $gatewayService;

    public function __construct(GatewayTokenService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * Request health information transfer from HIP.
     *
     * Endpoint: POST /v3/health-information/request
     */
    public function requestHealthInformation(string $consentId, string $consentArtefactId, array $keyMaterial): array
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $transactionId = (string) Str::uuid();

        if (! $realApiMode) {
            Log::info('HIU Health Request: Simulating Request Health Information', [
                'consentId' => $consentId,
                'consentArtefactId' => $consentArtefactId,
                'transactionId' => $transactionId,
            ]);

            return [
                'transactionId' => $transactionId,
                'status' => 'ACCEPTED',
            ];
        }

        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HIU Health Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.nhpr.base_url', 'https://dev.abdm.gov.in');
        $endpoint = rtrim($baseUrl, '/').'/api/hiecm/gateway/v3/health-information/request';
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

        // Construct HIU data push URL (usually /v3/health-information/on-request)
        $dataPushUrl = url('/v3/health-information/on-request');

        $payload = [
            'hiRequest' => [
                'consent' => [
                    'id' => $consentArtefactId,
                ],
                'dateRange' => [
                    'from' => now()->subYear()->toIso8601String(),
                    'to' => now()->toIso8601String(),
                ],
                'dataPushUrl' => $dataPushUrl,
                'keyMaterial' => [
                    'cryptoAlg' => 'ECDH',
                    'curve' => 'Curve25519',
                    'dhPublicKey' => [
                        'expiry' => now()->addHours(2)->toIso8601String(),
                        'parameters' => 'Curve25519/32byte-random-nonce',
                        'keyValue' => $keyMaterial['publicKey'],
                    ],
                    'nonce' => $keyMaterial['nonce'],
                ],
            ],
            'transactionId' => $transactionId,
        ];

        Log::info('HIU Request: Health Information Request', [
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

            Log::info('HIU Response: Health Information Request', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return [
                    'transactionId' => $transactionId,
                    'response' => $body,
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Health information request failed.';
            throw new Exception("ABDM Health Information Request failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HIU Health Service requestHealthInformation error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Notify ABDM of health data received/processed status.
     *
     * Endpoint: POST /v3/health-information/notify
     */
    public function notifyDataReceived(string $transactionId, string $status): array
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        if (! $realApiMode) {
            Log::info('HIU Health Notify: Simulating Data Received Notify', [
                'transactionId' => $transactionId,
                'status' => $status,
            ]);

            return ['status' => 'ACCEPTED'];
        }

        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HIU Health Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.nhpr.base_url', 'https://dev.abdm.gov.in');
        $endpoint = rtrim($baseUrl, '/').'/api/hiecm/gateway/v3/health-information/notify';
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
            'notification' => [
                'transactionId' => $transactionId,
                'status' => $status,
                'decryptionKeyStatus' => 'OK',
            ],
        ];

        Log::info('HIU Request: Health Information Notify', [
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

            Log::info('HIU Response: Health Information Notify', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Health notification failed.';
            throw new Exception("ABDM Health Notify failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HIU Health Service notifyDataReceived error: '.$e->getMessage());
            throw $e;
        }
    }
}
