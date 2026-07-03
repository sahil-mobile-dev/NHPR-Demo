<?php

namespace Tests\Feature;

use App\Models\DiagnosticReport;
use App\Models\HiuConsentArtefact;
use App\Models\HiuConsentRequest;
use App\Models\Prescription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HiuConsentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.nhpr.real_api_mode' => false,
        ]);
    }

    /**
     * Test the HIU Dashboard renders correctly.
     */
    public function test_hiu_dashboard_renders_successfully(): void
    {
        $response = $this->get(route('hiu.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('hiu.dashboard');
        $response->assertSee('Health Information User (HIU) Console');
    }

    /**
     * Test creating a consent request in mock mode.
     */
    public function test_create_consent_request_success(): void
    {
        $payload = [
            'patient_abha_address' => 'patient@sbx',
            'purpose' => 'General Consultation',
            'hi_types' => ['Prescription', 'DiagnosticReport'],
            'date_from' => '2026-01-01',
            'date_to' => '2026-07-01',
            'expiry' => '2026-07-10',
        ];

        $response = $this->postJson(route('hiu.consent.request'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('hiu_consent_requests', [
            'patient_abha_address' => 'patient@sbx',
            'purpose' => 'General Consultation',
            'status' => 'REQUESTED',
        ]);
    }

    /**
     * Test consent on-init callback.
     */
    public function test_consent_on_init_callback(): void
    {
        // 1. Create a local request representing our initial request
        $localRequest = HiuConsentRequest::create([
            'consent_request_id' => 'REQ-original-123',
            'patient_abha_address' => 'patient@sbx',
            'status' => 'REQUESTED',
            'purpose' => 'General Consultation',
            'hi_types' => ['Prescription'],
            'date_from' => now()->subYear(),
            'date_to' => now(),
            'expiry' => now()->addDays(3),
        ]);

        // 2. Call the ABDM Gateway callback v3/consent/on-init
        $callbackPayload = [
            'requestId' => 'callback-req-999',
            'timestamp' => now()->toIso8601String(),
            'consentRequest' => [
                'id' => 'consent-request-uuid-from-abdm',
            ],
            'resp' => [
                'requestId' => 'REQ-original-123', // links back to our original request
            ],
        ];

        $response = $this->postJson('/v3/consent/on-init', $callbackPayload);

        $response->assertStatus(202);

        $this->assertDatabaseHas('hiu_consent_requests', [
            'id' => $localRequest->id,
            'consent_request_id' => 'consent-request-uuid-from-abdm',
            'status' => 'INITIATED',
        ]);
    }

    /**
     * Test consent notify callback.
     */
    public function test_consent_notify_callback_granted(): void
    {
        $consentReqId = 'consent-req-uuid-123';

        $localRequest = HiuConsentRequest::create([
            'consent_request_id' => $consentReqId,
            'patient_abha_address' => 'patient@sbx',
            'status' => 'INITIATED',
            'purpose' => 'General Consultation',
            'hi_types' => ['Prescription'],
            'date_from' => now()->subYear(),
            'date_to' => now(),
            'expiry' => now()->addDays(3),
        ]);

        $callbackPayload = [
            'requestId' => 'notify-req-555',
            'timestamp' => now()->toIso8601String(),
            'notification' => [
                'consentRequestId' => $consentReqId,
                'status' => 'GRANTED',
                'consentArtefacts' => [
                    [
                        'id' => 'consent-artefact-uuid-xyz',
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/v3/consent/notify', $callbackPayload);

        $response->assertStatus(202);

        $this->assertDatabaseHas('hiu_consent_requests', [
            'id' => $localRequest->id,
            'status' => 'GRANTED',
        ]);

        $this->assertDatabaseHas('hiu_consent_artefacts', [
            'consent_request_id' => $consentReqId,
            'consent_id' => 'consent-artefact-uuid-xyz',
            'status' => 'GRANTED',
            'patient_abha_address' => 'patient@sbx',
        ]);
    }

    /**
     * Test simulated rejection (DENIED).
     */
    public function test_simulate_deny_consent_request(): void
    {
        $localRequest = HiuConsentRequest::create([
            'consent_request_id' => 'REQ-deny-123',
            'patient_abha_address' => 'patient@sbx',
            'status' => 'INITIATED',
            'purpose' => 'General Consultation',
            'hi_types' => ['Prescription'],
            'date_from' => now()->subYear(),
            'date_to' => now(),
            'expiry' => now()->addDays(3),
        ]);

        $response = $this->postJson(route('hiu.simulator.deny-consent'), [
            'consent_request_id' => 'REQ-deny-123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('hiu_consent_requests', [
            'id' => $localRequest->id,
            'status' => 'DENIED',
        ]);
    }

    /**
     * Test simulated patient revocation (REVOKED).
     */
    public function test_simulate_revoke_consent(): void
    {
        $consentId = 'CON-revoke-123';
        $patientAddress = 'patient@sbx';

        HiuConsentArtefact::create([
            'consent_request_id' => 'REQ-revoke-123',
            'consent_id' => $consentId,
            'status' => 'GRANTED',
            'patient_abha_address' => $patientAddress,
            'consent_detail' => [],
        ]);

        $response = $this->postJson(route('hiu.simulator.revoke-consent'), [
            'consent_id' => $consentId,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('hiu_consent_artefacts', [
            'consent_id' => $consentId,
            'status' => 'REVOKED',
        ]);
    }

    /**
     * Test local revocation and clinical records wiping.
     */
    public function test_local_revocation_and_wiping(): void
    {
        $consentId = 'CON-local-123';
        $patientAddress = 'patient@sbx';

        // 1. Setup local artefact
        HiuConsentArtefact::create([
            'consent_request_id' => 'REQ-local-123',
            'consent_id' => $consentId,
            'status' => 'GRANTED',
            'patient_abha_address' => $patientAddress,
            'consent_detail' => [],
        ]);

        // 2. Setup mock clinical records in database
        Prescription::create([
            'patient_abha_address' => $patientAddress,
            'doctor_name' => 'Dr. Negi',
            'facility_name' => 'Civil Hospital',
            'prescription_date' => now(),
            'medications' => [],
        ]);

        DiagnosticReport::create([
            'patient_abha_address' => $patientAddress,
            'doctor_name' => 'Dr. Negi',
            'facility_name' => 'Civil Hospital',
            'report_date' => now(),
            'test_name' => 'CBC',
            'result_status' => 'final',
            'observations' => [],
        ]);

        // 3. Trigger local revoke & wipe
        $response = $this->postJson(route('hiu.consent.revoke', $consentId));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify status updated to REVOKED
        $this->assertDatabaseHas('hiu_consent_artefacts', [
            'consent_id' => $consentId,
            'status' => 'REVOKED',
        ]);

        // Verify all decrypted clinical records are deleted/wiped
        $this->assertDatabaseMissing('prescriptions', [
            'patient_abha_address' => $patientAddress,
        ]);

        $this->assertDatabaseMissing('diagnostic_reports', [
            'patient_abha_address' => $patientAddress,
        ]);
    }
}
