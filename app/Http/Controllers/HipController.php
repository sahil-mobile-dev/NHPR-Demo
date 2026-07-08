<?php

namespace App\Http\Controllers;

use App\Helpers\AbdmCryptEngine;
use App\Models\CareContext;
use App\Models\ConsentArtefact;
use App\Models\HealthRecord;
use App\Models\SecurityAuditLog;
use App\Services\FhirBundleService;
use App\Services\HipLinkingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Class HipController
 *
 * Orchestrates clinical record creation, FHIR generation, outbound linking, and simulated ABDM callbacks.
 */
class HipController extends Controller
{
    protected FhirBundleService $fhirService;

    protected HipLinkingService $linkingService;

    /**
     * HipController constructor.
     */
    public function __construct(FhirBundleService $fhirService, HipLinkingService $linkingService)
    {
        $this->fhirService = $fhirService;
        $this->linkingService = $linkingService;
    }

    /**
     * Display the HIP Dashboard.
     */
    public function showDashboard(): View
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $contexts = CareContext::with('healthRecords')->orderBy('created_at', 'desc')->get();

        $stats = [
            'total' => $contexts->count(),
            'linked' => $contexts->where('is_linked', true)->count(),
            'unlinked' => $contexts->where('is_linked', false)->count(),
        ];

