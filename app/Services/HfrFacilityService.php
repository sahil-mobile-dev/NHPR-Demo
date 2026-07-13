<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class HfrFacilityService
 *
 * Manages healthcare facility search (HFR) and final professional practitioner profile registrations.
 */
class HfrFacilityService
{
    protected GatewayTokenService $gatewayService;

    /**
     * HfrFacilityService constructor.
     */
    public function __construct(GatewayTokenService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * Search for registered healthcare facilities in HFR registry.
     *
     * @param  array  $searchParams  Search parameters (state, district, pincode, facilityName, etc.)
     * @return array List of facilities and count metadata.
     *
     * @throws Exception If API request fails.
     */
    public function searchFacility(array $searchParams): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HFR Facility Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/FacilityManagement/v1.5/facility/search';

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        // Default pagination limits if empty
        $payload = array_merge([
            'ownershipCode' => '',
            'subDistrictLGDCode' => '',
            'pincode' => '',
            'facilityName' => '',
            'facilityId' => '',
            'page' => 1,
            'resultsPerPage' => 10,
            'stateLGDCode' => '',
            'districtLGDCode' => '',
        ], array_filter($searchParams, fn ($val) => $val !== null && $val !== ''));

        Log::info('HFR Facility Request: Search Facilities', [
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

            Log::info('HFR Facility Response: Search Facilities', [
                'status' => $statusCode,
                'body' => [
                    'totalFacilities' => $body['totalFacilities'] ?? null,
                    'numberOfPages' => $body['numberOfPages'] ?? null,
                    'facilitiesCount' => isset($body['facilities']) ? count($body['facilities']) : 0,
                ],
            ]);

            if ($response->successful()) {
                return [
                    'facilities' => $body['facilities'] ?? [],
                    'totalFacilities' => $body['totalFacilities'] ?? 0,
                    'numberOfPages' => $body['numberOfPages'] ?? 1,
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Facility search request error.';
            throw new Exception("HFR Facility search failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HFR Facility Service Exception in searchFacility: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Submit professional registration details mapping to HFR and medical council profiles.
     *
     * @param  array  $data  Registration profile payload.
     * @return array Reference number, HPR ID, and status message.
     *
     * @throws Exception If API request fails.
     */
    public function registerProfessional(array $data): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HFR Facility Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/apis/v1/doctors/register-professional-new';

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        // Mask credentials in payload logging
        $maskedPayload = $data;
        if (isset($maskedPayload['hprToken'])) {
            $maskedPayload['hprToken'] = substr($maskedPayload['hprToken'], 0, 10).'...[PROTECTED_HPR_TOKEN]...'.substr($maskedPayload['hprToken'], -10);
        }
        if (isset($maskedPayload['practitioner']['profilePhoto'])) {
            $maskedPayload['practitioner']['profilePhoto'] = '[IMAGE_BASE64_MASKED]';
        }
        if (isset($maskedPayload['practitioner']['registrationAcademic']['registrationData'])) {
            foreach ($maskedPayload['practitioner']['registrationAcademic']['registrationData'] as &$reg) {
                if (isset($reg['registrationCertificate']['data'])) {
                    $reg['registrationCertificate']['data'] = '[FILE_BASE64_MASKED]';
                }
                if (isset($reg['qualifications'])) {
                    foreach ($reg['qualifications'] as &$qual) {
                        if (isset($qual['degreeCertificate']['data'])) {
                            $qual['degreeCertificate']['data'] = '[FILE_BASE64_MASKED]';
                        }
                    }
                }
            }
        }
        if (isset($maskedPayload['practitioner']['currentWorkDetails']['certificateAttachment'])) {
            $maskedPayload['practitioner']['currentWorkDetails']['certificateAttachment'] = '[FILE_BASE64_MASKED]';
        }

        Log::info('HFR Facility Request: Register Professional', [
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

            Log::info('HFR Facility Response: Register Professional', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                $responseBody = $body['body'] ?? $body;

                return [
                    'referenceNumber' => $responseBody['referenceNumber'] ?? null,
                    'status' => $responseBody['status'] ?? 'false',
                    'message' => $responseBody['message'] ?? 'Successfully registered professional.',
                    'hprId' => $responseBody['hprId'] ?? null,
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Professional registration failed.';
            throw new Exception("HPR Professional registration failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HfrFacilityService Exception in registerProfessional: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all Central government ministries list.
     *
     * @return array List of ministries.
     */
    public function getAllMinistries(): array
    {
        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            Log::error('HFR Facility Service: Failed to fetch gateway token for ministries.');

            return $this->getStaticMinistries();
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v4/int/apis/v1/masters/getAllMinistry';

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->get($endpoint);

            if ($response->successful()) {
                return $response->json() ?: $this->getStaticMinistries();
            }

            Log::warning('HFR Facility Service: live ministries endpoint failed, using static fallback.');

            return $this->getStaticMinistries();
        } catch (Exception $e) {
            Log::error('HFR Facility Service Exception in getAllMinistries: '.$e->getMessage());

            return $this->getStaticMinistries();
        }
    }

    /**
     * Static fallback list of central ministries.
     */
    protected function getStaticMinistries(): array
    {
        return [
            ['code' => 'MOHFW', 'name' => 'Ministry of Health and Family Welfare'],
            ['code' => 'MOR', 'name' => 'Ministry of Railways (MoR)'],
            ['code' => 'MOD', 'name' => 'Ministry of Defence'],
            ['code' => 'MHA', 'name' => 'Ministry of Home Affairs'],
            ['code' => 'AYUSH', 'name' => 'Ministry of Ayush'],
            ['code' => 'MHRD', 'name' => 'Ministry of Education'],
            ['code' => 'OTH', 'name' => 'Other Central Ministry / Departments'],
        ];
    }
}
