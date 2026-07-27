<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\HiuConsentService;
use App\Services\HiuHealthInformationService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HiuApiController extends Controller
{
    use ApiResponseTrait;

    protected HiuConsentService $consentService;

    protected HiuHealthInformationService $healthInfoService;

    public function __construct(
        HiuConsentService $consentService,
        HiuHealthInformationService $healthInfoService
    ) {
        $this->consentService = $consentService;
        $this->healthInfoService = $healthInfoService;
    }

    /**
     * Request consent from patient for health records access.
     */
    public function requestConsent(Request $request): JsonResponse
    {
        $request->validate([
            'patientAbhaId' => 'required|string',
            'purpose' => 'required|string',
            'hiTypes' => 'required|array',
        ]);

        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            return $this->successResponse([
                'consentRequestId' => 'sim-consent-req-'.uniqid(),
                'status' => 'REQUESTED',
                'patientAbhaId' => $request->input('patientAbhaId'),
            ], 'Simulated consent requested.');
        }

        try {
            $result = $this->consentService->createConsentRequest(
                $request->input('patientAbhaId'),
                $request->input('purpose'),
                $request->input('hiTypes')
            );

            return $this->successResponse($result, 'Consent request created.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Request health information using granted consent ID.
     */
    public function requestHealthData(Request $request): JsonResponse
    {
        $request->validate([
            'consentArtifactId' => 'required|string',
        ]);

        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            return $this->successResponse([
                'transactionId' => 'sim-hi-tx-'.uniqid(),
                'consentArtifactId' => $request->input('consentArtifactId'),
                'status' => 'REQUESTED',
            ], 'Simulated health information request initiated.');
        }

        try {
            $result = $this->healthInfoService->requestHealthInformation($request->input('consentArtifactId'));

            return $this->successResponse($result, 'Health information requested.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
