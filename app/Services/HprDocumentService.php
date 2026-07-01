<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class HprDocumentService
 *
 * Manages document checklist retrieval and Base64 uploads to the ABDM HPR Registry.
 */
class HprDocumentService
{
    protected GatewayTokenService $gatewayService;

    /**
     * HprDocumentService constructor.
     */
    public function __construct(GatewayTokenService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * Retrieve the required documents block details list for a registered professional.
     *
     * @param  string  $hprId  The professional's HPR ID (e.g. 71-3563-6824-xxxx).
     * @return array Document block IDs list (profilePhoto, degreeCertificate, registrationCertificate, etc.).
     *
     * @throws Exception If API request fails.
     */
    public function fetchRequiredDocuments(string $hprId): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HPR Document Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/apis/v1/doctors/fetch-documents-list';

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
            'hprid' => $hprId,
        ];

        Log::info('HPR Document Request: Fetch Required Documents list', [
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

            Log::info('HPR Document Response: Fetch Required Documents list', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return $body['documentList'] ?? $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Fetch documents list error.';
            throw new Exception("HPR Fetch documents failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HPR Document Service Exception in fetchRequiredDocuments: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Upload Base64-encoded files mapped to document block IDs.
     *
     * @param  string  $hprToken  HPR Login Token JWT (from Step 7).
     * @param  array  $documents  List of documents to upload. Each contains: document_id, document_type, fileType, data.
     * @return array Upload results for each document type.
     *
     * @throws Exception If API request fails.
     */
    public function uploadDocuments(string $hprToken, array $documents): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HPR Document Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/apis/v1/uploads/upload-document';

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'hpr_token' => $hprToken,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'hpr_token' => $hprToken,
            'document' => $documents,
        ];

        // Mask payloads for secure logging
        $maskedPayload = $payload;
        $maskedPayload['hpr_token'] = substr($maskedPayload['hpr_token'], 0, 10).'...[PROTECTED_HPR_TOKEN]...'.substr($maskedPayload['hpr_token'], -10);
        if (isset($maskedPayload['document'])) {
            foreach ($maskedPayload['document'] as &$doc) {
                if (isset($doc['data'])) {
                    $doc['data'] = '[FILE_BASE64_MASKED]';
                }
            }
        }

        Log::info('HPR Document Request: Upload Documents', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => $maskedPayload,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('HPR Document Response: Upload Documents', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return $body;
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Document upload API error.';
            throw new Exception("HPR Document upload failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HPR Document Service Exception in uploadDocuments: '.$e->getMessage());
            throw $e;
        }
    }
}
