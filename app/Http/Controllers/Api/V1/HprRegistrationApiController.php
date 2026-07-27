<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SendAadhaarOtpRequest;
use App\Http\Requests\Api\V1\SendMobileOtpRequest;
use App\Http\Requests\Api\V1\VerifyAadhaarOtpRequest;
use App\Http\Requests\Api\V1\VerifyMobileOtpRequest;
use App\Services\AadhaarOTPService;
use App\Services\HfrFacilityService;
use App\Services\HprAccountService;
use App\Services\HprDocumentService;
use App\Services\MobileOTPService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HprRegistrationApiController extends Controller
{
    use ApiResponseTrait;

    protected AadhaarOTPService $aadhaarService;

    protected MobileOTPService $mobileService;

    protected HprAccountService $hprService;

    protected HfrFacilityService $hfrService;

    protected HprDocumentService $documentService;

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
     * Send OTP to Aadhaar registered mobile number.
     */
    public function sendAadhaarOtp(SendAadhaarOtpRequest $request): JsonResponse
    {
        $aadhaar = $request->input('aadhaar');
        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            $txnId = 'simulated-aadhaar-txn-'.Str::random(10);

            return $this->successResponse([
                'txnId' => $txnId,
                'message' => 'Simulated OTP sent to your registered mobile number ending with 8989.',
            ], 'Aadhaar OTP sent successfully.');
        }

        try {
            $result = $this->aadhaarService->sendOtp($aadhaar);

            return $this->successResponse([
                'txnId' => $result['txnId'] ?? null,
                'message' => $result['message'] ?? 'OTP sent successfully.',
            ], 'Aadhaar OTP sent successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Verify Aadhaar OTP and check if HPR account already exists.
     */
    public function verifyAadhaarOtp(VerifyAadhaarOtpRequest $request): JsonResponse
    {
        $txnId = $request->input('txnId');
        $otp = $request->input('otp');
        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            if ($otp !== '123456') {
                return $this->errorResponse('Invalid OTP. For simulation mode, use OTP 123456.', 400);
            }

            $aadhaarInfo = [
                'name' => 'Dr Ramesh Kumar',
                'gender' => 'M',
                'yearOfBirth' => '1990',
                'firstName' => 'Ramesh',
                'middleName' => '',
                'lastName' => 'Kumar',
                'stateCode' => '27',
                'districtCode' => '472',
                'profilePhoto' => '',
            ];

            return $this->successResponse([
                'txnId' => $txnId,
                'isExistingUser' => false,
                'aadhaarInfo' => $aadhaarInfo,
                'mobile' => '9876543210',
            ], 'Simulated Aadhaar OTP verified successfully.');
        }

        try {
            $result = $this->aadhaarService->verifyOtp($txnId, $otp);
            $currentTxnId = $result['txnId'] ?? $txnId;

            // Check if HPR account already exists
            $hprExistResult = $this->hprService->checkHprIdExists($currentTxnId);

            $isExistingUser = isset($hprExistResult['new']) && $hprExistResult['new'] === false;

            return $this->successResponse([
                'txnId' => $currentTxnId,
                'isExistingUser' => $isExistingUser,
                'aadhaarInfo' => [
                    'name' => $result['name'] ?? $hprExistResult['name'] ?? null,
                    'gender' => $result['gender'] ?? $hprExistResult['gender'] ?? null,
                    'yearOfBirth' => substr($result['dob'] ?? '1990', 0, 4),
                    'firstName' => $result['firstName'] ?? null,
                    'middleName' => $result['middleName'] ?? null,
                    'lastName' => $result['lastName'] ?? null,
                    'stateCode' => $result['stateCode'] ?? null,
                    'districtCode' => $result['districtCode'] ?? null,
                    'profilePhoto' => $result['photo'] ?? null,
                ],
                'mobile' => $result['mobileNumber'] ?? null,
            ], 'Aadhaar OTP verified successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Send Mobile OTP.
     */
    public function sendMobileOtp(SendMobileOtpRequest $request): JsonResponse
    {
        $mobile = $request->input('mobile');
        $txnId = $request->input('txnId', 'sim-txn-'.Str::random(8));
        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            return $this->successResponse([
                'txnId' => $txnId,
                'mobile' => $mobile,
                'message' => "Simulated OTP sent to mobile {$mobile}. Use OTP 123456.",
            ], 'Mobile OTP sent.');
        }

        try {
            $result = $this->mobileService->sendOtp($mobile, $txnId);

            return $this->successResponse([
                'txnId' => $result['txnId'] ?? $txnId,
                'mobile' => $mobile,
            ], 'Mobile OTP sent successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Verify Mobile OTP.
     */
    public function verifyMobileOtp(VerifyMobileOtpRequest $request): JsonResponse
    {
        $txnId = $request->input('txnId');
        $mobile = $request->input('mobile');
        $otp = $request->input('otp');
        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            if ($otp !== '123456') {
                return $this->errorResponse('Invalid OTP. Use 123456.', 400);
            }

            return $this->successResponse([
                'txnId' => $txnId,
                'verified' => true,
                'mobile' => $mobile,
            ], 'Simulated mobile OTP verified successfully.');
        }

        try {
            $result = $this->mobileService->verifyOtp($mobile, $otp, $txnId);

            return $this->successResponse([
                'txnId' => $result['txnId'] ?? $txnId,
                'verified' => true,
                'mobile' => $mobile,
            ], 'Mobile OTP verified successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Get HPR Username Suggestions.
     */
    public function getUsernameSuggestions(Request $request): JsonResponse
    {
        $txnId = $request->input('txnId', 'sim-txn');
        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            return $this->successResponse([
                'suggestions' => ['dr.ramesh.kumar', 'ramesh.k90', 'dr_ramesh_hpr'],
            ], 'Username suggestions generated.');
        }

        try {
            $suggestions = $this->hprService->getHprIdSuggestions($txnId);

            return $this->successResponse([
                'suggestions' => $suggestions,
            ], 'Username suggestions fetched.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Create HPR ID (Healthcare Professional ID).
     */
    public function createHprId(Request $request): JsonResponse
    {
        $request->validate([
            'hprId' => 'required|string',
            'password' => 'required|string|min:8',
            'txnId' => 'required|string',
        ]);

        $hprId = $request->input('hprId');
        $password = $request->input('password');
        $txnId = $request->input('txnId');
        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            return $this->successResponse([
                'hprId' => $hprId,
                'hprIdNumber' => '91-1234-5678-9012',
                'token' => 'simulated-hpr-jwt-token-'.Str::random(16),
            ], 'Simulated HPR ID created successfully.');
        }

        try {
            $result = $this->hprService->createHprId($hprId, $password, $txnId);

            return $this->successResponse([
                'hprId' => $result['hprId'] ?? $hprId,
                'hprIdNumber' => $result['hprIdNumber'] ?? null,
                'token' => $result['token'] ?? null,
            ], 'HPR ID created successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Submit Professional Registration Application.
     */
    public function submitProfessionalRegistration(Request $request): JsonResponse
    {
        $payload = $request->all();
        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            $appNumber = 'HPR-APP-'.strtoupper(Str::random(8));

            return $this->successResponse([
                'applicationNumber' => $appNumber,
                'status' => 'SUBMITTED',
                'submittedAt' => now()->toIso8601String(),
            ], 'Simulated HPR Application submitted successfully.');
        }

        try {
            $result = $this->hprService->registerProfessional($payload);

            return $this->successResponse($result, 'Professional application submitted successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Track HPR Application Status.
     */
    public function trackStatus(Request $request): JsonResponse
    {
        $request->validate([
            'applicationNumber' => 'required|string',
        ]);

        $appNumber = $request->input('applicationNumber');
        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            return $this->successResponse([
                'applicationNumber' => $appNumber,
                'status' => 'UNDER_VERIFICATION',
                'councilName' => 'Medical Council of India',
                'lastUpdated' => now()->toIso8601String(),
            ], 'Simulated application status retrieved.');
        }

        try {
            $status = $this->hprService->trackApplication($appNumber);

            return $this->successResponse($status, 'Application status retrieved successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
