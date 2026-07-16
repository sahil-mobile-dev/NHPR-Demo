<?php

namespace Tests\Feature;

use App\Services\GatewayTokenService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Class HfrManagementTest
 *
 * Validates the HFR Facility management flows including index view rendering,
 * searching facilities, registering new facilities, and bridge linkages.
 */
class HfrManagementTest extends TestCase
{
    /**
     * Set up tests, priming the gateway token cache and configurations.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.nhpr.base_url' => 'https://mock.abdm.gov.in',
            'services.nhpr.api_url' => 'https://mock-api.abdm.gov.in',
            'services.nhpr.hfr_api_url' => 'https://mock-hfr.abdm.gov.in',
            'services.nhpr.client_id' => 'mock-client-id',
            'services.nhpr.client_secret' => 'mock-client-secret',
            'services.nhpr.x_cm_id' => 'sbx',
            'services.nhpr.real_api_mode' => true,
        ]);

        // Prime cache with valid gateway token
        Cache::put(GatewayTokenService::CACHE_KEY_TOKEN, 'mock-gateway-access-token-999', 3600);
        Cache::put(GatewayTokenService::CACHE_KEY_METADATA, ['expires_at' => now()->addMinutes(50)->timestamp], 3600);
    }

    /**
     * Test the HFR Dashboard renders successfully.
     */
    public function test_hfr_dashboard_renders_successfully(): void
    {
        $response = $this->get(route('nhpr.hfr.index'));

        $response->assertStatus(200);
        $response->assertViewIs('nhpr.hfr');
        $response->assertSee('Health Facility Registry (HFR) Portal');
    }

    /**
     * Test searching facilities in simulated mode.
     */
    public function test_search_facility_simulated(): void
    {
        // Toggle live mode off in session
        session(['nhpr_real_api_mode' => false]);

        $response = $this->postJson(route('nhpr.hfr.search'), [
            'facilityName' => 'Dehradun',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(2, 'facilities');
        $response->assertJsonPath('facilities.0.facilityId', 'IN3310001245');
    }

    /**
     * Test searching facilities in real API mode (mocked).
     */
    public function test_search_facility_real_success(): void
    {
        session(['nhpr_real_api_mode' => true]);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/FacilityManagement/v1.5/facility/search' => Http::response([
                'facilities' => [
                    [
                        'facilityId' => 'IN2710001111',
                        'facilityName' => 'Live Test Hospital',
                        'address' => 'Mall Road, Dehradun',
                        'pincode' => '248001',
                        'stateName' => 'Uttarakhand',
                    ],
                ],
                'totalFacilities' => 1,
                'numberOfPages' => 1,
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.hfr.search'), [
            'facilityName' => 'Live Test',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'facilities');
        $response->assertJsonPath('facilities.0.facilityId', 'IN2710001111');
    }

    public function test_create_facility_fails_without_hpr_session(): void
    {
        session(['nhpr_real_api_mode' => false]);

        $payload = [
            'facilityName' => 'No Session Clinic',
            'ownershipCode' => 'P',
            'stateLGDCode' => '05',
            'address' => 'Rajpur Road, Dehradun',
        ];

        $response = $this->postJson(route('nhpr.hfr.create'), $payload);

        $response->assertStatus(401);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'HPR login or verification is required before registering a facility.');
    }

