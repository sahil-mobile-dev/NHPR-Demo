<?php

namespace App\Http\Controllers;

use App\Helpers\AbdmEncryptionHelper;
use App\Services\AadhaarOTPService;
use App\Services\HfrFacilityService;
use App\Services\HprAccountService;
use App\Services\HprDocumentService;
use App\Services\MobileOTPService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Class NhprRegistrationController
 *
 * Coordinates the multi-step HPR Registration Wizard flow.
 * Interacts with individual micro-services for Aadhaar, Mobile, HPR profile creation,
 * facility searching, professional mapping, and document uploading.
 * Supports toggling between real integration API mode and simulated workflow mode.
 */
class NhprRegistrationController extends Controller
{
    protected AadhaarOTPService $aadhaarService;

    protected MobileOTPService $mobileService;

    protected HprAccountService $hprService;

    protected HfrFacilityService $hfrService;

    protected HprDocumentService $documentService;

    /**
     * NhprRegistrationController constructor.
     */
    public function __construct(
        AadhaarOTPService $aadhaarService,
        MobileOTPService $mobileService,
        HprAccountService $hprService,
        HfrFacilityService $hfrService,
        HprDocumentService $documentService
    ) {
        $this->aadhaarService = $aadhaarService;
        $this->mobileService = $mobileService;
        $this->hprService = $hprService;
        $this->hfrService = $hfrService;
        $this->documentService = $documentService;
    }

    /**
     * Render the main HPR Registration Stepper Wizard.
     */
    public function showWizard(): View
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        // Display config statuses
        $config = [
            'baseUrl' => config('services.nhpr.base_url'),
            'apiUrl' => config('services.nhpr.api_url'),
            'xCmId' => config('services.nhpr.x_cm_id'),
            'isConfigured' => ! empty(config('services.nhpr.client_id')) && ! empty(config('services.nhpr.client_secret')),
            'realApiMode' => $realApiMode,
        ];

        // Clear wizard state when loading fresh view
        session()->forget([
            'hpr_reg_txn_id',
            'hpr_reg_aadhaar_info',
            'hpr_reg_mobile',
            'hpr_reg_hpr_token',
            'hpr_reg_hpr_id',
            'hpr_reg_category_code',
        ]);

