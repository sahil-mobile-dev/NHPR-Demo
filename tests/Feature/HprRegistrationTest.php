<?php

namespace Tests\Feature;

use App\Helpers\AbdmEncryptionHelper;
use App\Services\GatewayTokenService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Class HprRegistrationTest
 *
 * Validates the complete HPR Registration Wizard flow from Aadhaar OTP generation,
 * profile verification, existing HPR checks, demographic mobile validation,
 * username suggestion retrieve, pre-verified ID creation, facility search,
 * professional registration, and document uploads.
 */
class HprRegistrationTest extends TestCase
{
    protected string $testPublicKeyPem = '';

    /**
     * Set up HPR configurations and generate a valid RSA public key for mocks.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.nhpr.base_url' => 'https://mock.abdm.gov.in',
            'services.nhpr.api_url' => 'https://mock-api.abdm.gov.in',
            'services.nhpr.client_id' => 'mock-client-id',
            'services.nhpr.client_secret' => 'mock-client-secret',
            'services.nhpr.x_cm_id' => 'sbx',
            'services.nhpr.real_api_mode' => true,
        ]);

        // Generate dynamic RSA key pair for testing openssl encryption
        $res = openssl_pkey_new([
            'config' => base_path('tests/openssl.cnf'),
            'private_key_bits' => 1024, // Use 1024 for faster generation in tests
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $details = openssl_pkey_get_details($res);
        $this->testPublicKeyPem = $details['key'];

        // Prime cache with valid gateway token
        Cache::put(GatewayTokenService::CACHE_KEY_TOKEN, 'mock-gateway-access-token-999', 3600);
        Cache::put(GatewayTokenService::CACHE_KEY_METADATA, ['expires_at' => now()->addMinutes(50)->timestamp], 3600);

        // Cache public certificate to bypass network call in helper
        Cache::put(AbdmEncryptionHelper::CERTIFICATE_CACHE_KEY, $this->testPublicKeyPem, 86400);
    }

    /**
     * Test the HPR registration wizard GET route renders.
     */
    public function test_wizard_renders_successfully(): void
    {
        $response = $this->get(route('nhpr.register.wizard'));

        $response->assertStatus(200);
        $response->assertViewIs('nhpr.register');
        $response->assertSee('Healthcare Professional Onboarding');
    }