        return view('hip.dashboard', compact('contexts', 'stats', 'realApiMode'));
    }

    /**
     * Display the ABDM Milestone 2 Features dashboard/mapping.
     */
    public function milestone2Features(): View
    {
        $stats = [
            'contexts_count' => CareContext::count(),
            'consents_count' => ConsentArtefact::count(),
            'records_count' => HealthRecord::count(),
            'audits_count' => SecurityAuditLog::count(),
        ];

        return view('hip.milestone2', compact('stats'));
    }

    /**
     * Create a clinical record, compile to FHIR, and save to database.
     */
    public function createRecordStore(Request $request): JsonResponse
    {
        $request->validate([
            'patient_name' => 'required|string',
            'patient_gender' => 'required|in:M,F,O',
            'patient_dob' => 'required|date',
            'patient_abha_address' => 'required|string',
            'record_type' => 'required|in:PRESCRIPTION,DIAGNOSTIC_REPORT',
            'doctor_name' => 'required|string',
            'doctor_hpr_id' => 'required|string',
        ]);

        $recordType = $request->input('record_type');

        try {
            // 1. Generate FHIR R4 Bundle
            $fhirData = [];
            if ($recordType === 'PRESCRIPTION') {
                $medicationsInput = $request->input('medications', []);
                $medications = [];
                foreach ($medicationsInput as $med) {
                    if (! empty($med['name'])) {
                        $medications[] = [
                            'name' => $med['name'],
                            'dosage' => $med['dosage'] ?? '1 tablet',
                            'duration' => $med['duration'] ?? '5 days',
                            'instructions' => $med['instructions'] ?? 'Once daily after food',
                        ];
                    }
                }

                if (empty($medications)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please provide at least one medication details.',
                    ], 400);
                }

                $fhirData = $this->fhirService->buildPrescriptionBundle(array_merge($request->all(), [
                    'medications' => $medications,
                ]));
            } else {
                $fhirData = $this->fhirService->buildDiagnosticReportBundle($request->all());
            }

            // 2. Create Care Context Reference
            $refNumber = ($recordType === 'PRESCRIPTION' ? 'OPD-' : 'LAB-').rand(10000, 99999);
            $display = $recordType === 'PRESCRIPTION' ? 'OPD Consultation Visit' : 'Diagnostic Lab Test Report';

            $context = CareContext::create([
                'patient_abha_number' => $request->input('patient_abha_number'),
                'patient_abha_address' => trim($request->input('patient_abha_address')),
                'care_context_reference' => $refNumber,
                'display' => $display,
                'is_linked' => false,
            ]);

            // 3. Save Health Record
            HealthRecord::create([
                'care_context_id' => $context->id,
                'record_type' => $recordType,
                'record_date' => now(),
                'fhir_data' => $fhirData,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Record created and FHIR bundle compiled successfully!',
                'reference' => $refNumber,
                'fhir' => $fhirData,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to compile FHIR: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Link Care Contexts to ABHA.
     */
    public function linkContextPost(Request $request): JsonResponse
    {
        $request->validate([
            'care_context_id' => 'required|exists:care_contexts,id',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $context = CareContext::findOrFail($request->input('care_context_id'));

        if (! $realApiMode) {
            $context->update(['is_linked' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Care Context successfully linked to ABHA (Simulated Mode)!',
            ]);
        }

        try {
            $userToken = session('abha_verify_token') ?? session('abha_enroll_token');
            if (empty($userToken)) {
                throw new Exception('Linking Token not found in session. Please verify ABHA first.');
            }

            $contextsPayload = [
                [
                    'referenceNumber' => $context->care_context_reference,
                    'display' => $context->display,
                ],
            ];

            $this->linkingService->linkCareContext($userToken, $context->patient_abha_address, $contextsPayload);
            $context->update(['is_linked' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Care Context linked to ABHA successfully on ABDM Gateway!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display Patient Consents and Security Audit Logs for Hospital staff.
     */
    public function showConsentsAndAuditLogs(): View
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $consents = ConsentArtefact::orderBy('created_at', 'desc')->get();
        $auditLogs = SecurityAuditLog::orderBy('created_at', 'desc')->get();

        $stats = [
            'total_consents' => $consents->count(),
            'active_consents' => $consents->where('status', 'GRANTED')->count(),
            'transfers_count' => $auditLogs->where('status', 'SUCCESS')->count(),
        ];

        return view('hip.consents', compact('consents', 'auditLogs', 'stats', 'realApiMode'));
    }

    /**
     * Simulate Patient Discovery match request.
     */
    public function simulateDiscovery(Request $request): JsonResponse
    {
        $request->validate([
            'abha_address' => 'required|string',
        ]);

        $address = trim($request->input('abha_address'));

        // Find matching care contexts in database
        $contexts = CareContext::where('patient_abha_address', $address)
            ->where('is_linked', false)
            ->get();

        $matchedContexts = [];
        foreach ($contexts as $c) {
            $matchedContexts[] = [
                'referenceNumber' => $c->care_context_reference,
                'display' => $c->display,
            ];
        }

        return response()->json([
            'success' => true,
            'patient' => [
                'referenceNumber' => $address,
                'display' => $address,
                'careContexts' => $matchedContexts,
            ],
            'message' => 'ABDM patient discovery simulation processed successfully.',
        ]);
    }

    /**
     * Simulate incoming Consent Notification callback.
     */
    public function simulateConsent(Request $request): JsonResponse
    {
        $request->validate([
            'patient_abha_address' => 'required|string',
            'status' => 'required|in:GRANTED,REVOKED',
        ]);

        $consentId = 'CON-'.rand(100000, 999999);
        $status = $request->input('status');
        $address = trim($request->input('patient_abha_address'));

        $detail = [
            'consentId' => $consentId,
            'status' => $status,
            'purpose' => 'General Consultation',
            'hiuId' => 'HIU-98765',
            'hipId' => 'HIP-UK-HIMS',
            'permission' => [
                'accessMode' => 'VIEW',
                'dateRange' => [
                    'from' => now()->subYear()->toIso8601String(),
                    'to' => now()->addYear()->toIso8601String(),
                ],
                'dataTypes' => ['Prescription', 'DiagnosticReport'],
            ],
        ];

        ConsentArtefact::updateOrCreate(
            ['consent_id' => $consentId],
            [
                'status' => $status,
                'patient_abha_address' => $address,
                'consent_detail' => $detail,
            ]
        );

        return response()->json([
            'success' => true,
            'consent_id' => $consentId,
            'message' => 'Consent notification stored successfully (Simulated Callback).',
        ]);
    }

    /**
     * Simulate Health Information Request against a Consent (with Native Cryptography).
     */
    public function simulateHealthRequest(Request $request): JsonResponse
    {
        $request->validate([
            'consent_id' => 'required|exists:consent_artefacts,consent_id',
        ]);

        $consent = ConsentArtefact::where('consent_id', $request->input('consent_id'))->firstOrFail();

        if ($consent->status !== 'GRANTED') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot request health data. Consent status is '.$consent->status,
            ], 400);
        }

        // 1. Fetch patient records
        $contexts = CareContext::where('patient_abha_address', $consent->patient_abha_address)
            ->with('healthRecords')
            ->get();

        if ($contexts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No health records found for patient: '.$consent->patient_abha_address,
            ], 404);
        }

        // Compile combined FHIR R4 Bundle elements
        $records = [];
        foreach ($contexts as $c) {
            foreach ($c->healthRecords as $r) {
                $records[] = $r->fhir_data;
            }
        }
        $rawPlaintext = json_encode($records);

        try {
            // 2. Generate Local HIP Keys & Simulated Remote HIU Keys
            $hipKeys = AbdmCryptEngine::generateKeypair();
            $hiuKeys = AbdmCryptEngine::generateKeypair();

            // 3. Encrypt data from HIP -> HIU using native openSSL ECDH
            $hipSharedSecret = AbdmCryptEngine::deriveSharedSecret($hiuKeys['publicKey'], $hipKeys['privateKey']);
            $encrypted = AbdmCryptEngine::encryptPayload($rawPlaintext, $hipSharedSecret);

            // 4. Decrypt data at HIU to verify security
            $hiuSharedSecret = AbdmCryptEngine::deriveSharedSecret($hipKeys['publicKey'], $hiuKeys['privateKey']);
            $decryptedText = AbdmCryptEngine::decryptPayload(
                $encrypted['ciphertext'],
                $hiuSharedSecret,
                $encrypted['iv'],
                $encrypted['tag']
            );

            $decryptedData = json_decode($decryptedText, true);

            // Log secure exchange in HIMS audit trail
            SecurityAuditLog::create([
                'transaction_id' => 'TXN-'.rand(100000, 999999),
                'consent_id' => $consent->consent_id,
                'hiu_id' => 'HIU-98765',
                'patient_abha_address' => $consent->patient_abha_address,
                'records_transferred' => count($records),
                'status' => 'SUCCESS',
            ]);

            return response()->json([
                'success' => true,
                'crypto' => [
                    'hipPublicKey' => substr($hipKeys['publicKey'], 0, 80).'...',
                    'hiuPublicKey' => substr($hiuKeys['publicKey'], 0, 80).'...',
                    'ciphertext' => substr($encrypted['ciphertext'], 0, 100).'...',
                    'iv' => $encrypted['iv'],
                    'tag' => $encrypted['tag'],
                ],
                'original_records_count' => count($records),
                'decrypted_records' => $decryptedData,
                'message' => 'Health data packaged, ECDH Diffie-Hellman keys exchanged, encrypted via AES-GCM-256, and verified successfully!',
            ]);
        } catch (Exception $e) {
            SecurityAuditLog::create([
                'transaction_id' => 'TXN-'.rand(100000, 999999),
                'consent_id' => $consent->consent_id,
                'hiu_id' => 'HIU-98765',
                'patient_abha_address' => $consent->patient_abha_address,
                'records_transferred' => count($records) ?? 0,
                'status' => 'FAILED',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cryptography workflow failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Official Callback: Discover Care Context (ABDM calls HIP)
     * POST /api/v3/hip/discover
     */
    public function apiDiscover(Request $request): JsonResponse
    {
        Log::info('ABDM Callback: Discover Care Context', $request->all());

        $abhaAddress = $request->input('patient.id') ?? $request->input('patient.verifiedIdentifiers.0.value');
        if (empty($abhaAddress)) {
            return response()->json(['error' => 'Patient identifier not found'], 400);
        }

        $contexts = CareContext::where('patient_abha_address', $abhaAddress)->get();
        $matchedContexts = [];
        foreach ($contexts as $c) {
            $matchedContexts[] = [
                'referenceNumber' => $c->care_context_reference,
                'display' => $c->display,
            ];
        }

        $patientMatches = [
            'referenceNumber' => $abhaAddress,
            'display' => $abhaAddress,
            'careContexts' => $matchedContexts,
        ];

        try {
            $this->linkingService->onDiscoverResponse(
                $request->input('transactionId') ?? (string) Str::uuid(),
                $patientMatches
            );
        } catch (Exception $e) {
            Log::error('Discover callback failed to send on-discover: '.$e->getMessage());
        }

        return response()->json(['status' => 'ACCEPTED'], 202);
    }

    /**
     * Official Callback: Link Init (ABDM calls HIP)
     * POST /api/v3/hip/link/init
     */
    public function apiLinkInit(Request $request): JsonResponse
    {
        Log::info('ABDM Callback: Link Init', $request->all());

        return response()->json(['status' => 'ACCEPTED'], 202);
    }

    /**
     * Official Callback: Link Confirm (ABDM calls HIP)
     * POST /api/v3/hip/link/confirm
     */
    public function apiLinkConfirm(Request $request): JsonResponse
    {
        Log::info('ABDM Callback: Link Confirm', $request->all());

        return response()->json(['status' => 'ACCEPTED'], 202);
    }

    /**
     * Official Callback: Consent Notification (ABDM calls HIP)
     * POST /api/v3/consents/hip/notify
     */
    public function apiConsentNotify(Request $request): JsonResponse
    {
        Log::info('ABDM Callback: Consent Notification', $request->all());

        $notification = $request->input('notification');
        if ($notification) {
            ConsentArtefact::updateOrCreate(
                ['consent_id' => $notification['consentId']],
                [
                    'status' => $notification['status'],
                    'patient_abha_address' => $notification['consentDetail']['patient']['id'] ?? '',
                    'consent_detail' => $notification,
                ]
            );
        }

        return response()->json(['status' => 'ACCEPTED'], 202);
    }

    /**
     * Official Callback: Health Information Request (ABDM calls HIP)
     * POST /api/v3/health-information/hip/request
     */
    public function apiHealthRequest(Request $request): JsonResponse
    {
        Log::info('ABDM Callback: Health Information Request', $request->all());

        $consentId = $request->input('hiRequest.consent.id');
        $transactionId = $request->input('transactionId');
        $dataPushUrl = $request->input('hiRequest.dataPushUrl');
        $receiverPublicKey = $request->input('hiRequest.keyMaterial.dhPublicKey.keyValue');

        if (empty($consentId) || empty($transactionId) || empty($dataPushUrl)) {
            return response()->json(['error' => 'Invalid request params'], 400);
        }

        $consent = ConsentArtefact::where('consent_id', $consentId)->first();
        if ($consent && $consent->status === 'GRANTED') {
            $contexts = CareContext::where('patient_abha_address', $consent->patient_abha_address)
                ->with('healthRecords')
                ->get();

            $records = [];
            foreach ($contexts as $c) {
                foreach ($c->healthRecords as $r) {
                    $records[] = $r->fhir_data;
                }
            }

            try {
                $hipKeys = AbdmCryptEngine::generateKeypair();

                if ($receiverPublicKey) {
                    $sharedSecret = AbdmCryptEngine::deriveSharedSecret($receiverPublicKey, $hipKeys['privateKey']);
                    $encrypted = AbdmCryptEngine::encryptPayload(json_encode($records), $sharedSecret);

                    Http::post($dataPushUrl, [
                        'pageNumber' => 1,
                        'pageCount' => 1,
                        'transactionId' => $transactionId,
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
                            'nonce' => $encrypted['iv'],
                        ],
                    ]);
                }

                $this->linkingService->notifyHealthInformationTransfer($transactionId, 'DELIVERED');

                SecurityAuditLog::create([
                    'transaction_id' => $transactionId,
                    'consent_id' => $consentId,
                    'hiu_id' => $request->input('hiRequest.requester.id') ?? 'HIU-GATEWAY',
                    'patient_abha_address' => $consent->patient_abha_address,
                    'records_transferred' => count($records),
                    'status' => 'SUCCESS',
                ]);
            } catch (Exception $e) {
                Log::error('Health transfer failed: '.$e->getMessage());

                SecurityAuditLog::create([
                    'transaction_id' => $transactionId,
                    'consent_id' => $consentId,
                    'hiu_id' => $request->input('hiRequest.requester.id') ?? 'HIU-GATEWAY',
                    'patient_abha_address' => $consent->patient_abha_address,
                    'records_transferred' => count($records) ?? 0,
                    'status' => 'FAILED',
                ]);

                try {
                    $this->linkingService->notifyHealthInformationTransfer($transactionId, 'FAILED');
                } catch (Exception $ex) {
                }
            }
        }

        return response()->json(['status' => 'ACCEPTED'], 202);
    }

    /**
     * Callback: Link token on-generate (Gateway callback to HIP)
     * POST /api/v3/hip/token/on-generate-token
     */
    public function apiLinkTokenOnGenerate(Request $request): JsonResponse
    {
        Log::info('ABDM Callback: Link Token On-Generate', $request->all());

        return response()->json(['status' => 'ACCEPTED'], 202);
    }

    /**
     * Callback: Link care context response (Gateway callback to HIP)
     * POST /api/v3/link/on-carecontext
     */
    public function apiLinkOnCareContext(Request $request): JsonResponse
    {
        Log::info('ABDM Callback: Link On Care Context', $request->all());

        if (! $request->has('error')) {
            $abhaAddress = $request->input('abhaAddress') ?? $request->input('patient.id');
            if ($abhaAddress) {
                CareContext::where('patient_abha_address', $abhaAddress)->update(['is_linked' => true]);
            }
        }

        return response()->json(['status' => 'ACCEPTED'], 202);
    }

    /**
     * Callback: Notify care context update response (Gateway callback to HIP)
     * POST /api/v3/links/context/on-notify
     */
    public function apiLinkContextOnNotify(Request $request): JsonResponse
    {
        Log::info('ABDM Callback: Link Context On-Notify', $request->all());

        return response()->json(['status' => 'ACCEPTED'], 202);
    }

    /**
     * Callback: Patients SMS Notify response (Gateway callback to HIP)
     * POST /api/v3/patients/sms/on-notify
     */
    public function apiPatientsSmsOnNotify(Request $request): JsonResponse
    {
        Log::info('ABDM Callback: Patients SMS On-Notify', $request->all());

        return response()->json(['status' => 'ACCEPTED'], 202);
    }

    /**
     * Callback: Patient profile share request (Gateway callback to HIP)
     * POST /api/v3/hip/patient/share
     */
    public function apiPatientShare(Request $request): JsonResponse
    {
        Log::info('ABDM Callback: Patient Share', $request->all());

        return response()->json(['status' => 'ACCEPTED'], 202);
    }
}
