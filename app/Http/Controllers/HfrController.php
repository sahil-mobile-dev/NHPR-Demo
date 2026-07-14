<?php

namespace App\Http\Controllers;

use App\Services\HfrFacilityService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class HfrController
 *
 * Coordinates HFR Facility management including searching, registering new facilities,
 * and linking HRP bridges to registered facilities.
 */
class HfrController extends Controller
{
    protected HfrFacilityService $hfrService;

    /**
     * HfrController constructor.
     */
    public function __construct(HfrFacilityService $hfrService)
    {
        $this->hfrService = $hfrService;
    }

    /**
     * Display the HFR Dashboard / Management Portal.
     */
    public function index(): View
    {
        $defaultBridgeId = session('nhpr_credential_client_id', config('services.nhpr.client_id', ''));

        return view('nhpr.hfr', compact('defaultBridgeId'));
    }

    /**
     * Search facilities in HFR registry.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'facilityName' => 'required_without:facilityId|nullable|string',
            'pincode' => 'nullable|digits:6',
            'facilityId' => 'nullable|string',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            $facilities = [
                [
                    'facilityId' => 'IN3310001245',
                    'facilityName' => 'ABC Hospital (Simulated)',
                    'address' => 'No 510 south street koyambedu',
                    'pincode' => '600107',
                    'stateName' => 'Tamil Nadu',
                ],
                [
                    'facilityId' => 'IN2710000059',
                    'facilityName' => 'Dehradun Civil Hospital (Simulated)',
                    'address' => 'EC Road, Dehradun',
                    'pincode' => $request->input('pincode') ?: '248001',
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

            // If searching without facilityId, HFR API requires ownershipCode and stateLGDCode
            if (empty($searchParams['facilityId'])) {
                $searchParams['ownershipCode'] = 'P'; // Default: Private
                $searchParams['stateLGDCode'] = '05';  // Default: Uttarakhand LGD Code
            }

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
     * Create a new facility registration in HFR.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'facilityName' => 'required|string|min:3',
            'ownershipCode' => 'required|string',
            'systemOfMedicineCode' => 'required|string',
            'facilityTypeCode' => 'required|string',
            'pincode' => 'required|digits:6',
            'stateLGDCode' => 'required|string',
            'districtLGDCode' => 'required|string',
            'facilityAddress' => 'required|string',
            'contactNumber' => 'required|digits:10',
            'email' => 'required|email',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            $simulatedId = 'IN'.rand(1000000000, 9999999999);

            return response()->json([
                'success' => true,
                'facilityId' => $simulatedId,
                'facilityName' => $request->input('facilityName'),
                'message' => 'Facility registered successfully (Simulated Mode)!',
            ]);
        }

        try {
            $data = $request->only([
                'facilityName', 'ownershipCode', 'systemOfMedicineCode',
                'facilityTypeCode', 'pincode', 'stateLGDCode', 'districtLGDCode',
                'facilityAddress', 'contactNumber', 'email',
            ]);

            $result = $this->hfrService->createFacility($data);

            return response()->json([
                'success' => true,
                'facilityId' => $result['facilityId'],
                'facilityName' => $result['facilityName'],
                'message' => $result['message'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Link bridge / HRP software to health facility.
     */
    public function linkBridge(Request $request): JsonResponse
    {
        $request->validate([
            'facilityId' => 'required|string',
            'facilityName' => 'required|string',
            'bridgeId' => 'required|string',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            return response()->json([
                'success' => true,
                'message' => 'Facility '.$request->input('facilityId').' successfully linked to bridge software '.$request->input('bridgeId').' (Simulated Mode)!',
            ]);
        }

        try {
            $data = [
                'facilityId' => $request->input('facilityId'),
                'facilityName' => $request->input('facilityName'),
                'HRP' => [
                    [
                        'bridgeId' => $request->input('bridgeId'),
                        'hipName' => $request->input('facilityName'),
                        'type' => 'HIP',
                        'active' => true,
                    ],
                ],
            ];

            $result = $this->hfrService->linkBridgeToFacility($data);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
