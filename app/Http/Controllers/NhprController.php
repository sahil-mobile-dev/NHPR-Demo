<?php

namespace App\Http\Controllers;

use App\Services\GatewayTokenService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class NhprController
 *
 * Coordinates Gateway Token operations: rendering the credentials configuration,
 * saving credentials, clearing configuration, and triggering token generation.
 */
class NhprController extends Controller
{
    protected GatewayTokenService $tokenService;

    /**
     * NhprController constructor.
     */
    public function __construct(GatewayTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Display the token generation and monitoring interface.
     */
    public function show(): View
    {
        $tokenData = $this->tokenService->getCachedMetadata();

        // Read configuration values (session-based first, then fallback to services config)
        $config = [
            'baseUrl' => session('nhpr_credential_base_url', config('services.nhpr.base_url')),
            'apiUrl' => session('nhpr_credential_api_url', config('services.nhpr.api_url')),
            'xCmId' => session('nhpr_credential_x_cm_id', config('services.nhpr.x_cm_id')),
            'clientId' => session('nhpr_credential_client_id', config('services.nhpr.client_id')),
            'clientSecret' => session('nhpr_credential_client_secret', config('services.nhpr.client_secret')),
        ];

        $config['isConfigured'] = ! empty($config['clientId']) && ! empty($config['clientSecret']);

        return view('nhpr.token', compact('tokenData', 'config'));
    }

    /**
     * Save dynamic credentials in the user session.
     */
    public function saveCredentials(Request $request): JsonResponse
    {
        $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'base_url' => 'required|url',
            'api_url' => 'required|url',
            'x_cm_id' => 'required|string',
        ]);

        session([
            'nhpr_credential_client_id' => $request->input('client_id'),
            'nhpr_credential_client_secret' => $request->input('client_secret'),
            'nhpr_credential_base_url' => $request->input('base_url'),
            'nhpr_credential_api_url' => $request->input('api_url'),
            'nhpr_credential_x_cm_id' => $request->input('x_cm_id'),
        ]);

        // Clear active token cache when credentials change
        $this->tokenService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Credentials saved to session and active token cache cleared.',
        ]);
    }

    /**
     * Clear custom session credentials and token cache.
     */
    public function clearCredentials(): JsonResponse
    {
        session()->forget([
            'nhpr_credential_client_id',
            'nhpr_credential_client_secret',
            'nhpr_credential_base_url',
            'nhpr_credential_api_url',
            'nhpr_credential_x_cm_id',
        ]);

        $this->tokenService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Custom credentials cleared and token cache reset.',
        ]);
    }

    /**
     * Handle the POST request to manually generate or refresh the Gateway token.
     * Returns a JSON response for dynamic UI updates.
     */
    public function generate(): JsonResponse
    {
        try {
            $tokenData = $this->tokenService->generateToken(true);

            return response()->json([
                'success' => true,
                'message' => 'NHPR Gateway Access Token generated successfully!',
                'data' => [
                    'accessToken' => $tokenData['accessToken'],
                    'refreshToken' => $tokenData['refreshToken'],
                    'expiresIn' => $tokenData['expiresIn'],
                    'tokenType' => $tokenData['tokenType'],
                    'generatedAt' => $tokenData['generatedAt'],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
