<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\GatewayTokenService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NhprApiController extends Controller
{
    use ApiResponseTrait;

    protected GatewayTokenService $tokenService;

    public function __construct(GatewayTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Get or generate ABDM Gateway Access Token.
     */
    public function token(): JsonResponse
    {
        try {
            $token = $this->tokenService->getValidToken();

            return $this->successResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 1200,
            ], 'Gateway token fetched successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to generate gateway token: '.$e->getMessage(), 500);
        }
    }

    /**
     * Save temporary Client ID & Secret for API testing.
     */
    public function saveCredentials(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
        ]);

        try {
            $this->tokenService->saveCustomCredentials($validated['client_id'], $validated['client_secret']);

            return $this->successResponse(null, 'Custom ABDM API credentials updated successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Clear saved custom credentials.
     */
    public function clearCredentials(): JsonResponse
    {
        $this->tokenService->clearCustomCredentials();

        return $this->successResponse(null, 'Custom credentials cleared.');
    }
}