        return view('nhpr.register', compact('config'));
    }

    /**
     * Toggle the active integration mode (Real API vs Simulated Mode) in the user's session.
     */
    public function toggleMode(Request $request): JsonResponse
    {
        $request->validate([
            'real_api_mode' => 'required|boolean',
        ]);

        $mode = (bool) $request->input('real_api_mode');
        session(['nhpr_real_api_mode' => $mode]);

        return response()->json([
            'success' => true,
            'real_api_mode' => $mode,
            'message' => $mode ? 'ABDM Gateway integration activated.' : 'Offline simulated workflow activated.',
        ]);
    }

    /**
     * Send OTP to Aadhaar registered mobile number.
     */
    public function sendAadhaarOtp(Request $request): JsonResponse
    {
        $request->validate([
            'aadhaar' => 'required|digits:12',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            $txnId = 'simulated-aadhaar-txn-'.Str::random(10);
            session(['hpr_reg_txn_id' => $txnId]);

            return response()->json([
                'success' => true,
                'message' => 'Simulated OTP sent to your registered mobile number ending with 8989.',
                'txnId' => $txnId,
            ]);
        }

        try {
            $encryptedAadhaar = AbdmEncryptionHelper::encrypt($request->input('aadhaar'));
            $result = $this->aadhaarService->generateOtp($encryptedAadhaar);

            session(['hpr_reg_txn_id' => $result['txnId']]);

            return response()->json([
                'success' => true,
                'message' => 'Aadhaar OTP has been sent successfully to your registered mobile number ending with '.substr($result['mobileNumber'] ?? 'XXXX', -4),
                'txnId' => $result['txnId'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify Aadhaar OTP and check if HPR ID already exists.
     */
    public function verifyAadhaarOtp(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            $aadhaarInfo = [
                'name' => 'Dr Ramesh Kumar (Simulated)',
                'gender' => 'M',
                'yearOfBirth' => '1990',
                'firstName' => 'Ramesh',
                'middleName' => '',
                'lastName' => 'Kumar',
                'stateCode' => '27',
                'districtCode' => '472',
                'profilePhoto' => '',
            ];
            session([
                'hpr_reg_aadhaar_info' => $aadhaarInfo,
                'hpr_reg_mobile' => '9876543210',
            ]);

            return response()->json([
                'success' => true,
                'isExistingUser' => false,
                'message' => 'Aadhaar verified successfully (Simulated Mode)!',
                'mobile' => '9876543210',
            ]);
        }

        $txnId = session('hpr_reg_txn_id');
        if (empty($txnId)) {
            return response()->json([
                'success' => false,
                'message' => 'Handshake session expired. Please re-enter Aadhaar and request OTP.',
            ], 400);
        }

        try {
            $encryptedOtp = AbdmEncryptionHelper::encrypt($request->input('otp'));
            $verifyResult = $this->aadhaarService->verifyOtp($encryptedOtp, $txnId);

            $currentTxnId = $verifyResult['txnId'] ?: $txnId;
            session(['hpr_reg_txn_id' => $currentTxnId]);

            // Check if HPR ID already exists
            $checkResult = $this->hprService->checkHprIdExists($currentTxnId);

            if (isset($checkResult['new']) && $checkResult['new'] === false) {
                // User already exists, abort registration flow
                session()->forget(['hpr_reg_txn_id']);

                return response()->json([
                    'success' => true,
                    'isExistingUser' => true,
                    'message' => 'HPR account already exists for this Aadhaar number.',
                    'profile' => [
                        'hprIdNumber' => $checkResult['hprIdNumber'] ?? null,
                        'name' => $checkResult['name'] ?? 'Healthcare Professional',
                        'gender' => $checkResult['gender'] ?? null,
                        'yearOfBirth' => $checkResult['yearOfBirth'] ?? null,
                        'address' => $checkResult['address'] ?? null,
                        'profilePhoto' => $checkResult['profilePhoto'] ?? null,
                    ],
                ]);
            }

            // Save demographic data to session for final creation
            session([
                'hpr_reg_aadhaar_info' => [
                    'name' => $checkResult['name'] ?? null,
                    'gender' => $checkResult['gender'] ?? null,
                    'yearOfBirth' => $checkResult['yearOfBirth'] ?? null,
                    'firstName' => $checkResult['firstName'] ?? null,
                    'middleName' => $checkResult['middleName'] ?? null,
                    'lastName' => $checkResult['lastName'] ?? null,
                    'stateCode' => $checkResult['stateCode'] ?? null,
                    'districtCode' => $checkResult['districtCode'] ?? null,
                    'profilePhoto' => $checkResult['profilePhoto'] ?? null,
                ],
                'hpr_reg_mobile' => $checkResult['mobile'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'isExistingUser' => false,
                'message' => 'Aadhaar verified successfully! Please verify your mobile number to continue.',
                'mobile' => $checkResult['mobile'] ?? null,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify mobile number via demographic check, or generate fallback OTP.
     */
    public function verifyMobile(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|digits:10',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $mobile = $request->input('mobile');

        if (! $realApiMode) {
            session(['hpr_reg_mobile' => $mobile]);

            return response()->json([
                'success' => true,
                'verified' => true,
                'message' => 'Mobile number verified successfully via demographic check (Simulated Mode).',
            ]);
        }

        $txnId = session('hpr_reg_txn_id');
        if (empty($txnId)) {
            return response()->json(['success' => false, 'message' => 'Session expired.'], 400);
        }

        try {
            $encryptedMobile = AbdmEncryptionHelper::encrypt($mobile);
            $isVerified = $this->mobileService->verifyDemographicMobile($txnId, $encryptedMobile);

            if ($isVerified) {
                // Mobile matched registered Aadhaar mobile. Proceed directly.
                session(['hpr_reg_mobile' => $mobile]);

                return response()->json([
                    'success' => true,
                    'verified' => true,
                    'message' => 'Mobile number verified successfully via demographic authentication.',
                ]);
            }

            // Fallback: Generate Mobile OTP
            $otpResult = $this->mobileService->generateMobileOtp($mobile, $txnId);
            session([
                'hpr_reg_txn_id' => $otpResult['txnId'],
                'hpr_reg_mobile' => $mobile,
            ]);

            return response()->json([
                'success' => true,
                'verified' => false,
                'message' => 'Demographic check failed. An OTP has been sent to '.$mobile.' for verification.',
                'txnId' => $otpResult['txnId'],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify fallback Mobile OTP.
     */
    public function verifyMobileOtp(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            return response()->json([
                'success' => true,
                'message' => 'Mobile OTP verified (Simulated Mode)!',
            ]);
        }

        $txnId = session('hpr_reg_txn_id');
        if (empty($txnId)) {
            return response()->json(['success' => false, 'message' => 'Session expired.'], 400);
        }

        try {
            $encryptedOtp = AbdmEncryptionHelper::encrypt($request->input('otp'));
            $verifyResult = $this->mobileService->verifyMobileOtp($encryptedOtp, $txnId);

            session(['hpr_reg_txn_id' => $verifyResult['txnId']]);

            return response()->json([
                'success' => true,
                'message' => 'Mobile number verified successfully!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get suggested usernames for the HPR profile.
     */
    public function getUsernameSuggestions(): JsonResponse
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            return response()->json([
                'success' => true,
                'suggestions' => ['doctor1990', 'dr.rahul', 'rahul123', 'rahul.k', 'rahulkumar'],
            ]);
        }

        $txnId = session('hpr_reg_txn_id');
        if (empty($txnId)) {
            return response()->json(['success' => false, 'message' => 'Session expired.'], 400);
        }

        try {
            $suggestions = $this->hprService->getUsernameSuggestions($txnId);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create the HPR ID profile.
     */
    public function createHprId(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|alpha_dash|min:4',
            'password' => 'required|min:8',
            'email' => 'required|email',
            'category' => 'required|in:1,2', // 1=Doctor, 2=Nurse
            'subcategory' => 'required|integer',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            $username = $request->input('username').'@hpr.abdm';
            session([
                'hpr_reg_hpr_token' => 'simulated-jwt-token-'.Str::random(10),
                'hpr_reg_hpr_id' => $username,
                'hpr_reg_category_code' => $request->input('category'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'HPR ID created (Simulated Mode)!',
                'hprId' => $username,
                'hprIdNumber' => '71-'.rand(1000, 9999).'-'.rand(1000, 9999).'-2283',
            ]);
        }

        $txnId = session('hpr_reg_txn_id');
        $profile = session('hpr_reg_aadhaar_info');

        if (empty($txnId) || empty($profile)) {
            return response()->json(['success' => false, 'message' => 'Session expired.'], 400);
        }

        try {
            // Encrypt sensitive inputs
            $encryptedEmail = AbdmEncryptionHelper::encrypt($request->input('email'));
            $encryptedPassword = AbdmEncryptionHelper::encrypt($request->input('password'));

            $payload = [
                'txnId' => $txnId,
                'email' => $encryptedEmail,
                'idType' => 'hpr_id',
                'domainName' => '@hpr.abdm',
                'firstName' => $profile['firstName'] ?? '',
                'middleName' => $profile['middleName'] ?? '',
                'lastName' => $profile['lastName'] ?? '',
                'password' => $encryptedPassword,
                'profilePhoto' => $profile['profilePhoto'] ?? '',
                'hprId' => $request->input('username'),
                'sourceType' => 'AADHAAR',
                'hpCategoryCode' => (int) $request->input('category'),
                'hpSubCategoryCode' => (int) $request->input('subcategory'),
                'clientId' => '',
                'stateCode' => $profile['stateCode'] ?? '',
                'districtCode' => $profile['districtCode'] ?? '',
                'council' => false,
                'role' => 3,
            ];

            $result = $this->hprService->createHprId($payload);

            session([
                'hpr_reg_hpr_token' => $result['hprToken'],
                'hpr_reg_hpr_id' => $result['hprId'],
                'hpr_reg_category_code' => $request->input('category'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'HPR ID created successfully!',
                'hprId' => $result['hprId'],
                'hprIdNumber' => $result['hprIdNumber'],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search HFR Facility index.
     */
    public function searchFacility(Request $request): JsonResponse
    {
        $request->validate([
            'facilityName' => 'nullable|string',
            'pincode' => 'nullable|digits:6',
            'facilityId' => 'nullable|string',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            $facilities = [
                [
                    'facilityId' => 'IN2710000059',
                    'facilityName' => 'Dehradun Civil Hospital (Simulated)',
                    'address' => 'EC Road, Dehradun',
                    'pincode' => $request->input('pincode') ?: '248001',
                    'stateName' => 'Uttarakhand',
                ],
                [
                    'facilityId' => 'IN2710000060',
                    'facilityName' => 'AIIMS Rishikesh OPD (Simulated)',
                    'address' => 'Virbhadra Road, Rishikesh',
                    'pincode' => '249201',
                    'stateName' => 'Uttarakhand',
                ],
                [
                    'facilityId' => 'IN2710000061',
                    'facilityName' => 'Haldwani Base Hospital (Simulated)',
                    'address' => 'Haldwani, Nainital',
                    'pincode' => '263139',
                    'stateName' => 'Uttarakhand',
                ],
            ];

            return response()->json([
                'success' => true,
                'facilities' => $facilities,
                'totalFacilities' => count($facilities),
            ]);
        }

        try {
            $searchParams = $request->only(['facilityName', 'pincode', 'facilityId']);
            $result = $this->hfrService->searchFacility($searchParams);

            return response()->json([
                'success' => true,
                'facilities' => $result['facilities'],
                'totalFacilities' => $result['totalFacilities'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit professional registration and facility link.
     */
    public function submitProfessionalRegistration(Request $request): JsonResponse
    {
        $request->validate([
            'salutation' => 'required|integer',
            'dob' => 'required|date_format:Y-m-d',
            'languages' => 'required|string',
            'address' => 'required|string',
            'pincode' => 'required|digits:6',
            'council_id' => 'required|integer',
            'reg_no' => 'required|string',
            'reg_date' => 'required|date_format:Y-m-d',
            'reg_cert_base64' => 'required|string',
            'degree_code' => 'required|integer',
            'degree_college' => 'required|integer',
            'degree_university' => 'required|integer',
            'degree_year' => 'required|digits:4',
            'degree_cert_base64' => 'required|string',
            'currently_working' => 'required|in:0,1',
            'work_status' => 'required|integer',
            'facility_id' => 'required_if:currently_working,1|nullable|string',
            'facility_name' => 'required_if:currently_working,1|nullable|string',
            'facility_address' => 'required_if:currently_working,1|nullable|string',
            'facility_pincode' => 'required_if:currently_working,1|nullable|digits:6',
            'work_cert_base64' => 'nullable|string',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            $hprId = session('hpr_reg_hpr_id', 'practitioner@hpr.abdm');

            return response()->json([
                'success' => true,
                'message' => 'Professional registered successfully (Simulated Mode)!',
                'referenceNumber' => 'simulated-ref-'.Str::random(10),
                'hprId' => $hprId,
            ]);
        }

        $hprToken = session('hpr_reg_hpr_token');
        $profile = session('hpr_reg_aadhaar_info');
        $mobile = session('hpr_reg_mobile');
        $category = session('hpr_reg_category_code');

        if (empty($hprToken) || empty($profile)) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please restart the wizard.'], 400);
        }

        try {
            $professionalType = $category == 1 ? 'doctor' : 'nurse';

            $payload = [
                'hprToken' => $hprToken,
                'practitioner' => [
                    'healthProfessionalType' => $professionalType,
                    'apiClientId' => '',
                    'profilePhoto' => $profile['profilePhoto'] ?? '',
                    'officialMobile' => $mobile,
                    'officialEmail' => '',
                    'personalInformation' => [
                        'salutation' => (int) $request->input('salutation'),
                        'firstName' => $profile['firstName'],
                        'lastName' => $profile['lastName'],
                        'nationality' => '356',
                        'gender' => $profile['gender'],
                        'dateOfBirth' => $request->input('dob'),
                        'languagesSpoken' => $request->input('languages'),
                        'category' => 'C',
                    ],
                    'communicationAddress' => [
                        'isCommunicationAddressAsPerKYC' => 'false',
                        'address' => $request->input('address'),
                        'pincode' => $request->input('pincode'),
                    ],
                    'registrationAcademic' => [
                        'category' => (int) $category,
                        'registrationData' => [
                            [
                                'registeredWithCouncil' => (int) $request->input('council_id'),
                                'registrationNumber' => $request->input('reg_no'),
                                'registrationDate' => $request->input('reg_date'),
                                'registrationCertificate' => [
                                    'fileType' => 'pdf',
                                    'data' => $request->input('reg_cert_base64'),
                                ],
                                'isPermanentOrRenewable' => 'Permanent',
                                'qualifications' => [
                                    [
                                        'nameOfDegreeOrDiplomaObtained' => (int) $request->input('degree_code'),
                                        'country' => '356',
                                        'state' => $profile['stateCode'] ?: '29',
                                        'college' => (int) $request->input('degree_college'),
                                        'university' => (int) $request->input('degree_university'),
                                        'yearOfAwardingDegreeDiploma' => $request->input('degree_year'),
                                        'degreeCertificate' => [
                                            'fileType' => 'pdf',
                                            'data' => $request->input('degree_cert_base64'),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'currentWorkDetails' => [
                        'currentlyWorking' => $request->input('currently_working'),
                        'purposeOfWork' => 'Practice',
                        'chooseWorkStatus' => (int) $request->input('work_status'),
                        'certificateAttachment' => $request->input('work_cert_base64') ?: '',
                    ],
                ],
            ];

            // Append facility link details if currently working
            if ($request->input('currently_working') == '1') {
                $payload['practitioner']['currentWorkDetails']['facilityDeclarationData'] = [
                    'facilityId' => $request->input('facility_id'),
                    'facilityName' => $request->input('facility_name'),
                    'facilityAddress' => $request->input('facility_address'),
                    'facilityPincode' => $request->input('facility_pincode'),
                    'state' => $profile['stateCode'] ?: '27',
                    'district' => $profile['districtCode'] ?: '500',
                    'facilityType' => 'Hospital',
                ];
            }

            $result = $this->hfrService->registerProfessional($payload);

            session(['hpr_reg_hpr_id' => $result['hprId']]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'referenceNumber' => $result['referenceNumber'],
                'hprId' => $result['hprId'],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch document block IDs checklist for file uploads.
     */
    public function fetchDocuments(): JsonResponse
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            return response()->json([
                'success' => true,
                'documentList' => [
                    'profilePhoto' => ['id' => 40169],
                    'degreeCertificate' => ['id' => 13953],
                    'registrationCertificate' => ['id' => 27409],
                ],
            ]);
        }

        $hprId = session('hpr_reg_hpr_id');
        if (empty($hprId)) {
            return response()->json(['success' => false, 'message' => 'Session expired. Registration ID missing.'], 400);
        }

        try {
            $docList = $this->documentService->fetchRequiredDocuments($hprId);

            return response()->json([
                'success' => true,
                'documentList' => $docList,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload Base64 certificates and documents.
     */
    public function uploadDocuments(Request $request): JsonResponse
    {
        $request->validate([
            'documents' => 'required|array|min:1',
            'documents.*.document_id' => 'required|integer',
            'documents.*.document_type' => 'required|string',
            'documents.*.fileType' => 'required|string',
            'documents.*.data' => 'required|string',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            // Clear wizard session states upon completion of wizard
            session()->forget([
                'hpr_reg_txn_id',
                'hpr_reg_aadhaar_info',
                'hpr_reg_mobile',
                'hpr_reg_hpr_token',
                'hpr_reg_hpr_id',
                'hpr_reg_category_code',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documents uploaded and registration completed successfully (Simulated Mode)!',
                'result' => [
                    'hprIdNumber' => '71-3563-6824-2283',
                ],
            ]);
        }

        $hprToken = session('hpr_reg_hpr_token');
        if (empty($hprToken)) {
            return response()->json(['success' => false, 'message' => 'Session expired. Authentication token missing.'], 400);
        }

        try {
            $result = $this->documentService->uploadDocuments($hprToken, $request->input('documents'));

            // Clear session data upon completion of wizard
            session()->forget([
                'hpr_reg_txn_id',
                'hpr_reg_aadhaar_info',
                'hpr_reg_mobile',
                'hpr_reg_hpr_token',
                'hpr_reg_hpr_id',
                'hpr_reg_category_code',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documents uploaded and registration completed successfully!',
                'result' => $result,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the status tracking view.
     */
    public function showTracker(): View
    {
        $config = [
            'realApiMode' => session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false)),
        ];

        return view('nhpr.track', compact('config'));
    }

    /**
     * Track status of an HPR application.
     */
    public function trackStatus(Request $request): JsonResponse
    {
        $request->validate([
            'reference_number' => 'required|string|min:4',
        ]);

        $ref = strtoupper(trim($request->input('reference_number')));
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if ($realApiMode) {
            try {
                $result = $this->hprService->fetchProfessionalDetails($ref);

                // Parse nested array if present: "practitioners": [ [ { ... } ] ]
                $practitioners = $result['practitioners'] ?? [];
                if (isset($practitioners[0]) && is_array($practitioners[0])) {
                    $practitioner = $practitioners[0][0] ?? $practitioners[0] ?? null;
                } else {
                    $practitioner = $practitioners[0] ?? null;
                }

                if (empty($practitioner)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No active practitioner found with the given HPR ID.',
                    ], 404);
                }

                $appStatus = $practitioner['application_status'] ?? 'Pending';
                $councilVerified = $practitioner['is_council_verified'] ?? 'Submitted';
                $workVerified = $practitioner['is_work_verified'] ?? 'Submitted';

                // Map overall status to APPROVED, REVIEW, or ISSUES
                $status = 'REVIEW';
                $message = 'Application is currently under verification.';
                if ($appStatus === 'Approved') {
                    $status = 'APPROVED';
                    $message = 'Application approved. HPR ID profile is active.';
                } elseif ($councilVerified === 'Rejected') {
                    $status = 'ISSUES';
                    $message = 'Application review halted due to medical council verification rejection.';
                }

                // Map steps timeline
                $steps = [
                    [
                        'name' => 'Application Submitted',
                        'status' => 'COMPLETED',
                        'updated_at' => now()->subDays(2)->toDateTimeString(),
                        'desc' => 'Application received successfully.',
                    ],
                    [
                        'name' => 'Aadhaar Demographic Auth',
                        'status' => 'COMPLETED',
                        'updated_at' => now()->subDays(2)->toDateTimeString(),
                        'desc' => 'Demographic and KYC details verified.',
                    ],
                    [
                        'name' => 'State Council Verification',
                        'status' => ($councilVerified === 'Approved') ? 'COMPLETED' : (($councilVerified === 'Rejected') ? 'FAILED' : 'PROCESSING'),
                        'updated_at' => ($councilVerified === 'Approved' || $councilVerified === 'Rejected') ? now()->subDays(1)->toDateTimeString() : null,
                        'desc' => ($councilVerified === 'Approved') ? 'Verified by State Medical Council.' : (($councilVerified === 'Rejected') ? 'Rejected by State Registrar. Check credentials/documents.' : 'Pending Medical Council verification.'),
                    ],
                    [
                        'name' => 'HPR ID Issuance',
                        'status' => ($appStatus === 'Approved') ? 'COMPLETED' : 'PENDING',
                        'updated_at' => ($appStatus === 'Approved') ? now()->toDateTimeString() : null,
                        'desc' => ($appStatus === 'Approved') ? 'HPR ID active and verified under ABDM Gateway.' : 'Awaiting council review completion.',
                    ],
                ];

                return response()->json([
                    'success' => true,
                    'reference_number' => $ref,
                    'status' => $status,
                    'message' => $message,
                    'real_api_mode' => true,
                    'steps' => $steps,
                ]);

            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'ABDM Gateway Status Error: '.$e->getMessage(),
                ], 500);
            }
        }

        // Define tracking steps dynamically for mock mode
        if (str_contains($ref, 'REJECT') || str_contains($ref, 'FAIL') || str_contains($ref, 'ERROR')) {
            $status = 'ISSUES';
            $message = 'Application review halted due to documents issues.';
            $steps = [
                ['name' => 'Application Submitted', 'status' => 'COMPLETED', 'updated_at' => now()->subDays(3)->toDateTimeString(), 'desc' => 'Application received successfully.'],
                ['name' => 'Aadhaar Demographic Auth', 'status' => 'COMPLETED', 'updated_at' => now()->subDays(3)->toDateTimeString(), 'desc' => 'Demographic and KYC details verified.'],
                ['name' => 'State Council Verification', 'status' => 'FAILED', 'updated_at' => now()->subDays(1)->toDateTimeString(), 'desc' => 'Degree certificate scan is blurred or illegible. Please re-upload.'],
                ['name' => 'HPR ID Issuance', 'status' => 'PENDING', 'updated_at' => null, 'desc' => 'Awaiting state council clearance.'],
            ];
        } elseif (str_contains($ref, 'PENDING') || str_contains($ref, 'REVIEW') || str_contains($ref, 'WAIT')) {
            $status = 'REVIEW';
            $message = 'Application is currently under verification by the State Medical Council.';
            $steps = [
                ['name' => 'Application Submitted', 'status' => 'COMPLETED', 'updated_at' => now()->subDays(2)->toDateTimeString(), 'desc' => 'Application received successfully.'],
                ['name' => 'Aadhaar Demographic Auth', 'status' => 'COMPLETED', 'updated_at' => now()->subDays(2)->toDateTimeString(), 'desc' => 'Demographic and KYC details verified.'],
                ['name' => 'State Council Verification', 'status' => 'PROCESSING', 'updated_at' => now()->subMinutes(30)->toDateTimeString(), 'desc' => 'Verification in progress by State Registrar.'],
                ['name' => 'HPR ID Issuance', 'status' => 'PENDING', 'updated_at' => null, 'desc' => 'Awaiting review completion.'],
            ];
        } else {
            $status = 'APPROVED';
            $message = 'Application approved. HPR ID profile is active.';
            $steps = [
                ['name' => 'Application Submitted', 'status' => 'COMPLETED', 'updated_at' => now()->subDays(5)->toDateTimeString(), 'desc' => 'Application received successfully.'],
                ['name' => 'Aadhaar Demographic Auth', 'status' => 'COMPLETED', 'updated_at' => now()->subDays(5)->toDateTimeString(), 'desc' => 'Demographic and KYC details verified.'],
                ['name' => 'State Council Verification', 'status' => 'COMPLETED', 'updated_at' => now()->subDays(2)->toDateTimeString(), 'desc' => 'Medical degree and credentials verified by State Registrar.'],
                ['name' => 'HPR ID Issuance', 'status' => 'COMPLETED', 'updated_at' => now()->subDays(1)->toDateTimeString(), 'desc' => 'HPR ID active and token issued under ABDM Gateway.'],
            ];
        }

        return response()->json([
            'success' => true,
            'reference_number' => $ref,
            'status' => $status,
            'message' => $message,
            'real_api_mode' => $realApiMode,
            'steps' => $steps,
        ]);
    }
}
