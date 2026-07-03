<?php

namespace Tests\Feature;

use App\Helpers\AbdmCryptEngine;
use App\Models\HiuConsentArtefact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HiuDecryptionTest extends TestCase
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
     * Test the cryptographic engine encrypts and decrypts correctly.
     */
    public function test_abdm_crypt_engine_native(): void
    {
        $plaintext = '{"clinical_record": "Test record contents"}';

        // 1. Generate keypairs
        $receiverKeys = AbdmCryptEngine::generateKeypair();
        $senderKeys = AbdmCryptEngine::generateKeypair();

        $this->assertNotEmpty($receiverKeys['privateKey']);
        $this->assertNotEmpty($receiverKeys['publicKey']);
        $this->assertNotEmpty($senderKeys['privateKey']);
        $this->assertNotEmpty($senderKeys['publicKey']);

        // 2. Derive secret and encrypt at sender
        $senderSecret = AbdmCryptEngine::deriveSharedSecret($receiverKeys['publicKey'], $senderKeys['privateKey']);
        $encrypted = AbdmCryptEngine::encryptPayload($plaintext, $senderSecret);

        $this->assertNotEmpty($encrypted['ciphertext']);
        $this->assertNotEmpty($encrypted['iv']);
        $this->assertNotEmpty($encrypted['tag']);

        // 3. Derive secret and decrypt at receiver
        $receiverSecret = AbdmCryptEngine::deriveSharedSecret($senderKeys['publicKey'], $receiverKeys['privateKey']);
        $decrypted = AbdmCryptEngine::decryptPayload(
            $encrypted['ciphertext'],
            $receiverSecret,
            $encrypted['iv'],
            $encrypted['tag']
        );

        $this->assertEquals($plaintext, $decrypted);
    }

    /**
     * Test full health information request and data push simulation flow.
     */
    public function test_request_and_push_health_data_simulation(): void
    {
        // 1. Setup active granted consent artefact
        $consentId = 'CON-test-123';
        $patientAddress = 'patient@sbx';

        HiuConsentArtefact::create([
            'consent_request_id' => 'REQ-123',
            'consent_id' => $consentId,
            'status' => 'GRANTED',
            'patient_abha_address' => $patientAddress,
            'consent_detail' => ['test' => 'detail'],
        ]);

        // 2. Trigger Outgoing Records Request
        $response = $this->postJson(route('hiu.health-information.request'), [
            'consent_id' => $consentId,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $txnId = $response->json('transaction_id');
        $this->assertNotEmpty($txnId);

        $this->assertDatabaseHas('hiu_transactions', [
            'transaction_id' => $txnId,
            'consent_id' => $consentId,
            'status' => 'REQUESTED',
        ]);

        // 3. Trigger Simulated HIP Data Push
        $pushResponse = $this->postJson(route('hiu.simulator.push-health-data'), [
            'transaction_id' => $txnId,
        ]);

        $pushResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify transaction updated to DELIVERED
        $this->assertDatabaseHas('hiu_transactions', [
            'transaction_id' => $txnId,
            'status' => 'DELIVERED',
        ]);

        // Verify that FHIR bundle was parsed and prescriptions stored
        $this->assertDatabaseHas('prescriptions', [
            'patient_abha_address' => $patientAddress,
            'doctor_name' => 'Dr. Amit Negi (MD)',
            'facility_name' => 'Uttarakhand Civil Hospital',
        ]);

        // Verify that lab reports were parsed and stored
        $this->assertDatabaseHas('diagnostic_reports', [
            'patient_abha_address' => $patientAddress,
            'test_name' => 'Complete Blood Count (CBC)',
        ]);

        // Verify page view renders correctly with patient clinical timeline
        $viewResponse = $this->get(route('hiu.records', $patientAddress));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Rahul Sharma');
        $viewResponse->assertSee('Complete Blood Count (CBC)');
        $viewResponse->assertSee('Amoxicillin 500mg');
    }
}
