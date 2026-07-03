<?php

namespace App\Http\Controllers;

use App\Helpers\AbdmCryptEngine;
use App\Jobs\FetchConsentArtefactJob;
use App\Jobs\ProcessHealthDataJob;
use App\Models\DiagnosticReport;
use App\Models\Encounter;
use App\Models\HealthDocument;
use App\Models\HiuConsentArtefact;
use App\Models\HiuConsentRequest;
use App\Models\HiuTransaction;
use App\Models\Observation;
use App\Models\Prescription;
use App\Services\FhirParserService;
use App\Services\FideliusService;
use App\Services\HiuAuditService;
use App\Services\HiuConsentService;
use App\Services\HiuHealthInformationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Class HiuController
 *
 * Coordinates HIU views, requests, callbacks, decryption, parsing, and simulation triggers.
 */
class HiuController extends Controller
{
    protected HiuConsentService $consentService;

    protected HiuHealthInformationService $healthService;

    protected FideliusService $fideliusService;

    protected FhirParserService $fhirParserService;

    protected HiuAuditService $auditService;

    public function __construct(
        HiuConsentService $consentService,
        HiuHealthInformationService $healthService,
        FideliusService $fideliusService,
        FhirParserService $fhirParserService,
        HiuAuditService $auditService
    ) {
        $this->consentService = $consentService;
        $this->healthService = $healthService;
        $this->fideliusService = $fideliusService;
        $this->fhirParserService = $fhirParserService;
        $this->auditService = $auditService;
    }

    /**
     * Display the HIU Dashboard.
     */
    public function showDashboard(): View
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $requests = HiuConsentRequest::orderBy('created_at', 'desc')->get();
        $artefacts = HiuConsentArtefact::orderBy('created_at', 'desc')->get();

        $stats = [
            'total' => $requests->count(),
            'active' => $artefacts->where('status', 'GRANTED')->count(),
            'revoked' => $artefacts->where('status', 'REVOKED')->count(),
            'expired' => $artefacts->where('status', 'EXPIRED')->count(),
        ];

