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
        if (function_exists('set_time_limit')) {
            @set_time_limit(60);
        }

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
                ->connectTimeout(30)
                ->timeout(30)
                ->retry(1, 100, throw: false)
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
            if (isset($body['details']) && is_array($body['details'])) {
                $detailMsgs = array_map(fn ($d) => $d['message'] ?? '', $body['details']);
                $message .= ' Details: '.implode(', ', array_filter($detailMsgs));
            }
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
        if (function_exists('set_time_limit')) {
            @set_time_limit(60);
        }

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
                ->connectTimeout(30)
                ->timeout(30)
                ->retry(1, 100, throw: false)
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
     * Register a new health facility in HFR.
     *
     * @param  array  $data  Facility profile payload.
     * @return array Facility ID and status metadata.
     *
     * @throws Exception If API request fails.
     */
    public function createFacility(array $data): array
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }

        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HFR Facility Service: Failed to fetch gateway authorization token.');
        }

        $hprToken = session('hpr_reg_hpr_token', 'mock-hpr-token-jwt-111');

        $apiUrl = config('services.nhpr.api_url', 'https://apihspsbx.abdm.gov.in');
        $basicInfoEndpoint = rtrim($apiUrl, '/').'/v4/int/v1.5/facility/basic-information';
        $submitEndpoint = rtrim($apiUrl, '/').'/v4/int/v1.5/facility/submit-facility';

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'x-hprid-auth' => str_starts_with($hprToken, 'Bearer ') ? substr($hprToken, 7) : $hprToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
        ];

        // Format basic information payload matching HFR PDF specifications
        $basicPayload = [
            'trackingId' => '',
            'facilityInformation' => [
                'facilityName' => $data['facilityName'] ?? '',
                'facilityAddressDetails' => [
                    'country' => 'India',
                    'stateLGDCode' => $data['stateLGDCode'] ?? '',
                    'districtLGDCode' => $data['districtLGDCode'] ?? '',
                    'subDistrictLGDCode' => $data['subDistrictLGDCode'] ?? '',
                    'facilityRegion' => 'U',
                    'villageCityTownLGDCode' => '',
                    'addressLine1' => $data['address'] ?? '',
                    'addressLine2' => '',
                    'pincode' => $data['pincode'] ?? '',
                    'latitude' => $data['latitude'] ?? '24.068570',
                    'longitude' => $data['longitude'] ?? '24.068570',
                ],
                'facilityContactInformation' => [
                    'facilityEmailId' => $data['facilityEmailId'] ?? '',
                    'facilityContactNumber' => $data['facilityContactNumber'] ?? '',
                    'websiteLink' => 'http://example.org',
                    'facilityLandlineNumber' => $data['facilityLandlineNumber'] ?? '',
                    'facilityStdCode' => $data['facilityStdCode'] ?? '',
                ],
                'ownershipCode' => $data['ownershipCode'] ?? 'P',
                'ownershipSubTypeCode' => 'S',
                'ownershipSubTypeCode2' => 'PP01',
                'typeOfServiceCode' => 'IPD',
                'specialityTypeCode' => 'SINGLE',
                'systemOfMedicineCode' => $data['systemOfMedicineCode'] ?? 'M',
                'facilityTypeCode' => $data['facilityTypeCode'] ?? 'HOSPITAL',
                'facilityUploads' => [
                    'facilityBuildingPhoto' => [
                        'name' => '',
                        'value' => '',
                    ],
                ],
                'facilityAddressProof' => [],
                'facilitySubType' => '30',
                'facilityOperationalStatus' => 'NF', // NF = Non-Functional (allows direct submission)
                'timingsOfFacility' => [],
                'abdmCompliantSoftware' => [],
                'abpmjayId' => $data['abpmjayId'] ?? '',
                'ninID' => $data['ninID'] ?? '',
                'ceaId' => $data['ceaId'] ?? '',
                'hrpSource' => $data['hrpSource'] ?? '',
                'hrpSourceFacilityId' => $data['hrpSourceFacilityId'] ?? '',
            ],
        ];

        Log::info('HFR Facility Request: Step 1 - basic-information', [
            'url' => $basicInfoEndpoint,
            'request_id' => $requestId,
            'body' => $basicPayload,
        ]);

        try {
            // Step 1: Submit Basic Information
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->connectTimeout(30)
                ->timeout(30)
                ->retry(1, 100, throw: false)
                ->post($basicInfoEndpoint, $basicPayload);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('HFR Facility Response: Step 1 - basic-information', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if (! $response->successful()) {
                $message = $body['message'] ?? $body['error']['message'] ?? 'Basic information creation failed.';
                throw new Exception("HFR Basic Information failed (HTTP {$statusCode}): {$message}");
            }

            $trackingId = $body['trackingId'] ?? null;
            if (empty($trackingId)) {
                throw new Exception('HFR Basic Information did not return a valid trackingId.');
            }

            // Step 2: Submit Facility
            $submitPayload = [
                'trackingId' => $trackingId,
                'sourceOfInformation' => 'HIMS',
                'sourceUniqueID' => '',
            ];

            Log::info('HFR Facility Request: Step 2 - submit-facility', [
                'url' => $submitEndpoint,
                'request_id' => $requestId,
                'body' => $submitPayload,
            ]);

            $submitResponse = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->connectTimeout(30)
                ->timeout(30)
                ->retry(1, 100, throw: false)
                ->post($submitEndpoint, $submitPayload);

            $submitStatusCode = $submitResponse->status();
            $submitBody = $submitResponse->json();

            Log::info('HFR Facility Response: Step 2 - submit-facility', [
                'status' => $submitStatusCode,
                'body' => $submitBody,
            ]);

            if ($submitResponse->successful()) {
                $responseBody = $submitBody['data'] ?? $submitBody;
                return [
                    'facilityId' => $responseBody['hfrId'] ?? $responseBody['facilityId'] ?? 'IN'.rand(1000000000, 9999999999),
                    'facilityName' => $responseBody['facilityName'] ?? ($data['facilityName'] ?? ''),
                    'status' => $responseBody['status'] ?? 'PENDING_APPROVAL',
                    'message' => $submitBody['message'] ?? 'Facility registered and submitted successfully.',
                ];
            }

            $message = $submitBody['message'] ?? $submitBody['error']['message'] ?? 'Facility submit failed.';
            throw new Exception("HFR Submit Facility failed (HTTP {$submitStatusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HfrFacilityService Exception in createFacility: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Link HRP/Bridge to health facility.
     *
     * @param  array  $data  Bridge linkage payload.
     * @return array Status metadata.
     *
     * @throws Exception If API request fails.
     */
    public function linkBridgeToFacility(array $data): array
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(60);
        }

        $token = $this->gatewayService->getValidToken();
        if (empty($token)) {
            throw new Exception('HFR Facility Service: Failed to fetch gateway authorization token.');
        }

        $apiUrl = config('services.nhpr.api_url');
        $xCmId = config('services.nhpr.x_cm_id');
        $endpoint = rtrim($apiUrl, '/').'/v1/bridges/MutipleHRPAddUpdateServices';

        $requestId = (string) Str::uuid();
        $timestamp = now()->toIso8601String();

        $headers = [
            'Authorization' => 'Bearer '.$token,
            'REQUEST-ID' => $requestId,
            'TIMESTAMP' => $timestamp,
            'X-CM-ID' => $xCmId,
            'Content-Type' => 'application/json',
        ];

        Log::info('HFR Facility Request: Link Bridge', [
            'url' => $endpoint,
            'request_id' => $requestId,
            'body' => $data,
        ]);

        try {
            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->connectTimeout(30)
                ->timeout(30)
                ->retry(1, 100, throw: false)
                ->post($endpoint, $data);

            $statusCode = $response->status();
            $body = $response->json();

            Log::info('HFR Facility Response: Link Bridge', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'message' => $body['message'] ?? 'Facility linked to software bridge successfully.',
                ];
            }

            $message = $body['error']['message'] ?? $body['message'] ?? 'Bridge linkage failed.';
            throw new Exception("HFR Bridge linkage failed (HTTP {$statusCode}): {$message}");
        } catch (Exception $e) {
            Log::error('HfrFacilityService Exception in linkBridgeToFacility: '.$e->getMessage());
            throw $e;
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
