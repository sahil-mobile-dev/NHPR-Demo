<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\HipLinkingService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HipApiController extends Controller
{
    use ApiResponseTrait;

    protected HipLinkingService $hipService;

    public function __construct(HipLinkingService $hipService)
    {
        $this->hipService = $hipService;
    }

    /**
     * Create patient record and link care context.
     */
    public function linkCareContext(Request $request): JsonResponse
    {
        $request->validate([
            'patientAbhaId' => 'required|string',
            'careContextRef' => 'required|string',
            'display' => 'required|string',
        ]);

        $realApiMode = config('services.nhpr.real_api_mode', false);

        if (! $realApiMode) {
            return $this->successResponse([
                'status' => 'LINKED',
                'careContextRef' => $request->input('careContextRef'),
                'abhaId' => $request->input('patientAbhaId'),
            ], 'Simulated care context linked successfully.');
        }

        try {
            $result = $this->hipService->linkCareContext(
                $request->input('patientAbhaId'),
                $request->input('careContextRef'),
                $request->input('display')
            );

            return $this->successResponse($result, 'Care context linked successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