        return view('hiu.dashboard', compact('requests', 'artefacts', 'stats', 'realApiMode'));
    }

    /**
     * Submit a consent request to ABDM Gateway.
     */
    public function requestConsent(Request $request): JsonResponse
    {
        $request->validate([
            'patient_abha_address' => 'required|string',
            'purpose' => 'required|string',
            'hi_types' => 'required|array',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'expiry' => 'required|date',
        ]);

        $patient = trim($request->input('patient_abha_address'));
        $purpose = $request->input('purpose');
        $hiTypes = $request->input('hi_types');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $expiry = $request->input('expiry');

        // Local tracking record
        $localRequest = HiuConsentRequest::create([
            'patient_abha_address' => $patient,
            'status' => 'REQUESTED',
            'purpose' => $purpose,
            'hi_types' => $hiTypes,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'expiry' => $expiry,
        ]);

        $purposeCode = 'REFERRAL';
        $purposeMap = [
            'General Consultation' => 'REFERRAL',
            'Referral Clinic Consultation' => 'REFERRAL',
            'Emergency Treatment Care' => 'BTG',
            'Chronic Condition Monitoring' => 'CAREMGT',
        ];
        if (isset($purposeMap[$purpose])) {
            $purposeCode = $purposeMap[$purpose];
        } elseif (in_array(strtoupper($purpose), ['REFERRAL', 'BTG', 'CAREMGT', 'PUBLICHEALTH', 'SELFVIEW'])) {
            $purposeCode = strtoupper($purpose);
        }

        $consentData = [
            'purpose' => [
                'code' => $purposeCode,
                'text' => $purpose,
            ],
            'patient' => [
                'id' => $patient,
            ],
            'hiu' => [
                'id' => session('nhpr_credential_client_id', config('services.nhpr.client_id', '100001')),
            ],
            'requester' => [
                'name' => 'Dr. Uttarakhand HIMS',
                'identifier' => [
                    'type' => 'REGNO',
                    'value' => 'UK-HIMS-99',
                    'system' => 'https://healthid.ndhm.gov.in',
                ],
            ],
            'hiTypes' => $hiTypes,
            'permission' => [
                'accessMode' => 'VIEW',
                'dateRange' => [
                    'from' => now()->parse($dateFrom)->toIso8601String(),
                    'to' => now()->parse($dateTo)->toIso8601String(),
                ],
                'dataEraseAt' => now()->parse($expiry)->addYears(2)->toIso8601String(),
                'frequency' => [
                    'unit' => 'HOUR',
                    'value' => 1,
                    'repeats' => 0,
                ],
            ],
        ];

        try {
            $response = $this->consentService->createConsentRequest($consentData);

            $consentReqId = $response['consentRequestId'] ?? $response['requestId'] ?? 'REQ-'.rand(100000, 999999);

            $localRequest->update(['consent_request_id' => $consentReqId]);

            $this->auditService->logConsent(
                'REQUEST',
                $consentReqId,
                null,
                $patient,
                ['payload' => $consentData, 'gateway_response' => $response]
            );

            return response()->json([
                'success' => true,
                'consent_request_id' => $consentReqId,
                'message' => 'Consent request successfully sent to ABDM Gateway!',
            ]);
        } catch (Exception $e) {
            $localRequest->update(['status' => 'FAILED']);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create consent request: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch Consent Artefact from Gateway.
     */
    public function fetchArtefact(Request $request, $id): JsonResponse
    {
        try {
            $response = $this->consentService->fetchConsentArtefact($id);

            // Save artefact details locally
            $artefact = HiuConsentArtefact::updateOrCreate(
                ['consent_id' => $id],
                [
                    'consent_request_id' => $response['consentDetail']['consentRequestId'] ?? 'REQ-UNKNOWN',
                    'status' => 'GRANTED',
                    'patient_abha_address' => $response['consentDetail']['patient']['id'] ?? '',
                    'consent_detail' => $response,
                ]
            );

            // Update associated request status
            HiuConsentRequest::where('consent_request_id', $artefact->consent_request_id)
                ->update(['status' => 'GRANTED']);

            $this->auditService->logConsent(
                'FETCH_ARTEFACT',
                $artefact->consent_request_id,
                $id,
                $artefact->patient_abha_address,
                $response
            );

            return response()->json([
                'success' => true,
                'message' => 'Consent artefact fetched successfully!',
                'artefact' => $artefact,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch consent artefact: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request Health Information from HIP.
     */
    public function requestHealthData(Request $request): JsonResponse
    {
        $request->validate([
            'consent_id' => 'required|string',
        ]);

        $consentId = $request->input('consent_id');
        $artefact = HiuConsentArtefact::where('consent_id', $consentId)->firstOrFail();

        try {
            // Generate HIU keys
            $keys = $this->fideliusService->generateKeys();

            // Request health information
            $response = $this->healthService->requestHealthInformation($consentId, $consentId, $keys);
            $txnId = $response['transactionId'];

            // Store transaction details locally
            HiuTransaction::create([
                'transaction_id' => $txnId,
                'consent_id' => $consentId,
                'status' => 'REQUESTED',
                'private_key' => $keys['privateKey'],
                'public_key' => $keys['publicKey'],
                'nonce' => $keys['nonce'],
            ]);

            $this->auditService->logRequest('OUTGOING_HI_REQUEST', $txnId, $consentId, $response);

            return response()->json([
                'success' => true,
                'transaction_id' => $txnId,
                'message' => 'Health information request successfully sent to HIP!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to request health data: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Local Revocation: Mark consent as REVOKED and wipe local clinical records.
     */
    public function revokeConsentLocal($consentId): JsonResponse
    {
        try {
            $artefact = HiuConsentArtefact::where('consent_id', $consentId)->firstOrFail();
            $artefact->update(['status' => 'REVOKED']);

            $localRequest = HiuConsentRequest::where('consent_request_id', $artefact->consent_request_id)->first();
            if ($localRequest) {
                $localRequest->update(['status' => 'REVOKED']);
            }

            $patientAddress = $artefact->patient_abha_address;

            Prescription::where('patient_abha_address', $patientAddress)->delete();
            DiagnosticReport::where('patient_abha_address', $patientAddress)->delete();
            Observation::where('patient_abha_address', $patientAddress)->delete();
            Encounter::where('patient_abha_address', $patientAddress)->delete();
            HealthDocument::where('patient_abha_address', $patientAddress)->delete();

            $this->auditService->logConsent('LOCAL_REVOKE', $artefact->consent_request_id, $consentId, $patientAddress, ['action' => 'local_revoke_wipe']);

            return response()->json([
                'success' => true,
                'message' => 'Consent policy revoked locally and decrypted clinical files completely wiped.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke locally: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display Decrypted Health Records for Patient.
     */
    public function showRecords($abhaAddress): View
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $abhaAddress = trim($abhaAddress);

        $prescriptions = Prescription::where('patient_abha_address', $abhaAddress)->orderBy('prescription_date', 'desc')->get();
        $reports = DiagnosticReport::where('patient_abha_address', $abhaAddress)->orderBy('report_date', 'desc')->get();
        $observations = Observation::where('patient_abha_address', $abhaAddress)->orderBy('observation_date', 'desc')->get();
        $encounters = Encounter::where('patient_abha_address', $abhaAddress)->orderBy('encounter_date', 'desc')->get();
        $documents = HealthDocument::where('patient_abha_address', $abhaAddress)->orderBy('document_date', 'desc')->get();

        // Extract linked facilities
        $facilities = collect()
            ->merge($prescriptions->pluck('facility_name'))
            ->merge($reports->pluck('facility_name'))
            ->merge($observations->pluck('facility_name'))
            ->merge($encounters->pluck('facility_name'))
            ->merge($documents->pluck('facility_name'))
            ->filter()
            ->unique();

        $timeline = collect();

        foreach ($prescriptions as $p) {
            $timeline->push([
                'type' => 'PRESCRIPTION',
                'date' => $p->prescription_date,
                'title' => 'OPD Prescription - '.count($p->medications).' Medication(s)',
                'doctor' => $p->doctor_name,
                'facility' => $p->facility_name,
                'details' => $p,
            ]);
        }

        foreach ($reports as $r) {
            $timeline->push([
                'type' => 'DIAGNOSTIC_REPORT',
                'date' => $r->report_date,
                'title' => 'Lab Report: '.$r->test_name,
                'doctor' => $r->doctor_name,
                'facility' => $r->facility_name,
                'details' => $r,
            ]);
        }

        foreach ($encounters as $e) {
            $timeline->push([
                'type' => 'ENCOUNTER',
                'date' => $e->encounter_date,
                'title' => 'Visit Encounter: '.$e->encounter_type,
                'doctor' => $e->doctor_name,
                'facility' => $e->facility_name,
                'details' => $e,
            ]);
        }

        foreach ($documents as $d) {
            $timeline->push([
                'type' => 'DOCUMENT',
                'date' => $d->document_date,
                'title' => $d->title.' ('.$d->document_type.')',
                'doctor' => $d->author_name,
                'facility' => $d->facility_name,
                'details' => $d,
            ]);
        }

        $timeline = $timeline->sortByDesc('date');

        // Audit page view
        $this->auditService->logRecordProcessing($abhaAddress, 'VIEW', 'Doctor viewed patient clinical records.', 'SUCCESS');

        return view('hiu.records', compact('prescriptions', 'reports', 'observations', 'encounters', 'documents', 'facilities', 'timeline', 'abhaAddress', 'realApiMode'));
    }

    /**
     * ABDM Callback: Consent Init
     * POST /v3/consent/on-init
     */
    public function apiConsentOnInit(Request $request): JsonResponse
    {
        Log::info('ABDM Callback: Consent On-Init (HIU)', $request->all());

        $requestId = $request->input('resp.requestId');
        $consentReqId = $request->input('consentRequest.id');

        if ($requestId && $consentReqId) {
            $localRequest = HiuConsentRequest::where('consent_request_id', $requestId)->first();
            if ($localRequest) {
                $localRequest->update([
                    'consent_request_id' => $consentReqId,
                    'status' => 'INITIATED',
                ]);
            }
            $this->auditService->logConsent('CALLBACK_INIT', $consentReqId, null, $localRequest?->patient_abha_address, $request->all());
        }

        return response()->json(['status' => 'ACCEPTED'], 202);
    }

    /**
     * ABDM Callback: Consent Notify
     * POST /v3/consent/notify
     */
    public function apiConsentNotify(Request $request): JsonResponse
    {
        Log::info('ABDM Callback: Consent Notify (HIU)', $request->all());

        $notification = $request->input('notification');
        if ($notification) {
            $consentReqId = $notification['consentRequestId'] ?? 'UNKNOWN';
            $status = $notification['status'] ?? 'GRANTED';

            $localRequest = HiuConsentRequest::where('consent_request_id', $consentReqId)->first();

            if ($localRequest) {
                $localRequest->update(['status' => $status]);
            }

            $patientAddress = $localRequest?->patient_abha_address ?? '';

            // Handle artefacts details
            $artefacts = $notification['consentArtefacts'] ?? [];
            foreach ($artefacts as $art) {
                $consentId = $art['id'];

                if ($status === 'GRANTED') {
                    // Dispatch background job to fetch consent artefact from Gateway!
                    FetchConsentArtefactJob::dispatch(
                        $consentId,
                        $consentReqId,
                        $patientAddress,
                        $notification
                    );
                } else {
                    // Revoked or Expired
                    HiuConsentArtefact::where('consent_id', $consentId)->update(['status' => $status]);
                }

                $this->auditService->logConsent('CALLBACK_NOTIFY', $consentReqId, $consentId, $patientAddress, $request->all());
            }
        }

        return response()->json(['status' => 'ACCEPTED'], 202);
    }

    /**
     * Callback: Receive Encrypted Health Data (HIP pushes data here!)
     * POST /v3/health-information/on-request
     */
    public function apiReceiveHealthData(Request $request): JsonResponse
    {
        Log::info('ABDM Callback: Receive Health Data (HIU dataPushUrl)', [
            'transactionId' => $request->input('transactionId'),
            'pageNumber' => $request->input('pageNumber'),
        ]);

        $txnId = $request->input('transactionId');
        $entries = $request->input('entries', []);
        $keyMaterial = $request->input('keyMaterial');

        if (empty($txnId) || empty($entries) || empty($keyMaterial)) {
            Log::error('Receive Health Data: Missing required parameters', $request->all());

            return response()->json(['error' => 'Invalid parameters'], 400);
        }

        $transaction = HiuTransaction::where('transaction_id', $txnId)->first();
        if (! $transaction) {
            Log::error('Receive Health Data: Local transaction not found', ['transactionId' => $txnId]);

            return response()->json(['error' => 'Transaction not found'], 404);
        }

        $consent = HiuConsentArtefact::where('consent_id', $transaction->consent_id)->first();
        $patientAddress = $consent?->patient_abha_address ?? 'unknown';

        $this->auditService->logRequest('INCOMING_DATA_PUSH', $txnId, $transaction->consent_id, $request->all());

        try {
            // Dispatch background job to process the pushed health information
            ProcessHealthDataJob::dispatch($txnId, $entries, $keyMaterial);

            return response()->json(['status' => 'ACCEPTED'], 202);
        } catch (Exception $e) {
            Log::error('ProcessHealthDataJob dispatch failed: '.$e->getMessage());

            $transaction->update(['status' => 'FAILED']);
            $this->healthService->notifyDataReceived($txnId, 'FAILED');

            $this->auditService->logRecordProcessing($patientAddress, 'DECRYPT', 'Decryption dispatch failed: '.$e->getMessage(), 'FAILED');
            $this->auditService->logTransaction($txnId, $transaction->consent_id, 'HIU_REQUEST', 'FAILED', 0, $e->getMessage());

            return response()->json(['error' => 'Data processing dispatch failed: '.$e->getMessage()], 500);
        }
    }

    /**
     * Simulator: Approve Consent Request
     */
    public function simulateApproveConsent(Request $request): JsonResponse
    {
        $request->validate([
            'consent_request_id' => 'required|string',
        ]);

        $consentReqId = $request->input('consent_request_id');
        $localRequest = HiuConsentRequest::where('consent_request_id', $consentReqId)->firstOrFail();

        $consentId = 'CON-'.rand(100000, 999999);
        $patientAddress = $localRequest->patient_abha_address;

        $notifyPayload = [
            'requestId' => (string) Str::uuid(),
            'timestamp' => now()->toIso8601String(),
            'notification' => [
                'consentRequestId' => $consentReqId,
                'status' => 'GRANTED',
                'consentArtefacts' => [
                    [
                        'id' => $consentId,
                    ],
                ],
            ],
        ];

        // Call the notify endpoint locally (internal dispatch)
        $subRequest = Request::create('/v3/consent/notify', 'POST', $notifyPayload);
        $subResponse = app()->handle($subRequest);

        if ($subResponse->isSuccessful()) {
            return response()->json([
                'success' => true,
                'consent_id' => $consentId,
                'message' => 'Simulated patient approval notification dispatched successfully!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to dispatch simulated approval notification.',
        ], 500);
    }

    /**
     * Simulator: Deny/Reject Consent Request
     */
    public function simulateDenyConsent(Request $request): JsonResponse
    {
        $request->validate([
            'consent_request_id' => 'required|string',
        ]);

        $consentReqId = $request->input('consent_request_id');
        $localRequest = HiuConsentRequest::where('consent_request_id', $consentReqId)->firstOrFail();

        $notifyPayload = [
            'requestId' => (string) Str::uuid(),
            'timestamp' => now()->toIso8601String(),
            'notification' => [
                'consentRequestId' => $consentReqId,
                'status' => 'DENIED',
                'consentArtefacts' => [],
            ],
        ];

        $subRequest = Request::create('/v3/consent/notify', 'POST', $notifyPayload);
        $subResponse = app()->handle($subRequest);

        if ($subResponse->isSuccessful()) {
            return response()->json([
                'success' => true,
                'message' => 'Simulated patient rejection notification dispatched successfully!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to dispatch simulated rejection notification.',
        ], 500);
    }

    /**
     * Simulator: Revoke Consent Policy
     */
    public function simulateRevokeConsent(Request $request): JsonResponse
    {
        $request->validate([
            'consent_id' => 'required|string',
        ]);

        $consentId = $request->input('consent_id');
        $artefact = HiuConsentArtefact::where('consent_id', $consentId)->firstOrFail();

        $notifyPayload = [
            'requestId' => (string) Str::uuid(),
            'timestamp' => now()->toIso8601String(),
            'notification' => [
                'consentRequestId' => $artefact->consent_request_id,
                'status' => 'REVOKED',
                'consentArtefacts' => [
                    [
                        'id' => $consentId,
                    ],
                ],
            ],
        ];

        $subRequest = Request::create('/v3/consent/notify', 'POST', $notifyPayload);
        $subResponse = app()->handle($subRequest);

        if ($subResponse->isSuccessful()) {
            return response()->json([
                'success' => true,
                'message' => 'Simulated patient revocation notification dispatched successfully!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to dispatch simulated revocation notification.',
        ], 500);
    }

    /**
     * Simulator: Push encrypted clinical data
     */
    public function simulatePushHealthData(Request $request): JsonResponse
    {
        $request->validate([
            'transaction_id' => 'required|string',
        ]);

        $txnId = $request->input('transaction_id');
        $transaction = HiuTransaction::where('transaction_id', $txnId)->firstOrFail();
        $consent = HiuConsentArtefact::where('consent_id', $transaction->consent_id)->firstOrFail();

        // 1. Generate mockup clinical data (FHIR Bundle)
        $fhirBundle = $this->createMockFHIRBundle($consent->patient_abha_address);

        try {
            // 2. Generate simulated HIP Keys
            $hipKeys = AbdmCryptEngine::generateKeypair();

            // 3. Encrypt data from HIP to HIU (using HIU's public key)
            $hipSharedSecret = AbdmCryptEngine::deriveSharedSecret($transaction->public_key, $hipKeys['privateKey']);
            $encrypted = AbdmCryptEngine::encryptPayload(json_encode($fhirBundle), $hipSharedSecret);

            // 4. Create HIP push payload
            $pushPayload = [
                'pageNumber' => 1,
                'pageCount' => 1,
                'transactionId' => $txnId,
                'entries' => [
                    [
                        'content' => $encrypted['ciphertext'],
                        'media' => 'application/fhir+json',
                        'checksum' => md5($encrypted['ciphertext']),
                    ],
                ],
                'keyMaterial' => [
                    'cryptoAlg' => 'ECDH',
                    'curve' => 'Curve25519',
                    'dhPublicKey' => [
                        'keyValue' => $hipKeys['publicKey'],
                    ],
                    'nonce' => $encrypted['iv'], // Send the IV as the nonce
                ],
            ];

            // Trigger data push locally
            // We pass the encryption tag as the nonce/tag variable in decryption
            $pushPayload['keyMaterial']['nonce'] = $encrypted['tag']; // Use tag as sender nonce/tag
            $pushPayload['keyMaterial']['dhPublicKey']['parameters'] = $encrypted['iv']; // Carry IV here

            // Call the data push endpoint locally (internal dispatch)
            $subRequest = Request::create('/v3/health-information/on-request', 'POST', $pushPayload);
            $subResponse = app()->handle($subRequest);

            if ($subResponse->isSuccessful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Simulated encrypted health data pushed and decrypted successfully!',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'HIP pushed data, but HIU processing failed: '.$subResponse->getContent(),
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Simulation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Compile a mock FHIR Document Bundle for Simulation.
     */
    protected function createMockFHIRBundle(string $patientAddress): array
    {
        $now = now()->toIso8601String();
        $patientId = (string) Str::uuid();
        $practitionerId = (string) Str::uuid();
        $compositionId = (string) Str::uuid();
        $encounterId = (string) Str::uuid();
        $medReqId = (string) Str::uuid();
        $reportId = (string) Str::uuid();
        $obsId = (string) Str::uuid();
        $docId = (string) Str::uuid();

        return [
            'resourceType' => 'Bundle',
            'id' => (string) Str::uuid(),
            'type' => 'document',
            'timestamp' => $now,
            'entry' => [
                // 1. Composition
                [
                    'fullUrl' => 'urn:uuid:'.$compositionId,
                    'resource' => [
                        'resourceType' => 'Composition',
                        'id' => $compositionId,
                        'status' => 'final',
                        'type' => [
                            'text' => 'Discharge Summary / Clinical Consultation Record',
                        ],
                        'date' => $now,
                        'title' => ' उत्तराखंड ParaCare+ Health Record',
                        'author' => [
                            [
                                'reference' => 'urn:uuid:'.$practitionerId,
                                'display' => 'Dr. Amit Negi (MD)',
                            ],
                        ],
                        'custodian' => [
                            'display' => 'Uttarakhand Civil Hospital',
                        ],
                    ],
                ],
                // 2. Patient
                [
                    'fullUrl' => 'urn:uuid:'.$patientId,
                    'resource' => [
                        'resourceType' => 'Patient',
                        'id' => $patientId,
                        'name' => [
                            ['text' => 'Rahul Sharma'],
                        ],
                        'gender' => 'male',
                        'birthDate' => '1990-05-15',
                    ],
                ],
                // 3. Practitioner
                [
                    'fullUrl' => 'urn:uuid:'.$practitionerId,
                    'resource' => [
                        'resourceType' => 'Practitioner',
                        'id' => $practitionerId,
                        'name' => [
                            ['text' => 'Dr. Amit Negi (MD)'],
                        ],
                        'identifier' => [
                            [
                                'system' => 'https://ndhm.gov.in/hpr',
                                'value' => 'amitnegi@hpr',
                            ],
                        ],
                    ],
                ],
                // 4. Encounter
                [
                    'fullUrl' => 'urn:uuid:'.$encounterId,
                    'resource' => [
                        'resourceType' => 'Encounter',
                        'id' => $encounterId,
                        'status' => 'finished',
                        'class' => ['code' => 'AMB'],
                        'type' => [
                            ['text' => 'OPD Consultation'],
                        ],
                        'period' => ['start' => $now],
                        'serviceProvider' => ['display' => 'Uttarakhand Civil Hospital'],
                    ],
                ],
                // 5. MedicationRequest (Prescription)
                [
                    'fullUrl' => 'urn:uuid:'.$medReqId,
                    'resource' => [
                        'resourceType' => 'MedicationRequest',
                        'id' => $medReqId,
                        'status' => 'active',
                        'intent' => 'order',
                        'authoredOn' => $now,
                        'medicationCodeableConcept' => [
                            'text' => 'Amoxicillin 500mg',
                        ],
                        'subject' => [
                            'reference' => 'urn:uuid:'.$patientId,
                        ],
                        'requester' => [
                            'reference' => 'urn:uuid:'.$practitionerId,
                            'display' => 'Dr. Amit Negi (MD)',
                        ],
                        'dosageInstruction' => [
                            [
                                'text' => '1 tablet thrice daily',
                                'timing' => [
                                    'repeat' => [
                                        'boundsDuration' => [
                                            'value' => 5,
                                            'unit' => 'days',
                                        ],
                                    ],
                                ],
                                'additionalInstruction' => [
                                    ['text' => 'Take after food'],
                                ],
                            ],
                        ],
                    ],
                ],
                // 6. Observation
                [
                    'fullUrl' => 'urn:uuid:'.$obsId,
                    'resource' => [
                        'resourceType' => 'Observation',
                        'id' => $obsId,
                        'status' => 'final',
                        'code' => [
                            'coding' => [
                                [
                                    'system' => 'http://loinc.org',
                                    'code' => '8867-4',
                                    'display' => 'Heart rate',
                                ],
                            ],
                            'text' => 'Pulse Rate',
                        ],
                        'effectiveDateTime' => $now,
                        'valueQuantity' => [
                            'value' => 78,
                            'unit' => 'bpm',
                        ],
                    ],
                ],
                // 7. DiagnosticReport
                [
                    'fullUrl' => 'urn:uuid:'.$reportId,
                    'resource' => [
                        'resourceType' => 'DiagnosticReport',
                        'id' => $reportId,
                        'status' => 'final',
                        'category' => [
                            ['text' => 'Laboratory'],
                        ],
                        'code' => [
                            'text' => 'Complete Blood Count (CBC)',
                        ],
                        'effectiveDateTime' => $now,
                        'conclusion' => 'Hemoglobin and WBC counts are within reference limits.',
                        'resultsInterpreter' => [
                            ['display' => 'Dr. Amit Negi (MD)'],
                        ],
                    ],
                ],
                // 8. DocumentReference
                [
                    'fullUrl' => 'urn:uuid:'.$docId,
                    'resource' => [
                        'resourceType' => 'DocumentReference',
                        'id' => $docId,
                        'status' => 'current',
                        'type' => [
                            'text' => 'Discharge Summary Certificate',
                        ],
                        'date' => $now,
                        'description' => 'Official Patient Discharge Advice Summary',
                        'content' => [
                            [
                                'attachment' => [
                                    'contentType' => 'text/plain',
                                    'data' => base64_encode('Patient recovered fully from acute respiratory tract infection. Advised rest for 3 days and Amoxicillin course completion.'),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
