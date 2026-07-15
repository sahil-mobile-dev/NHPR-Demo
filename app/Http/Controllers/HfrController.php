<?php

namespace App\Http\Controllers;

use App\Services\HfrFacilityService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Class HfrController
 *
 * Coordinates HFR Facility management including searching, registering new facilities,
 * and linking HRP bridges or HPR professionals to registered facilities.
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
        $loggedInHprId = session('hpr_reg_hpr_id', 'practitioner@hpr.abdm');

        return view('nhpr.hfr', compact('defaultBridgeId', 'loggedInHprId'));
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
            'stateLGDCode' => 'required|string',
            'districtLGDCode' => 'nullable|string',
            'subDistrictLGDCode' => 'nullable|string',
            'address' => 'required|string',
            'pincode' => 'nullable|string',
            'facilityEmailId' => 'nullable|email',
            'facilityContactNumber' => 'nullable|string',
            'facilityLandlineNumber' => 'nullable|string',
            'facilityStdCode' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'systemOfMedicineCode' => 'nullable|string',
            'facilityTypeCode' => 'nullable|string',
            'abpmjayId' => 'nullable|string',
            'ninID' => 'nullable|string',
            'ceaId' => 'nullable|string',
            'hrpSource' => 'nullable|string',
            'hrpSourceFacilityId' => 'nullable|string',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            $simulatedId = 'IN'.rand(1000000000, 9999999999);

            return response()->json([
                'success' => true,
                'facilityId' => $simulatedId,
                'facilityName' => $request->input('facilityName'),
                'status' => 'PENDING_APPROVAL',
                'message' => 'Facility registered successfully (Simulated Mode)!',
            ]);
        }

        try {
            $data = $request->only([
                'facilityName', 'ownershipCode', 'stateLGDCode', 'districtLGDCode',
                'subDistrictLGDCode', 'address', 'pincode', 'facilityEmailId',
                'facilityContactNumber', 'facilityLandlineNumber', 'facilityStdCode',
                'latitude', 'longitude', 'systemOfMedicineCode', 'facilityTypeCode',
                'abpmjayId', 'ninID', 'ceaId', 'hrpSource', 'hrpSourceFacilityId',
            ]);

            $result = $this->hfrService->createFacility($data);

            return response()->json([
                'success' => true,
                'facilityId' => $result['facilityId'],
                'facilityName' => $result['facilityName'],
                'status' => $result['status'] ?? 'PENDING_APPROVAL',
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
     * Link HFR facility to the logged-in HPR ID / Facility Manager.
     */
    public function linkBridge(Request $request): JsonResponse
    {
        $request->validate([
            'facilityId' => 'required|string',
            'facilityName' => 'required|string',
            'hprId' => 'required_without:bridgeId|nullable|string',
            'bridgeId' => 'required_without:hprId|nullable|string',
            'hprToken' => 'nullable|string',
        ]);

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));
        $facilityId = $request->input('facilityId');
        $facilityName = $request->input('facilityName');

        if ($request->has('hprId') && ! empty($request->input('hprId'))) {
            $hprId = $request->input('hprId');

            if (! $realApiMode) {
                return response()->json([
                    'success' => true,
                    'message' => "Facility {$facilityName} (ID: {$facilityId}) has been successfully linked to HPR ID / Facility Manager {$hprId} (Simulated Mode)!",
                ]);
            }

            try {
                $hprToken = $request->input('hprToken') ?: session('hpr_reg_hpr_token');
                $isMockToken = empty($hprToken) || str_starts_with($hprToken, 'mock') || str_starts_with($hprToken, 'simulated') || str_starts_with($hprToken, 'session');

                if ($isMockToken) {
                    return response()->json([
                        'success' => true,
                        'message' => "Successfully linked HPR ID {$hprId} to facility {$facilityName}!",
                        'referenceNumber' => 'LINK-SIM-'.strtoupper(Str::random(10)),
                    ]);
                }

                // In real mode, map the professional to the facility using the registerProfessional API endpoint
                $payload = [
                    'hprToken' => $hprToken,
                    'practitioner' => [
                        'healthProfessionalType' => 'doctor',
                        'apiClientId' => '',
                        'personalInformation' => [
                            'nationality' => '356',
                        ],
                        'currentWorkDetails' => [
                            'currentlyWorking' => '1',
                            'purposeOfWork' => 'Practice',
                            'chooseWorkStatus' => '1',
                            'facilityDeclarationData' => [
                                'facilityId' => $facilityId,
                                'facilityName' => $facilityName,
                                'facilityAddress' => $request->input('facilityAddress', 'Dehradun'),
                                'facilityPincode' => $request->input('facilityPincode', '248001'),
                                'state' => '05', // Uttarakhand
                                'district' => '060',
                                'facilityType' => 'Hospital',
                            ],
                        ],
                    ],
                ];

                $result = $this->hfrService->registerProfessional($payload);

                return response()->json([
                    'success' => true,
                    'message' => "Successfully linked HPR ID {$hprId} to facility {$facilityName}!",
                    'referenceNumber' => $result['referenceNumber'] ?? 'LINK-'.strtoupper(Str::random(10)),
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
        } else {
            // Legacy/Test Case for Bridge ID linkage
            $bridgeId = $request->input('bridgeId');

            if (! $realApiMode) {
                return response()->json([
                    'success' => true,
                    'message' => "Facility {$facilityId} successfully linked to bridge software {$bridgeId} (Simulated Mode)!",
                ]);
            }

            try {
                $data = [
                    'facilityId' => $facilityId,
                    'facilityName' => $facilityName,
                    'HRP' => [
                        [
                            'bridgeId' => $bridgeId,
                            'hipName' => $facilityName,
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
}
