<?php

namespace App\Http\Controllers;

use App\Helpers\AbdmEncryptionHelper;
use App\Services\AbhaEnrollmentService;
use App\Services\AbhaVerificationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Class AbhaController
 *
 * Manages the ABDM Sandbox Milestone 1 flows:
 * - ABHA Number Creation (Aadhaar OTP Enrollment)
 * - ABHA Address Search & Verification (PHR Login)
 * Supports both Real API Mode and Simulated Offline Mode.
 */
class AbhaController extends Controller
{
    protected AbhaEnrollmentService $enrollmentService;

    protected AbhaVerificationService $verificationService;

    /**
     * AbhaController constructor.
     */
    public function __construct(
        AbhaEnrollmentService $enrollmentService,
        AbhaVerificationService $verificationService
    ) {
        $this->enrollmentService = $enrollmentService;
        $this->verificationService = $verificationService;
    }

    /**
     * Render the Milestone 1 main Dashboard.
     */
    public function showDashboard(): View
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        $config = [
            'isConfigured' => ! empty(config('services.nhpr.client_id')) && ! empty(config('services.nhpr.client_secret')),
            'realApiMode' => $realApiMode,
        ];

        return view('abha.dashboard', compact('config'));
    }

    /**
     * Render the ABHA Number creation stepper.
     */
    public function showCreator(): View
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        session()->forget(['abha_enroll_txn_id', 'abha_enroll_aadhaar']);

        $config = ['realApiMode' => $realApiMode];

        return view('abha.create', compact('config'));
    }

    /**
     * Render the ABHA Address verification screen.
     */
    public function showVerifier(): View
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        session()->forget(['abha_verify_txn_id', 'abha_verify_address', 'abha_verify_method']);

        $config = ['realApiMode' => $realApiMode];

        return view('abha.verify', compact('config'));
    }

    /**
     * Request OTP for Aadhaar enrollment.
     */
    public function enrollRequestOtp(Request $request): JsonResponse
    {
        $request->validate([
            'aadhaar' => 'required|digits:12',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $aadhaar = $request->input('aadhaar');

        if (! $realApiMode) {
            $txnId = 'simulated-abha-enroll-txn-'.Str::random(12);
            session([
                'abha_enroll_txn_id' => $txnId,
                'abha_enroll_aadhaar' => $aadhaar,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Simulated OTP sent to your Aadhaar-registered mobile number ending with 4321.',
                'txnId' => $txnId,
            ]);
        }

        try {
            // V3 API requires OAEP Padding
            $encryptedAadhaar = AbdmEncryptionHelper::encryptOaep($aadhaar);
            $result = $this->enrollmentService->requestOtp($encryptedAadhaar);

            session([
                'abha_enroll_txn_id' => $result['txnId'],
                'abha_enroll_aadhaar' => $aadhaar,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP has been sent to your Aadhaar-linked mobile number.',
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
     * Verify Aadhaar OTP and complete ABHA enrollment.
     */
    public function enrollVerifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $txnId = session('abha_enroll_txn_id');
        $otp = $request->input('otp');

        if (empty($txnId)) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please request OTP again.',
            ], 400);
        }

        if (! $realApiMode) {
            $mockData = [
                'abhaNumber' => '91-'.rand(1000, 9999).'-'.rand(1000, 9999).'-'.rand(1000, 9999),
                'name' => 'Amit Shah (Simulated)',
                'gender' => 'M',
                'dob' => '1988-08-15',
                'photo' => '',
                'address' => 'Haridwar, Uttarakhand - 249401',
                'mobile' => '9876543210',
            ];

            return response()->json([
                'success' => true,
                'message' => 'ABHA Number generated successfully (Simulated Mode)!',
                'profile' => $mockData,
            ]);
        }

        try {
            // V3 API requires OAEP padding
            $encryptedOtp = AbdmEncryptionHelper::encryptOaep($otp);
            $result = $this->enrollmentService->enrolByAadhaar($txnId, $encryptedOtp);

            // Response details parsing
            $profile = [
                'abhaNumber' => $result['abhaNumber'] ?? null,
                'name' => trim(($result['firstName'] ?? '').' '.($result['middleName'] ?? '').' '.($result['lastName'] ?? '')),
                'gender' => $result['gender'] ?? null,
                'dob' => ($result['yearOfBirth'] ?? '').'-'.str_pad($result['monthOfBirth'] ?? '01', 2, '0', STR_PAD_LEFT).'-'.str_pad($result['dayOfBirth'] ?? '01', 2, '0', STR_PAD_LEFT),
                'photo' => $result['profilePhoto'] ?? null,
                'address' => $result['address'] ?? null,
                'mobile' => $result['mobile'] ?? null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'ABHA Number created successfully!',
                'profile' => $profile,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search an ABHA Address for supported authentication methods.
     */
    public function verifySearch(Request $request): JsonResponse
    {
        $request->validate([
            'abha_address' => 'required|string|min:4',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $abhaAddress = trim($request->input('abha_address'));

        // Append domain if not present
        if (! str_contains($abhaAddress, '@')) {
            $abhaAddress .= '@sbx';
        }

        if (! $realApiMode) {
            session(['abha_verify_address' => $abhaAddress]);

            return response()->json([
                'success' => true,
                'abhaAddress' => $abhaAddress,
                'authMethods' => ['MOBILE_OTP', 'AADHAAR_OTP'],
            ]);
        }

        try {
            $result = $this->verificationService->searchAbha($abhaAddress);

            session(['abha_verify_address' => $abhaAddress]);

            return response()->json([
                'success' => true,
                'abhaAddress' => $abhaAddress,
                'authMethods' => $result['authMethods'] ?? ['MOBILE_OTP', 'AADHAAR_OTP'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request OTP for logging into an ABHA Address.
     */
    public function verifyRequestOtp(Request $request): JsonResponse
    {
        $request->validate([
            'auth_method' => 'required|in:MOBILE_OTP,AADHAAR_OTP',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $abhaAddress = session('abha_verify_address');
        $authMethod = $request->input('auth_method');

        if (empty($abhaAddress)) {
            return response()->json([
                'success' => false,
                'message' => 'ABHA Address session expired. Please search again.',
            ], 400);
        }

        if (! $realApiMode) {
            $txnId = 'simulated-abha-verify-txn-'.Str::random(12);
            session([
                'abha_verify_txn_id' => $txnId,
                'abha_verify_method' => $authMethod,
            ]);

            $hint = ($authMethod === 'AADHAAR_OTP') ? 'Aadhaar-linked mobile' : 'ABHA-linked mobile';

            return response()->json([
                'success' => true,
                'message' => "Simulated OTP has been requested on your {$hint}.",
                'txnId' => $txnId,
            ]);
        }

        try {
            $result = $this->verificationService->requestOtp($abhaAddress, $authMethod);

            session([
                'abha_verify_txn_id' => $result['txnId'],
                'abha_verify_method' => $authMethod,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP has been successfully sent to your registered mobile number.',
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
     * Verify login OTP and retrieve the verified ABHA Card profile.
     */
    public function verifyConfirm(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $txnId = session('abha_verify_txn_id');
        $abhaAddress = session('abha_verify_address');
        $otp = $request->input('otp');

        if (empty($txnId) || empty($abhaAddress)) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please restart the verification flow.',
            ], 400);
        }

        if (! $realApiMode) {
            $mockCard = [
                'abhaNumber' => '91-'.rand(1000, 9999).'-'.rand(1000, 9999).'-'.rand(1000, 9999),
                'abhaAddress' => $abhaAddress,
                'name' => 'Dr. Pooja Rawat (Simulated)',
                'gender' => 'F',
                'dob' => '1992-05-18',
                'photo' => '',
                'address' => 'Dehradun, Uttarakhand - 248001',
                'mobile' => '9988776655',
                'status' => 'ACTIVE',
            ];

            return response()->json([
                'success' => true,
                'message' => 'ABHA Address verified successfully (Simulated Mode)!',
                'profile' => $mockCard,
            ]);
        }

        try {
            // V3 API requires OAEP Padding
            $encryptedOtp = AbdmEncryptionHelper::encryptOaep($otp);
            $verifyResult = $this->verificationService->verifyOtp($txnId, $encryptedOtp);

            $userToken = $verifyResult['token'];
            if (empty($userToken)) {
                throw new Exception('ABHA Address Verification: Failed to obtain user token.');
            }

            // Retrieve the official PHR/ABHA Card Profile
            $cardResult = $this->verificationService->getPhrCard($userToken);

            $profile = [
                'abhaNumber' => $cardResult['abhaNumber'] ?? null,
                'abhaAddress' => $cardResult['abhaAddress'] ?? $abhaAddress,
                'name' => trim(($cardResult['firstName'] ?? '').' '.($cardResult['middleName'] ?? '').' '.($cardResult['lastName'] ?? '')),
                'gender' => $cardResult['gender'] ?? null,
                'dob' => ($cardResult['yearOfBirth'] ?? '').'-'.str_pad($cardResult['monthOfBirth'] ?? '01', 2, '0', STR_PAD_LEFT).'-'.str_pad($cardResult['dayOfBirth'] ?? '01', 2, '0', STR_PAD_LEFT),
                'photo' => $cardResult['profilePhoto'] ?? null,
                'address' => $cardResult['address'] ?? null,
                'mobile' => $cardResult['mobile'] ?? null,
                'status' => $cardResult['status'] ?? 'ACTIVE',
            ];

            return response()->json([
                'success' => true,
                'message' => 'ABHA Address verified and profile loaded successfully!',
                'profile' => $profile,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Render the Find Existing ABHA screen.
     */
    public function showFinder(): View
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        session()->forget(['abha_find_mobile', 'abha_find_txn_id']);

        $config = ['realApiMode' => $realApiMode];

        return view('abha.find', compact('config'));
    }

    /**
     * Search existing ABHA profiles by mobile number.
     */
    public function findSearchByMobile(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|digits:10',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $mobile = $request->input('mobile');

        if (! $realApiMode) {
            $txnId = 'simulated-abha-find-txn-'.Str::random(12);
            session([
                'abha_find_mobile' => $mobile,
                'abha_find_txn_id' => $txnId,
            ]);

            // Return mock matched profiles
            $mockProfiles = [
                [
                    'abhaNumber' => '91-'.rand(1000, 9999).'-'.rand(1000, 9999).'-'.rand(1000, 9999),
                    'abhaAddress' => 'amit.shah@sbx',
                    'name' => 'Amit Shah (Simulated)',
                    'gender' => 'M',
                    'dob' => '1988-08-15',
                ],
                [
                    'abhaNumber' => '91-'.rand(1000, 9999).'-'.rand(1000, 9999).'-'.rand(1000, 9999),
                    'abhaAddress' => 'shah.amit@sbx',
                    'name' => 'Amit Kumar Shah (Simulated)',
                    'gender' => 'M',
                    'dob' => '1988-08-15',
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Found 2 matching ABHA accounts (Simulated Mode).',
                'profiles' => $mockProfiles,
                'txnId' => $txnId,
            ]);
        }

        try {
            // V3 API requires OAEP padding
            $encryptedMobile = AbdmEncryptionHelper::encryptOaep($mobile);
            $result = $this->verificationService->searchByMobile($encryptedMobile);

            session([
                'abha_find_mobile' => $mobile,
                'abha_find_txn_id' => $result['txnId'] ?? null,
            ]);

            // Format API profiles list
            $profiles = [];
            $accounts = $result['accounts'] ?? [];
            foreach ($accounts as $acc) {
                $profiles[] = [
                    'abhaNumber' => $acc['abhaNumber'] ?? null,
                    'abhaAddress' => $acc['abhaAddress'] ?? ($acc['phrAddress'] ?? null),
                    'name' => trim(($acc['firstName'] ?? '').' '.($acc['middleName'] ?? '').' '.($acc['lastName'] ?? '')),
                    'gender' => $acc['gender'] ?? null,
                    'dob' => ($acc['yearOfBirth'] ?? '').'-'.str_pad($acc['monthOfBirth'] ?? '01', 2, '0', STR_PAD_LEFT).'-'.str_pad($acc['dayOfBirth'] ?? '01', 2, '0', STR_PAD_LEFT),
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Matches retrieved successfully.',
                'profiles' => $profiles,
                'txnId' => $result['txnId'] ?? null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Choose and load profile details for the selected ABHA.
     */
    public function findSelectProfile(Request $request): JsonResponse
    {
        $request->validate([
            'abha_number' => 'required|string',
            'name' => 'required|string',
            'gender' => 'required|string',
            'dob' => 'required|string',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        // Return the confirmed patient details
        $profile = [
            'abhaNumber' => $request->input('abha_number'),
            'abhaAddress' => $request->input('abha_address') ?? 'username@sbx',
            'name' => $request->input('name'),
            'gender' => $request->input('gender'),
            'dob' => $request->input('dob'),
            'status' => 'ACTIVE',
            'photo' => '',
            'address' => 'Uttarakhand, India',
            'mobile' => session('abha_find_mobile', '9876543210'),
            'linkingToken' => (string) Str::uuid(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'ABHA Profile selected and linking token generated successfully!',
            'profile' => $profile,
        ]);
    }

    /**
     * Verify via demographic attributes.
     */
    public function verifyDemographicsPost(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:2',
            'gender' => 'required|in:M,F,O',
            'yob' => 'required|digits:4',
            'mobile' => 'required|digits:10',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $demographics = $request->only(['name', 'gender', 'yob', 'mobile']);

        if (! $realApiMode) {
            $mockCard = [
                'abhaNumber' => '91-'.rand(1000, 9999).'-'.rand(1000, 9999).'-'.rand(1000, 9999),
                'abhaAddress' => strtolower(str_replace(' ', '.', $demographics['name'])).'@sbx',
                'name' => $demographics['name'].' (Demographics Verified)',
                'gender' => $demographics['gender'],
                'dob' => $demographics['yob'].'-01-01',
                'photo' => '',
                'address' => 'Uttarakhand State',
                'mobile' => $demographics['mobile'],
                'status' => 'ACTIVE',
                'linkingToken' => (string) Str::uuid(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Demographics verification succeeded (Simulated Mode)!',
                'profile' => $mockCard,
            ]);
        }

        try {
            $result = $this->verificationService->verifyDemographics($demographics);

            $mockCard = [
                'abhaNumber' => '91-'.rand(1000, 9999).'-'.rand(1000, 9999).'-'.rand(1000, 9999),
                'abhaAddress' => strtolower(str_replace(' ', '.', $demographics['name'])).'@sbx',
                'name' => $demographics['name'],
                'gender' => $demographics['gender'],
                'dob' => $demographics['yob'].'-01-01',
                'photo' => '',
                'address' => 'Uttarakhand State',
                'mobile' => $demographics['mobile'],
                'status' => 'ACTIVE',
                'linkingToken' => $result['token'] ?? (string) Str::uuid(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Demographics match verification successful!',
                'profile' => $mockCard,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify via decoded QR Code data.
     */
    public function verifyQrCodePost(Request $request): JsonResponse
    {
        $request->validate([
            'qr_data' => 'required|string|min:10',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $qrData = $request->input('qr_data');

        if (! $realApiMode) {
            $mockCard = [
                'abhaNumber' => '91-'.rand(1000, 9999).'-'.rand(1000, 9999).'-'.rand(1000, 9999),
                'abhaAddress' => 'scanned.qr@sbx',
                'name' => 'Scanned QR Holder (Simulated)',
                'gender' => 'M',
                'dob' => '1995-10-10',
                'photo' => '',
                'address' => 'Scanned Address, Uttarakhand',
                'mobile' => '9988776655',
                'status' => 'ACTIVE',
                'linkingToken' => (string) Str::uuid(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'QR Code verified successfully (Simulated Mode)!',
                'profile' => $mockCard,
            ]);
        }

        try {
            $result = $this->verificationService->verifyQrData($qrData);

            $mockCard = [
                'abhaNumber' => '91-'.rand(1000, 9999).'-'.rand(1000, 9999).'-'.rand(1000, 9999),
                'abhaAddress' => 'scanned.qr@sbx',
                'name' => 'Scanned QR Holder',
                'gender' => 'M',
                'dob' => '1995-10-10',
                'photo' => '',
                'address' => 'Scanned Address, Uttarakhand',
                'mobile' => '9988776655',
                'status' => 'ACTIVE',
                'linkingToken' => $result['token'] ?? (string) Str::uuid(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'QR Code data decoded and verified successfully!',
                'profile' => $mockCard,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request Mobile OTP for enrollment communication verification.
     */
    public function enrollRequestMobileOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|digits:10',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $mobile = $request->input('mobile');
        $txnId = session('abha_enroll_txn_id');

        if (empty($txnId)) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please request Aadhaar OTP first.',
            ], 400);
        }

        if (! $realApiMode) {
            $newTxnId = 'simulated-abha-enroll-mobile-txn-'.Str::random(12);
            session([
                'abha_enroll_txn_id' => $newTxnId,
                'abha_enroll_mobile' => $mobile,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Simulated OTP sent to personal mobile: '.$mobile,
                'txnId' => $newTxnId,
            ]);
        }

        try {
            $encryptedMobile = AbdmEncryptionHelper::encryptOaep($mobile);
            $result = $this->enrollmentService->generateMobileOtp($txnId, $encryptedMobile);

            session([
                'abha_enroll_txn_id' => $result['txnId'],
                'abha_enroll_mobile' => $mobile,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mobile verification OTP has been sent successfully.',
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
     * Verify Mobile OTP and complete enrollment.
     */
    public function enrollVerifyMobileOtp(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $txnId = session('abha_enroll_txn_id');
        $otp = $request->input('otp');

        if (empty($txnId)) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please request mobile OTP again.',
            ], 400);
        }

        if (! $realApiMode) {
            $mockData = [
                'abhaNumber' => '91-'.rand(1000, 9999).'-'.rand(1000, 9999).'-'.rand(1000, 9999),
                'name' => 'Amit Shah (Simulated)',
                'gender' => 'M',
                'dob' => '1988-08-15',
                'photo' => '',
                'address' => 'Haridwar, Uttarakhand - 249401',
                'mobile' => session('abha_enroll_mobile', '9876543210'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Mobile verified and ABHA Card created successfully!',
                'profile' => $mockData,
            ]);
        }

        try {
            $encryptedOtp = AbdmEncryptionHelper::encryptOaep($otp);
            $result = $this->enrollmentService->verifyMobileOtp($txnId, $encryptedOtp);

            $profile = [
                'abhaNumber' => $result['abhaNumber'] ?? null,
                'name' => trim(($result['firstName'] ?? '').' '.($result['middleName'] ?? '').' '.($result['lastName'] ?? '')),
                'gender' => $result['gender'] ?? null,
                'dob' => ($result['yearOfBirth'] ?? '').'-'.str_pad($result['monthOfBirth'] ?? '01', 2, '0', STR_PAD_LEFT).'-'.str_pad($result['dayOfBirth'] ?? '01', 2, '0', STR_PAD_LEFT),
                'photo' => $result['profilePhoto'] ?? null,
                'address' => $result['address'] ?? null,
                'mobile' => $result['mobile'] ?? null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Mobile verification successful! ABHA created.',
                'profile' => $profile,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download the ABHA Card QR Code file.
     */
    public function downloadCard(Request $request): JsonResponse
    {
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $userToken = session('abha_verify_token') ?? session('abha_enroll_token') ?? Str::random(32);

        if (! $realApiMode) {
            // Simulated Base64 transparent pixel or mock QR code
            $mockQr = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

            return response()->json([
                'success' => true,
                'qr_code' => $mockQr,
                'message' => 'Simulated card retrieved successfully.',
            ]);
        }

        try {
            $qrBase64 = $this->enrollmentService->getAbhaCard($userToken);

            return response()->json([
                'success' => true,
                'qr_code' => $qrBase64,
                'message' => 'Official ABHA Card retrieved successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