    public function test_create_facility_simulated(): void
    {
        session(['nhpr_real_api_mode' => false]);

        $payload = [
            'facilityName' => 'New Uttarakhand Clinic',
            'ownershipCode' => 'P',
            'systemOfMedicineCode' => 'M',
            'facilityTypeCode' => 'CLINIC',
            'pincode' => '248001',
            'stateLGDCode' => '05',
            'districtLGDCode' => '060',
            'address' => 'Rajpur Road, Dehradun',
            'facilityContactNumber' => '9876543210',
            'facilityEmailId' => 'contact@utclinic.org',
        ];

        $response = $this->withSession([
            'hpr_reg_hpr_token' => 'mock-hpr-token-jwt-111',
            'hpr_reg_hpr_id' => 'practitioner@hpr.abdm',
        ])->postJson(route('nhpr.hfr.create'), $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('facilityName', 'New Uttarakhand Clinic');
        $this->assertStringStartsWith('IN', $response->json('facilityId'));
    }

    /**
     * Test registering new facility in real API mode (mocked).
     */
    public function test_create_facility_real_success(): void
    {
        session(['nhpr_real_api_mode' => true]);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/v1.5/facility/basic-information' => Http::response([
                'trackingId' => 'mock-tracking-id-123',
                'status' => 'Created',
            ], 200),
            'https://mock-api.abdm.gov.in/v4/int/v1.5/facility/submit-facility' => Http::response([
                'facilityId' => 'IN2710002222',
                'status' => 'Created',
                'message' => 'Facility registered and submitted successfully.',
            ], 200),
        ]);

        $payload = [
            'facilityName' => 'Live Created Clinic',
            'ownershipCode' => 'P',
            'systemOfMedicineCode' => 'M',
            'facilityTypeCode' => 'CLINIC',
            'pincode' => '248001',
            'stateLGDCode' => '05',
            'districtLGDCode' => '060',
            'address' => 'Rajpur Road, Dehradun',
            'facilityContactNumber' => '9876543210',
            'facilityEmailId' => 'contact@utclinic.org',
        ];

        $response = $this->withSession([
            'hpr_reg_hpr_token' => 'mock-hpr-token-jwt-111',
            'hpr_reg_hpr_id' => 'practitioner@hpr.abdm',
        ])->postJson(route('nhpr.hfr.create'), $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('facilityId', 'IN2710002222');
        $response->assertJsonPath('facilityName', 'Live Created Clinic');
    }

    /**
     * Test bridge linkage in simulated mode.
     */
    public function test_link_bridge_simulated(): void
    {
        session(['nhpr_real_api_mode' => false]);

        $response = $this->postJson(route('nhpr.hfr.link'), [
            'facilityId' => 'IN2710000059',
            'facilityName' => 'Dehradun Civil Hospital',
            'bridgeId' => 'test-bridge-id',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertSee('test-bridge-id');
    }

    /**
     * Test bridge linkage in real API mode (mocked).
     */
    public function test_link_bridge_real_success(): void
    {
        session(['nhpr_real_api_mode' => true]);

        Http::fake([
            'https://mock-api.abdm.gov.in/v1/bridges/MutipleHRPAddUpdateServices' => Http::response([
                'message' => 'Linked bridge to facility successfully.',
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.hfr.link'), [
            'facilityId' => 'IN2710000059',
            'facilityName' => 'Dehradun Civil Hospital',
            'bridgeId' => 'test-bridge-id',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Linked bridge to facility successfully.');
    }

    /**
     * Test HFR master data types in simulated and real modes.
     */
    public function test_masters_types_endpoint(): void
    {
        // 1. Simulated
        session(['nhpr_real_api_mode' => false]);
        $response = $this->getJson(route('nhpr.hfr.masters.types'));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure(['success', 'data']);
        $response->assertJsonFragment(['type' => 'MEDICINE']);

        // 2. Real
        session(['nhpr_real_api_mode' => true]);
        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/v1.5/facility/get-master-types' => Http::response([
                'masterTypes' => [
                    ['type' => 'MEDICINE', 'desc' => 'System Of Medicine'],
                ],
            ], 200),
        ]);
        $response = $this->getJson(route('nhpr.hfr.masters.types'));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.0.type', 'MEDICINE');
    }

    /**
     * Test HFR master data values.
     */
    public function test_masters_data_endpoint(): void
    {
        // 1. Simulated
        session(['nhpr_real_api_mode' => false]);
        $response = $this->getJson(route('nhpr.hfr.masters.data', ['type' => 'OWNER']));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonFragment(['code' => 'G', 'value' => 'Government']);

        // 2. Real
        session(['nhpr_real_api_mode' => true]);
        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/v1.5/facility/get-master-data?type=OWNER' => Http::response([
                'type' => 'OWNER',
                'data' => [
                    ['code' => 'G', 'value' => 'Government'],
                ],
            ], 200),
        ]);
        $response = $this->getJson(route('nhpr.hfr.masters.data', ['type' => 'OWNER']));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.0.code', 'G');
    }

    /**
     * Test HFR LGD endpoints (states, districts, subdistricts).
     */
    public function test_masters_lgd_endpoints(): void
    {
        // 1. Simulated states
        session(['nhpr_real_api_mode' => false]);
        $response = $this->getJson(route('nhpr.hfr.masters.states'));
        $response->assertStatus(200);
        $response->assertJsonFragment(['code' => '05', 'name' => 'Uttarakhand']);

        // 2. Simulated districts
        $response = $this->getJson(route('nhpr.hfr.masters.districts', ['stateCode' => '05']));
        $response->assertStatus(200);
        $response->assertJsonFragment(['code' => '060', 'name' => 'Dehradun']);

        // 3. Simulated subdistricts
        $response = $this->getJson(route('nhpr.hfr.masters.subdistricts', ['districtCode' => '060']));
        $response->assertStatus(200);
        $response->assertJsonFragment(['code' => '0501', 'name' => 'Dehradun Tehsil']);

        // 4. Real states
        session(['nhpr_real_api_mode' => true]);
        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/v1.5/facility/lgd/states' => Http::response([
                ['code' => '05', 'name' => 'Uttarakhand'],
            ], 200),
            'https://mock-api.abdm.gov.in/v4/int/v1.5/facility/lgd/districts?stateCode=05' => Http::response([
                ['code' => '060', 'name' => 'Dehradun'],
            ], 200),
            'https://mock-api.abdm.gov.in/v4/int/v1.5/facility/lgd/subdistricts?districtCode=060' => Http::response([
                ['code' => '0501', 'name' => 'Dehradun Tehsil'],
            ], 200),
        ]);

        $response = $this->getJson(route('nhpr.hfr.masters.states'));
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.code', '05');

        $response = $this->getJson(route('nhpr.hfr.masters.districts', ['stateCode' => '05']));
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.code', '060');

        $response = $this->getJson(route('nhpr.hfr.masters.subdistricts', ['districtCode' => '060']));
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.code', '0501');
    }

    /**
     * Test POST master endpoints.
     */
    public function test_masters_post_endpoints(): void
    {
        // Fetch facility type real API mock
        session(['nhpr_real_api_mode' => true]);
        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/v1.5/facility/fetch-facility-type' => Http::response([
                'type' => 'FACILITY-TYPE',
                'data' => [
                    ['code' => '5', 'value' => 'Hospital'],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.hfr.masters.facility-types'), [
            'ownershipCode' => 'P',
            'systemOfMedicineCode' => 'M',
        ]);
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.code', '5');
    }
}
