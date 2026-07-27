<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AbhaEnrollmentService;
use App\Services\AbhaVerificationService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AbhaApiController extends Controller
{
    use ApiResponseTrait;

    protected AbhaEnrollmentService $enrollmentService;

    protected AbhaVerificationService $verificationService;

    public function __construct(
        AbhaEnrollmentService $enrollmentService,
        AbhaVerificationService $verificationService
    ) {
        $this->enrollmentService = $enrollmentService;
        $this->verificationService = $verificationService;
    }

    /**
     * Request Aadhaar OTP for ABHA Enrollment.
     */
    public function enrollRequestOtp(Request $request): JsonResponse
    {
        $request->validate([
            'aadhaar' => 'required|digits:12',
        ]);

        $aadhaar = $request->input('aadhaar');
        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            $txnId = 'sim-abha-txn-'.Str::random(8);

            return $this->successResponse([
                'txnId' => $txnId,
                'message' => 'Simulated Aadhaar OTP sent. Use 123456.',
            ], 'OTP requested.');
        }

        try {
            $result = $this->enrollmentService->requestAadhaarOtp($aadhaar);

            return $this->successResponse([
                'txnId' => $result['txnId'] ?? null,
                'message' => $result['message'] ?? 'OTP sent.',
            ], 'OTP requested successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Verify Aadhaar OTP for ABHA Enrollment.
     */
    public function enrollVerifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'txnId' => 'required|string',
            'otp' => 'required|digits:6',
        ]);

        $txnId = $request->input('txnId');
        $otp = $request->input('otp');
        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            if ($otp !== '123456') {
                return $this->errorResponse('Invalid OTP. Use 123456.', 400);
            }

            return $this->successResponse([
                'txnId' => $txnId,
                'abhaNumber' => '91-9876-5432-1098',
                'phrAddress' => 'ramesh.kumar@abdm',
                'name' => 'Ramesh Kumar',
                'gender' => 'M',
                'dob' => '1990-01-01',
                'mobile' => '9876543210',
            ], 'Simulated ABHA creation successful.');
        }

        try {
            $result = $this->enrollmentService->verifyAadhaarOtp($txnId, $otp);

            return $this->successResponse($result, 'ABHA created successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Search ABHA profile by mobile.
     */
    public function findByMobile(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|digits:10',
        ]);

        $mobile = $request->input('mobile');
        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            return $this->successResponse([
                'profiles' => [
                    [
                        'abhaNumber' => '91-9876-5432-1098',
                        'name' => 'Ramesh Kumar',
                        'phrAddress' => 'ramesh.kumar@abdm',
                        'gender' => 'M',
                    ],
                ],
            ], 'Simulated search result.');
        }

        try {
            $result = $this->verificationService->searchByMobile($mobile);

            return $this->successResponse($result, 'Profiles retrieved.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Download ABHA Card details / image payload.
     */
    public function downloadCard(Request $request): JsonResponse
    {
        $request->validate([
            'abhaNumber' => 'required|string',
            'token' => 'nullable|string',
        ]);

        $abhaNumber = $request->input('abhaNumber');
        $token = $request->input('token');
        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            return $this->successResponse([
                'abhaNumber' => $abhaNumber,
                'cardBase64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
                'mimeType' => 'image/png',
            ], 'Simulated ABHA Card retrieved.');
        }

        try {
            $cardData = $this->enrollmentService->downloadCard($token);

            return $this->successResponse($cardData, 'ABHA Card retrieved.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
