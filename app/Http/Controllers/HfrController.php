<?php

namespace App\Http\Controllers;

use App\Services\HfrFacilityService;
use App\Services\HprAccountService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $hprAuthenticated = session()->has('hpr_reg_hpr_token');
        $loggedInHprId = session('hpr_reg_hpr_id');

        return view('nhpr.hfr', compact('defaultBridgeId', 'loggedInHprId', 'hprAuthenticated'));
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
        if (! session()->has('hpr_reg_hpr_token')) {
            return response()->json([
                'success' => false,
                'message' => 'HPR login or verification is required before registering a facility.',
            ], 401);
        }
        $request->validate([
            'facilityName' => 'required|string|min:3',
            'ownershipCode' => 'required|string',
            'ownershipSubTypeCode' => 'nullable|string',
            'ownershipSubTypeCode2' => 'nullable|string',
            'stateLGDCode' => 'required|string',
            'districtLGDCode' => 'nullable|string',
            'subDistrictLGDCode' => 'nullable|string',
            'address' => 'required|string',
            'address2' => 'nullable|string',
            'pincode' => 'required|digits:6',
            'facilityEmailId' => 'nullable|email',
            'facilityContactNumber' => 'nullable|string',
            'facilityLandlineNumber' => 'nullable|string',
            'facilityStdCode' => 'nullable|string',
            'websiteLink' => 'nullable|url',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'systemOfMedicineCode' => 'nullable|string',
            'facilityTypeCode' => 'nullable|string',
            'typeOfServiceCode' => 'nullable|string',
            'specialityTypeCode' => 'nullable|string',
            'facilityRegion' => 'nullable|string|in:U,R',
            'abpmjayId' => 'nullable|string',
            'ninID' => 'nullable|string',
            'ceaId' => 'nullable|string',
            'hrpSource' => 'nullable|string',
            'hrpSourceFacilityId' => 'nullable|string',
            // Facility Photos
            'facilityBuildingPhotoName' => 'nullable|string',
            'facilityBuildingPhotoValue' => 'nullable|string',
            'facilityBoardPhotoName' => 'nullable|string',
            'facilityBoardPhotoValue' => 'nullable|string',
            // Facility Timings
            'timingsOfFacility' => 'nullable|array',
            'timingsOfFacility.*.workingDays' => 'required_with:timingsOfFacility|string',
            'timingsOfFacility.*.openingHours' => 'required_with:timingsOfFacility|string',
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
                'facilityName', 'ownershipCode', 'ownershipSubTypeCode', 'ownershipSubTypeCode2',
                'stateLGDCode', 'districtLGDCode', 'subDistrictLGDCode', 'address', 'address2', 'pincode',
                'facilityEmailId', 'facilityContactNumber', 'facilityLandlineNumber', 'facilityStdCode',
                'websiteLink', 'latitude', 'longitude', 'systemOfMedicineCode', 'facilityTypeCode',
                'typeOfServiceCode', 'specialityTypeCode', 'facilityRegion',
                'abpmjayId', 'ninID', 'ceaId', 'hrpSource', 'hrpSourceFacilityId',
                // Facility Photos
                'facilityBuildingPhotoName', 'facilityBuildingPhotoValue',
                'facilityBoardPhotoName', 'facilityBoardPhotoValue',
                // Facility Timings
                'timingsOfFacility',
            ]);

            $result = $this->hfrService->createFacility($data);

            return response()->json([
                'success'     => true,
                'facilityId'  => $result['facilityId'],
                'facilityName'=> $result['facilityName'],
                'trackingId'  => $result['trackingId'] ?? null,
                'status'      => $result['status'] ?? 'PENDING_APPROVAL',
                'message'     => $result['message'],
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

    /**
     * Authenticate HPR credentials and cache the token in the session.
     */
    public function hprLogin(Request $request, HprAccountService $hprService): JsonResponse
    {
        $request->validate([
            'hpr_id' => 'required_without:mobile|nullable|string',
            'mobile' => 'required_if:auth_method,MOBILE_OTP|nullable|digits:10',
            'auth_method' => 'required|string|in:PASSWORD,MOBILE_OTP,AADHAAR_OTP,OTP',
            'password' => 'required_if:auth_method,PASSWORD|nullable|string',
            'otp' => 'required_if:auth_method,AADHAAR_OTP,OTP|nullable|digits:6',
            'txn_id' => 'required_if:auth_method,MOBILE_OTP,AADHAAR_OTP,OTP|nullable|string',
            'selected_hpr_id' => 'required_if:auth_method,MOBILE_OTP|nullable|string',
        ]);

        $authMethod = $request->input('auth_method');
        $hprId = trim($request->input('selected_hpr_id') ?: $request->input('hpr_id') ?: '');
        $otp = $request->input('otp');
        $password = $request->input('password');
        $txnId = $request->input('txn_id');

        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if ($realApiMode) {
            try {
                if ($authMethod === 'PASSWORD') {
                    $authResponse = $hprService->confirmHprAuthWithPassword($hprId, $password);
                    $hprToken = $authResponse['token'] ?? null;
                } elseif ($authMethod === 'MOBILE_OTP') {
                    $authResponse = $hprService->loginWithHprId($hprId, $txnId);
                    $hprToken = $authResponse['token'] ?? null;
                } elseif ($authMethod === 'AADHAAR_OTP') {
                    $authResponse = $hprService->confirmHprAuthWithAadhaarOtp($txnId, $otp);
                    $hprToken = $authResponse['token'] ?? null;
                } else {
                    $authResponse = $hprService->confirmHprAuthWithOtp($txnId, $otp);
                    $hprToken = $authResponse['token'] ?? null;
                }

                if (empty($hprToken)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Authentication succeeded but no HPR access token was returned.',
                    ], 500);
                }

                // Cache HPR Token in session
                session(['hpr_reg_hpr_token' => $hprToken]);
                session(['hpr_reg_hpr_id' => $hprId]);

                return response()->json([
                    'success' => true,
                    'message' => "Successfully authenticated HPR ID {$hprId}!",
                    'hpr_id' => $hprId,
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication failed: '.$e->getMessage(),
                ], 500);
            }
        }

        // Simulated Mode fallback
        if ($authMethod === 'PASSWORD' && strtolower($password) === 'wrong') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid HPR credentials / password. Please try again.',
            ], 422);
        }

        if (($authMethod === 'AADHAAR_OTP' || $authMethod === 'OTP') && $otp !== '123456') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please enter 123456 for simulated verification.',
            ], 422);
        }

        $mockToken = 'simulated-jwt-token-'.Str::random(10);
        session(['hpr_reg_hpr_token' => $mockToken]);
        session(['hpr_reg_hpr_id' => $hprId]);

        return response()->json([
            'success' => true,
            'message' => "HPR ID {$hprId} successfully authenticated (Simulated Mode)!",
            'hpr_id' => $hprId,
        ]);
    }

    /**
     * Clear the HPR session cache.
     */
    public function hprLogout(): JsonResponse
    {
        session()->forget(['hpr_reg_hpr_token', 'hpr_reg_hpr_id']);

        return response()->json([
            'success' => true,
            'message' => 'HPR session cleared successfully.',
        ]);
    }

    /**
     * Fetch all HFR master types.
     */
    public function masterTypes(Request $request): JsonResponse
    {
        try {
            $types = $this->hfrService->getMasterTypes();

            return response()->json([
                'success' => true,
                'data' => $types['masterTypes'] ?? [],
            ]);
        } catch (Exception $e) {
            Log::error('HFR Controller - masterTypes: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch master data values for a specific type.
     */
    public function masterData(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
        ]);

        try {
            $type = $request->input('type');
            $result = $this->hfrService->getMasterData($type);

            return response()->json([
                'success' => true,
                'type' => $result['type'] ?? $type,
                'data' => $result['data'] ?? [],
            ]);
        } catch (Exception $e) {
            Log::error('HFR Controller - masterData: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch all LGD states.
     */
    public function lgdStates(Request $request): JsonResponse
    {
        try {
            $states = $this->hfrService->getLgdStates();

            return response()->json([
                'success' => true,
                'data' => $states,
            ]);
        } catch (Exception $e) {
            Log::error('HFR Controller - lgdStates: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch LGD districts for a state.
     */
    public function lgdDistricts(Request $request): JsonResponse
    {
        $request->validate([
            'stateCode' => 'required|string',
        ]);

        try {
            $districts = $this->hfrService->getLgdDistricts($request->input('stateCode'));

            return response()->json([
                'success' => true,
                'data' => $districts,
            ]);
        } catch (Exception $e) {
            Log::error('HFR Controller - lgdDistricts: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch LGD subdistricts for a district.
     */
    public function lgdSubdistricts(Request $request): JsonResponse
    {
        $request->validate([
            'districtCode' => 'required|string',
        ]);

        try {
            $subdistricts = $this->hfrService->getLgdSubdistricts($request->input('districtCode'));

            return response()->json([
                'success' => true,
                'data' => $subdistricts,
            ]);
        } catch (Exception $e) {
            Log::error('HFR Controller - lgdSubdistricts: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch facility types.
     */
    public function fetchFacilityTypes(Request $request): JsonResponse
    {
        $request->validate([
            'ownershipCode' => 'required|string',
            'systemOfMedicineCode' => 'required|string',
        ]);

        try {
            $result = $this->hfrService->fetchFacilityType(
                $request->input('ownershipCode'),
                $request->input('systemOfMedicineCode')
            );

            return response()->json([
                'success' => true,
                'data' => $result['data'] ?? [],
            ]);
        } catch (Exception $e) {
            Log::error('HFR Controller - fetchFacilityTypes: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch owner subtypes.
     */
    public function getOwnerSubtypes(Request $request): JsonResponse
    {
        $request->validate([
            'ownershipCode' => 'required|string',
            'ownerSubtypeCode' => 'nullable|string',
        ]);

        try {
            $result = $this->hfrService->getOwnerSubtype(
                $request->input('ownershipCode'),
                $request->input('ownerSubtypeCode')
            );

            return response()->json([
                'success' => true,
                'data' => $result['data'] ?? [],
            ]);
        } catch (Exception $e) {
            Log::error('HFR Controller - getOwnerSubtypes: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch specialties.
     */
    public function getSpecialities(Request $request): JsonResponse
    {
        $request->validate([
            'systemOfMedicineCode' => 'required|string',
        ]);

        try {
            $result = $this->hfrService->getSpecialities($request->input('systemOfMedicineCode'));

            return response()->json([
                'success' => true,
                'data' => $result['data'] ?? [],
            ]);
        } catch (Exception $e) {
            Log::error('HFR Controller - getSpecialities: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch facility subtypes.
     */
    public function fetchFacilitySubtypes(Request $request): JsonResponse
    {
        $request->validate([
            'facilityTypeCode' => 'required|string',
        ]);

        try {
            $result = $this->hfrService->fetchFacilitySubtype($request->input('facilityTypeCode'));

            return response()->json([
                'success' => true,
                'data' => $result['data'] ?? [],
            ]);
        } catch (Exception $e) {
            Log::error('HFR Controller - fetchFacilitySubtypes: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track / look up a registered health facility by its HFR Facility ID.
     *
     * The HFR API does not expose a status endpoint for the draft trackingId.
     * Once a facility is submitted, it receives an IN-prefixed facilityId that
     * can be queried via the documented facility/search endpoint.
     */
    public function trackFacility(Request $request): JsonResponse
    {
        $request->validate([
            'facilityId' => 'required|string|min:3',
        ]);

        $facilityId  = trim($request->input('facilityId'));
        $realApiMode = session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false));

        if (! $realApiMode) {
            return response()->json([
                'success'    => true,
                'facilityId' => $facilityId,
                'status'     => 'Submitted',
                'message'    => 'Facility found in HFR registry (Simulated Mode).',
                'facility'   => [
                    'facilityId'   => $facilityId,
                    'facilityName' => 'Sample Hospital (Simulated)',
                    'facilityStatus' => 'Submitted',
                    'ownership'    => 'PRIVATE',
                    'facilityType' => 'Hospital',
                    'stateName'    => 'Gujarat',
                    'districtName' => 'Ahmedabad',
                    'address'      => 'Vejalpur, Ahmedabad',
                    'pincode'      => '380051',
                ],
            ]);
        }

        try {
            $result = $this->hfrService->lookupFacilityById($facilityId);

            $facility = $result['facilities'][0] ?? null;

            if (! $facility) {
                return response()->json([
                    'success' => false,
                    'message' => "No facility found for ID: {$facilityId}",
                ], 404);
            }

            return response()->json([
                'success'    => true,
                'facilityId' => $facilityId,
                'status'     => $facility['facilityStatus'] ?? 'Unknown',
                'message'    => $result['message'] ?? 'Facility found.',
                'facility'   => $facility,
            ]);
        } catch (Exception $e) {
            Log::error('HFR Controller - trackFacility: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
