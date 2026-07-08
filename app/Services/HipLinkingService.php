<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class HipLinkingService
 *
 * Handles outward APIs called by the HIP to ABDM gateway under Milestone 2 (V3).
 */
class HipLinkingService
{
    protected GatewayTokenService $gatewayService;

    /**
     * HipLinkingService constructor.
     */
    public function __construct(GatewayTokenService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * Link Care Contexts to patient's ABHA account.
     *
     * Endpoint: POST /v3/hip/link/care-contexts
     *
     * @param  string  $userToken  Linking token (X-token) obtained from verification step.
     * @param  string  $patientAbhaAddress  Patient's ABHA Address.
     * @param  array  $careContexts  List of contexts to link (array of ['referenceNumber' => '...', 'display' => '...']).
     * @return array Response payload.
     *
     * @throws Exception If API call fails.
     */
    public function linkCareContext(string $userToken, string $patientAbhaAddress, array $careContexts): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HIP Linking Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.nhpr.base_url', 'https://dev.abdm.gov.in');
        $endpoint = rtrim($baseUrl, '/').'/api/hiecm/hip/v3/link/carecontext';
        $xCmId = config('services.nhpr.x_cm_id', 'sbx');

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'X-token' => 'Bearer '.$userToken,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'patient' => [
                'referenceNumber' => $patientAbhaAddress,
                'display' => $patientAbhaAddress,
                'careContexts' => $careContexts,
            ],
        ];

        Log::info('HIP Request: Link Care Contexts', [
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

            Log::info('HIP Response: Link Care Contexts', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Care context linking failed.';
            throw new Exception("ABDM Link Care Context failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HIP Linking Service linkCareContext error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Notify ABDM of health information transfer completion.
     *
     * Endpoint: POST /v3/hip/health-information/notify
     *
     * @param  string  $transactionId  ABDM transfer transaction ID.
     * @param  string  $status  Transfer status (DELIVERED or FAILED).
     * @return array Response payload.
     *
     * @throws Exception If API call fails.
     */
    public function notifyHealthInformationTransfer(string $transactionId, string $status): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HIP Linking Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.abha.base_url', 'https://abhasbx.abdm.gov.in/abha/api');
        $endpoint = rtrim($baseUrl, '/').'/v3/hip/health-information/notify';
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

        Log::info('HIP Request: Health Information Notify', [
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

            Log::info('HIP Response: Health Information Notify', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Health information notification failed.';
            throw new Exception("ABDM Health Information Notify failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HIP Linking Service notifyHealthInformationTransfer error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Send discovery response back to ABDM Gateway.
     *
     * Endpoint: POST /v3/hip/on-discover
     *
     * @param  string  $txnId  Transaction ID from discovery.
     * @param  array  $patientMatches  Matched patient care context details.
     * @return array Response payload.
     *
     * @throws Exception If API call fails.
     */
    public function onDiscoverResponse(string $txnId, array $patientMatches): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HIP Linking Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.abha.base_url', 'https://abhasbx.abdm.gov.in/abha/api');
        $endpoint = rtrim($baseUrl, '/').'/v3/hip/on-discover';
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
            'transactionId' => $txnId,
            'patient' => $patientMatches,
        ];

        Log::info('HIP Request: On Discover Response', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => $payload,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("ABDM On Discover Response failed (HTTP {$response->status()})");
        } catch (Exception $e) {
            Log::error('HIP Linking Service onDiscoverResponse error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Notify patient via SMS (Optional).
     *
     * Endpoint: POST /v3/hip/link/notify-sms
     *
     * @param  string  $mobile  10-digit mobile number.
     * @param  string  $message  SMS message text.
     * @return array Response payload.
     *
     * @throws Exception If API call fails.
     */
    public function notifyPatientSms(string $mobile, string $message): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HIP Linking Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.abha.base_url', 'https://abhasbx.abdm.gov.in/abha/api');
        $endpoint = rtrim($baseUrl, '/').'/v3/hip/link/notify-sms';
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
            'mobileNo' => $mobile,
            'message' => $message,
        ];

        Log::info('HIP Request: Notify Patient SMS', [
            'url' => $endpoint,
            'request_id' => $requestId,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("ABDM Notify SMS failed (HTTP {$response->status()})");
        } catch (Exception $e) {
            Log::error('HIP Linking Service notifyPatientSms error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate Link Token.
     */
    public function generateLinkToken(array $patientDetails): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HIP Linking Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.nhpr.base_url', 'https://dev.abdm.gov.in');
        $endpoint = rtrim($baseUrl, '/').'/api/hiecm/v3/token/generate-token';
        $xCmId = config('services.nhpr.x_cm_id', 'sbx');
        $xHipId = session('nhpr_credential_client_id', config('services.nhpr.client_id', '100001'));

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-HIP-ID' => $xHipId,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        Log::info('HIP Request: Generate Link Token', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => $patientDetails,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->post($endpoint, $patientDetails);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("ABDM Generate Link Token failed (HTTP {$response->status()})");
        } catch (Exception $e) {
            Log::error('HIP Linking Service generateLinkToken error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Notify Care Context Update.
     */
    public function notifyCareContextUpdate(array $notification): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HIP Linking Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.nhpr.base_url', 'https://dev.abdm.gov.in');
        $endpoint = rtrim($baseUrl, '/').'/api/hiecm/hip/v3/link/context/notify';
        $xCmId = config('services.nhpr.x_cm_id', 'sbx');
        $xHipId = session('nhpr_credential_client_id', config('services.nhpr.client_id', '100001'));

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-HIP-ID' => $xHipId,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        Log::info('HIP Request: Notify Care Context Update', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => $notification,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->post($endpoint, $notification);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("ABDM Notify Care Context Update failed (HTTP {$response->status()})");
        } catch (Exception $e) {
            Log::error('HIP Linking Service notifyCareContextUpdate error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * SMS Notify (v2.8 endpoint).
     */
    public function smsNotify(string $mobile, string $message): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HIP Linking Service: Failed to retrieve gateway authorization token.');
        }

        $baseUrl = config('services.nhpr.base_url', 'https://dev.abdm.gov.in');
        $endpoint = rtrim($baseUrl, '/').'/api/hiecm/hip/v3/link/patient/links/sms/notify2';
        $xCmId = config('services.nhpr.x_cm_id', 'sbx');
        $xHipId = session('nhpr_credential_client_id', config('services.nhpr.client_id', '100001'));

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-HIP-ID' => $xHipId,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'phoneNo' => $mobile,
            'message' => $message,
        ];

        Log::info('HIP Request: SMS Notify', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => $payload,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception("ABDM SMS Notify failed (HTTP {$response->status()})");
        } catch (Exception $e) {
            Log::error('HIP Linking Service smsNotify error: '.$e->getMessage());
            throw $e;
        }
    }
}
