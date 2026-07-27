<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\HfrFacilityService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HfrApiController extends Controller
{
    use ApiResponseTrait;

    protected HfrFacilityService $hfrService;

    public function __construct(HfrFacilityService $hfrService)
    {
        $this->hfrService = $hfrService;
    }

    /**
     * Search Health Facilities by name, state, district or facility type.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query');
        $stateCode = $request->input('stateCode');
        $districtCode = $request->input('districtCode');
        $facilityType = $request->input('facilityType');

        try {
            $facilities = $this->hfrService->searchFacilities($query, $stateCode, $districtCode, $facilityType);

            return $this->successResponse([
                'facilities' => $facilities,
            ], 'Facilities fetched successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Fetch LGD States master data.
     */
    public function getStates(): JsonResponse
    {
        try {
            $states = $this->hfrService->getLgdStates();

            return $this->successResponse([
                'states' => $states,
            ], 'States master data retrieved.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Fetch LGD Districts by State Code.
     */
    public function getDistricts(Request $request): JsonResponse
    {
        $request->validate([
            'stateCode' => 'required',
        ]);

        try {
            $districts = $this->hfrService->getLgdDistricts($request->input('stateCode'));

            return $this->successResponse([
                'districts' => $districts,
            ], 'Districts master data retrieved.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Fetch Facility Types.
     */
    public function getFacilityTypes(): JsonResponse
    {
        try {
            $types = $this->hfrService->getFacilityTypes();

            return $this->successResponse([
                'facilityTypes' => $types,
            ], 'Facility types retrieved.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Fetch Specialities.
     */
    public function getSpecialities(): JsonResponse
    {
        try {
            $specialities = $this->hfrService->getSpecialities();

            return $this->successResponse([
                'specialities' => $specialities,
            ], 'Specialities master data retrieved.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