    /**
     * Test Aadhaar OTP generation API endpoint.
     */
    public function test_send_aadhaar_otp_success(): void
    {
        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/v2/registration/aadhaar/generateOtp' => Http::response([
                'txnId' => 'mock-aadhaar-txn-id-123',
                'mobileNumber' => '9999991234',
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.aadhaar.send-otp'), [
            'aadhaar' => '123456789012',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'txnId' => 'mock-aadhaar-txn-id-123',
            ]);

        $this->assertEquals('mock-aadhaar-txn-id-123', session('hpr_reg_txn_id'));
    }

    /**
     * Test Aadhaar OTP verification for a NEW HPR account profile.
     */
    public function test_verify_aadhaar_otp_new_user_success(): void
    {
        session(['hpr_reg_txn_id' => 'mock-aadhaar-txn-id-123']);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/v2/registration/aadhaar/verifyOTP' => Http::response([
                'txnId' => 'mock-aadhaar-txn-id-123',
                'mobileNumber' => null,
            ], 200),
            'https://mock-api.abdm.gov.in/v4/int/v1/registration/aadhaar/checkHpIdAccountExist' => Http::response([
                'hprIdNumber' => '',
                'name' => 'Dr Ramesh Kumar',
                'gender' => 'M',
                'yearOfBirth' => '1990',
                'firstName' => 'Ramesh',
                'lastName' => 'Kumar',
                'stateCode' => '27',
                'districtCode' => '472',
                'profilePhoto' => 'dummybase64photo',
                'mobile' => '9876543210',
                'new' => true,
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.aadhaar.verify-otp'), [
            'otp' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'isExistingUser' => false,
                'mobile' => '9876543210',
            ]);

        $this->assertEquals('Dr Ramesh Kumar', session('hpr_reg_aadhaar_info.name'));
        $this->assertEquals('9876543210', session('hpr_reg_mobile'));
    }

    /**
     * Test Aadhaar OTP verification for an EXISTING HPR account profile.
     */
    public function test_verify_aadhaar_otp_existing_user_stops_flow(): void
    {
        session(['hpr_reg_txn_id' => 'mock-aadhaar-txn-id-123']);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/v2/registration/aadhaar/verifyOTP' => Http::response([
                'txnId' => 'mock-aadhaar-txn-id-123',
                'mobileNumber' => null,
            ], 200),
            'https://mock-api.abdm.gov.in/v4/int/v1/registration/aadhaar/checkHpIdAccountExist' => Http::response([
                'hprIdNumber' => '71-3563-6824-2283',
                'name' => 'Dr Ramesh Kumar',
                'gender' => 'M',
                'yearOfBirth' => '1990',
                'address' => 'Rishikesh, Uttarakhand',
                'profilePhoto' => 'dummyphoto',
                'new' => false,
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.aadhaar.verify-otp'), [
            'otp' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'isExistingUser' => true,
                'profile' => [
                    'hprIdNumber' => '71-3563-6824-2283',
                    'name' => 'Dr Ramesh Kumar',
                ],
            ]);

        $this->assertNull(session('hpr_reg_txn_id'));
    }

    /**
     * Test mobile verification demographic matches directly.
     */
    public function test_verify_mobile_demographic_success(): void
    {
        session(['hpr_reg_txn_id' => 'mock-txn-999']);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/v2/registration/aadhaar/demographicAuthViaMobile' => Http::response([
                'verified' => true,
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.mobile.verify'), [
            'mobile' => '9876543210',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'verified' => true,
            ]);

        $this->assertEquals('9876543210', session('hpr_reg_mobile'));
    }

    /**
     * Test mobile verification demographic failed, triggering fallback Mobile OTP generation.
     */
    public function test_verify_mobile_demographic_failed_triggers_otp(): void
    {
        session(['hpr_reg_txn_id' => 'mock-txn-999']);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/v2/registration/aadhaar/demographicAuthViaMobile' => Http::response([
                'verified' => false,
            ], 200),
            'https://mock-api.abdm.gov.in/v4/int/v1/registration/aadhaar/generateMobileOTP' => Http::response([
                'txnId' => 'mock-fallback-mobile-txn-id',
                'mobileNumber' => null,
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.mobile.verify'), [
            'mobile' => '9876543210',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'verified' => false,
                'txnId' => 'mock-fallback-mobile-txn-id',
            ]);

        $this->assertEquals('mock-fallback-mobile-txn-id', session('hpr_reg_txn_id'));
    }

    /**
     * Test fallback Mobile OTP verification.
     */
    public function test_verify_mobile_otp_success(): void
    {
        session(['hpr_reg_txn_id' => 'mock-fallback-mobile-txn-id']);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/v1/registration/aadhaar/verifyMobileOTP' => Http::response([
                'txnId' => 'mock-mobile-otp-verified-txn-id',
                'mobileNumber' => null,
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.mobile.verify-otp'), [
            'otp' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals('mock-mobile-otp-verified-txn-id', session('hpr_reg_txn_id'));
    }

    /**
     * Test username suggestions retrieve API.
     */
    public function test_get_username_suggestions_success(): void
    {
        session(['hpr_reg_txn_id' => 'mock-txn-999']);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/v1/registration/aadhaar/hpid/suggestion' => Http::response([
                'dr.rahul',
                'rahul123',
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.suggestions'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'suggestions' => [
                    'dr.rahul',
                    'rahul123',
                ],
            ]);
    }

    /**
     * Test creating HPR ID profile.
     */
    public function test_create_hpr_id_success(): void
    {
        session([
            'hpr_reg_txn_id' => 'mock-txn-999',
            'hpr_reg_aadhaar_info' => [
                'firstName' => 'Ramesh',
                'lastName' => 'Kumar',
                'gender' => 'M',
                'yearOfBirth' => '1990',
                'stateCode' => '27',
                'districtCode' => '472',
                'profilePhoto' => 'dummyphoto',
            ],
        ]);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/v2/registration/aadhaar/createHprIdWithPreVerified' => Http::response([
                'token' => 'mock-hpr-token-jwt-111',
                'hprIdNumber' => '71-3563-6824-2283',
                'hprId' => 'ramesh1990@hpr.abdm',
                'name' => 'Ramesh Kumar',
                'gender' => 'M',
                'yearOfBirth' => '1990',
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.create-id'), [
            'username' => 'ramesh1990',
            'email' => 'ramesh@example.com',
            'password' => 'SecurePass123!',
            'category' => '1', // Doctor
            'subcategory' => '1', // Modern Medicine
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'hprId' => 'ramesh1990@hpr.abdm',
                'hprIdNumber' => '71-3563-6824-2283',
            ]);

        $this->assertEquals('mock-hpr-token-jwt-111', session('hpr_reg_hpr_token'));
        $this->assertEquals('ramesh1990@hpr.abdm', session('hpr_reg_hpr_id'));
    }

    /**
     * Test HFR healthcare facility search.
     */
    public function test_facility_search_success(): void
    {
        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/FacilityManagement/v1.5/facility/search' => Http::response([
                'facilities' => [
                    [
                        'facilityId' => 'IN2710000059',
                        'facilityName' => 'AIIMS Rishikesh',
                        'address' => 'Rishikesh',
                        'pincode' => '249201',
                    ],
                ],
                'totalFacilities' => 1,
                'numberOfPages' => 1,
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.facility.search'), [
            'facilityName' => 'AIIMS',
            'pincode' => '249201',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'facilities' => [
                    [
                        'facilityId' => 'IN2710000059',
                        'facilityName' => 'AIIMS Rishikesh',
                    ],
                ],
            ]);
    }

    /**
     * Test submitting professional medical practitioner registration and facility link.
     */
    public function test_submit_professional_registration_success(): void
    {
        session([
            'hpr_reg_hpr_token' => 'mock-hpr-token-jwt-111',
            'hpr_reg_aadhaar_info' => [
                'firstName' => 'Ramesh',
                'lastName' => 'Kumar',
                'gender' => 'M',
                'stateCode' => '27',
                'districtCode' => '472',
                'profilePhoto' => 'dummyphoto',
            ],
            'hpr_reg_mobile' => '9876543210',
            'hpr_reg_category_code' => '1',
            'hpr_reg_subcategory_code' => '1',
        ]);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/apis/v1/doctors/register-professional-new' => Http::response([
                'body' => [
                    'referenceNumber' => 'ref-999-000',
                    'status' => 'true',
                    'message' => 'Congratulations! Registered successfully.',
                    'hprId' => '71-3563-6824-2283',
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.professional.submit'), [
            'salutation' => 1,
            'dob' => '1990-08-12',
            'languages' => '1,2',
            'address' => 'Rishikesh',
            'pincode' => '249201',
            'council_id' => 41,
            'reg_no' => 'MC-12345',
            'reg_date' => '2015-06-15',
            'reg_cert_base64' => 'base64pdfcertdata',
            'degree_code' => 4060,
            'degree_college' => 1149,
            'degree_university' => 7010,
            'degree_year' => '2014',
            'degree_cert_base64' => 'base64degreecertdata',
            'currently_working' => '1',
            'work_status' => 1,
            'facility_id' => 'IN2710000059',
            'facility_name' => 'AIIMS Rishikesh',
            'facility_address' => 'Rishikesh',
            'facility_pincode' => '249201',
            'gov_type' => 'Central',
            'ministry' => 'Ministry of Health and Family Welfare',
            'is_permanent' => 'Permanent',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'referenceNumber' => 'ref-999-000',
                'hprId' => '71-3563-6824-2283',
            ]);

        $this->assertEquals('71-3563-6824-2283', session('hpr_reg_hpr_id'));
    }

    /**
     * Test generating Aadhaar authentication redirect link.
     */
    public function test_generate_aadhaar_link_success(): void
    {
        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/aadhaar/generateLink' => Http::response([
                'url' => 'https://mock-gateway.abdm.gov.in/auth/12345',
                'txnId' => 'mock-link-txn-id',
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.aadhaar.generate-link'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'url' => 'https://mock-gateway.abdm.gov.in/auth/12345',
                'txnId' => 'mock-link-txn-id',
            ]);

        $this->assertEquals('mock-link-txn-id', session('hpr_reg_txn_id'));
    }

    /**
     * Test checking Aadhaar authentication status when authenticated.
     */
    public function test_check_aadhaar_auth_status_authenticated(): void
    {
        session(['hpr_reg_txn_id' => 'mock-link-txn-id']);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/aadhaar/isAuthenticated' => Http::response('true', 200),
            'https://mock-api.abdm.gov.in/v4/int/v2/registration/aadhaar/verifyOTP' => Http::response([
                'name' => 'Dr Ramesh Kumar',
                'gender' => 'M',
                'dob' => '1990-05-15',
                'firstName' => 'Ramesh',
                'lastName' => 'Kumar',
                'stateCode' => '27',
                'districtCode' => '472',
                'photo' => 'dummybase64photo',
                'mobileNumber' => '9876543210',
            ], 200),
            'https://mock-api.abdm.gov.in/v4/int/v1/registration/aadhaar/checkHpIdAccountExist' => Http::response([
                'new' => true,
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.aadhaar.check-status'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'authenticated' => true,
                'isExistingUser' => false,
                'mobile' => '9876543210',
            ]);

        $this->assertEquals('Dr Ramesh Kumar', session('hpr_reg_aadhaar_info.name'));
        $this->assertEquals('9876543210', session('hpr_reg_mobile'));
    }

    /**
     * Test fetching central ministries master list.
     */
    public function test_get_ministries_success(): void
    {
        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/apis/v1/masters/getAllMinistry' => Http::response([
                ['code' => 'MOHFW', 'name' => 'Ministry of Health and Family Welfare'],
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.masters.ministries'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure(['ministries']);
    }

    /**
     * Test retrieving the list of required documents blocks checklist.
     */
    public function test_fetch_documents_success(): void
    {
        session(['hpr_reg_hpr_id' => '71-3563-6824-2283']);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/apis/v1/doctors/fetch-documents-list' => Http::response([
                'documentList' => [
                    'profilePhoto' => ['id' => 40169],
                    'degreeCertificate' => ['id' => 13953],
                    'registrationCertificate' => ['id' => 27409],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.documents.fetch'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'documentList' => [
                    'profilePhoto' => ['id' => 40169],
                ],
            ]);
    }

    /**
     * Test uploading base64 files checklist and completing registration wizard.
     */
    public function test_upload_documents_and_finish_success(): void
    {
        session(['hpr_reg_hpr_token' => 'mock-hpr-token-jwt-111']);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/apis/v1/uploads/upload-document' => Http::response([
                'profilePhoto' => ['status' => 'pass', 'msg' => 'uploaded'],
                'degreeCertificate' => ['status' => 'pass', 'msg' => 'uploaded'],
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.register.documents.upload'), [
            'documents' => [
                [
                    'document_id' => 40169,
                    'document_type' => 'profilePhoto',
                    'fileType' => 'image/jpeg',
                    'data' => 'base64imagedata',
                ],
                [
                    'document_id' => 13953,
                    'document_type' => 'degreeCertificate',
                    'fileType' => 'application/pdf',
                    'data' => 'base64pdfdata',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Documents uploaded and registration completed successfully!',
            ]);

        // Assert wizard state is cleared from session upon completion
        $this->assertNull(session('hpr_reg_hpr_token'));
    }

    /**
     * Test toggling integration mode dynamically in session.
     */
    public function test_toggle_mode_success(): void
    {
        $response = $this->postJson(route('nhpr.register.toggle-mode'), [
            'real_api_mode' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'real_api_mode' => true,
            ]);

        $this->assertTrue(session('nhpr_real_api_mode'));

        $response2 = $this->postJson(route('nhpr.register.toggle-mode'), [
            'real_api_mode' => false,
        ]);

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'real_api_mode' => false,
            ]);

        $this->assertFalse(session('nhpr_real_api_mode'));
    }

    /**
     * Test simulated offline flow doesn't invoke HTTP client endpoints.
     */
    public function test_simulated_mode_flow(): void
    {
        session(['nhpr_real_api_mode' => false]);

        // Hit send-otp; should return simulated status and bypass Http::fake
        $response = $this->postJson(route('nhpr.register.aadhaar.send-otp'), [
            'aadhaar' => '123456789012',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure(['txnId']);

        $this->assertStringContainsString('simulated-aadhaar-txn', session('hpr_reg_txn_id'));

        // Hit verify-otp; should return simulated user profile details
        $response2 = $this->postJson(route('nhpr.register.aadhaar.verify-otp'), [
            'otp' => '112233',
        ]);

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'isExistingUser' => false,
                'mobile' => '9876543210',
            ]);

        $this->assertEquals('Dr Ramesh Kumar (Simulated)', session('hpr_reg_aadhaar_info.name'));
    }

    /**
     * Test the status tracking page renders successfully.
     */
    public function test_track_status_view_renders_successfully(): void
    {
        $response = $this->get(route('nhpr.track.show'));

        $response->assertStatus(200)
            ->assertViewIs('nhpr.track')
            ->assertSee('Track Application Status');
    }

    /**
     * Test tracking status API returns validation error for missing reference number.
     */
    public function test_track_status_api_validation_error(): void
    {
        $response = $this->postJson(route('nhpr.track.post'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reference_number']);
    }

    /**
     * Test tracking status API returns APPROVED status by default.
     */
    public function test_track_status_api_approved(): void
    {
        session(['nhpr_real_api_mode' => false]);

        $response = $this->postJson(route('nhpr.track.post'), [
            'reference_number' => 'REF-1234567890',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'APPROVED',
            ])
            ->assertJsonStructure(['reference_number', 'status', 'message', 'steps']);
    }

    /**
     * Test tracking status API returns ISSUES status for 'REJECT'.
     */
    public function test_track_status_api_issues(): void
    {
        session(['nhpr_real_api_mode' => false]);

        $response = $this->postJson(route('nhpr.track.post'), [
            'reference_number' => 'REF-REJECT-001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'ISSUES',
            ]);
    }

    /**
     * Test tracking status API returns REVIEW status for 'PENDING'.
     */
    public function test_track_status_api_review(): void
    {
        session(['nhpr_real_api_mode' => false]);

        $response = $this->postJson(route('nhpr.track.post'), [
            'reference_number' => 'REF-PENDING-001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'REVIEW',
            ]);
    }

    /**
     * Test tracking status API in real mode makes HTTP requests and maps response correctly.
     */
    public function test_track_status_api_real_mode(): void
    {
        session(['nhpr_real_api_mode' => true]);

        Http::fake([
            'https://mock-api.abdm.gov.in/v4/int/apis/v1/doctors/fetch-professional-info' => Http::response([
                'practitioners' => [
                    [
                        'hpr_id' => '71-1234-5678-9012',
                        'application_status' => 'Approved',
                        'is_council_verified' => 'Approved',
                        'is_work_verified' => 'Approved',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.track.post'), [
            'reference_number' => '71-1234-5678-9012',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'APPROVED',
                'real_api_mode' => true,
            ]);
    }
}
