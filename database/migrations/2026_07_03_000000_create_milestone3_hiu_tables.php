<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Consent Requests created by HIU
        Schema::create('hiu_consent_requests', function (Blueprint $table) {
            $table->id();
            $table->string('consent_request_id')->nullable()->index();
            $table->string('patient_abha_address');
            $table->string('status')->default('REQUESTED'); // REQUESTED, GRANTED, REVOKED, EXPIRED, DENIED
            $table->string('purpose');
            $table->json('hi_types');
            $table->dateTime('date_from');
            $table->dateTime('date_to');
            $table->dateTime('expiry');
            $table->timestamps();
        });

        // Consent Artefacts stored after notification
        Schema::create('hiu_consent_artefacts', function (Blueprint $table) {
            $table->id();
            $table->string('consent_request_id')->index();
            $table->string('consent_id')->unique();
            $table->string('status')->default('GRANTED'); // GRANTED, REVOKED, EXPIRED
            $table->string('patient_abha_address');
            $table->json('consent_detail');
            $table->timestamps();
        });

        // HIU Health Information Request Transactions
        Schema::create('hiu_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('consent_id')->index();
            $table->string('status')->default('REQUESTED'); // REQUESTED, DELIVERED, FAILED
            $table->text('private_key');
            $table->text('public_key');
            $table->string('nonce');
            $table->timestamps();
        });

        // Parsed clinical resources: DocumentReference, Composition
        Schema::create('health_documents', function (Blueprint $table) {
            $table->id();
            $table->string('patient_abha_address')->index();
            $table->string('document_type'); // DISCHARGE_SUMMARY, DOCUMENT_REFERENCE
            $table->dateTime('document_date');
            $table->string('title');
            $table->string('author_name')->nullable();
            $table->string('facility_name')->nullable();
            $table->longText('file_content');
            $table->timestamps();
        });

        // Parsed clinical resources: DiagnosticReport
        Schema::create('diagnostic_reports', function (Blueprint $table) {
            $table->id();
            $table->string('patient_abha_address')->index();
            $table->dateTime('report_date');
            $table->string('test_name');
            $table->string('category')->nullable();
            $table->string('result_status');
            $table->text('conclusion')->nullable();
            $table->string('facility_name')->nullable();
            $table->string('doctor_name')->nullable();
            $table->timestamps();
        });

        // Parsed clinical resources: MedicationRequest, Prescription
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('patient_abha_address')->index();
            $table->dateTime('prescription_date');
            $table->string('doctor_name');
            $table->string('doctor_hpr_id')->nullable();
            $table->json('medications');
            $table->string('facility_name')->nullable();
            $table->timestamps();
        });

        // Parsed clinical resources: Observation
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->string('patient_abha_address')->index();
            $table->dateTime('observation_date');
            $table->string('code');
            $table->string('display');
            $table->string('value');
            $table->string('unit')->nullable();
            $table->string('status');
            $table->string('facility_name')->nullable();
            $table->timestamps();
        });

        // Parsed clinical resources: Encounter
        Schema::create('encounters', function (Blueprint $table) {
            $table->id();
            $table->string('patient_abha_address')->index();
            $table->dateTime('encounter_date');
            $table->string('encounter_type')->nullable();
            $table->string('class_code')->nullable();
            $table->string('status');
            $table->string('facility_name')->nullable();
            $table->string('doctor_name')->nullable();
            $table->timestamps();
        });

        // Audit logs for consents
        Schema::create('hiu_consent_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // REQUEST, CALLBACK_INIT, CALLBACK_NOTIFY
            $table->string('consent_request_id')->nullable();
            $table->string('consent_id')->nullable();
            $table->string('patient_abha_address')->nullable();
            $table->json('payload');
            $table->timestamp('created_at')->useCurrent();
        });

        // Audit logs for health information requests
        Schema::create('hiu_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // OUTGOING_HI_REQUEST, CALLBACK_ON_REQUEST, INCOMING_DATA_PUSH
            $table->string('transaction_id')->nullable();
            $table->string('consent_id')->nullable();
            $table->json('payload');
            $table->timestamp('created_at')->useCurrent();
        });

        // Audit logs for transactions
        Schema::create('hiu_abdm_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->string('consent_id');
            $table->string('type'); // HIU_REQUEST, HIP_TRANSFER
            $table->string('status'); // SUCCESS, FAILED
            $table->integer('records_count')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();
        });

        // Audit logs for health records processing (decryption, parsing)
        Schema::create('hiu_health_record_logs', function (Blueprint $table) {
            $table->id();
            $table->string('patient_abha_address');
            $table->string('action'); // DECRYPT, PARSE, VIEW
            $table->text('details');
            $table->string('status'); // SUCCESS, FAILED
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hiu_health_record_logs');
        Schema::dropIfExists('hiu_abdm_transactions');
        Schema::dropIfExists('hiu_request_logs');
        Schema::dropIfExists('hiu_consent_logs');
        Schema::dropIfExists('encounters');
        Schema::dropIfExists('observations');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('diagnostic_reports');
        Schema::dropIfExists('health_documents');
        Schema::dropIfExists('hiu_transactions');
        Schema::dropIfExists('hiu_consent_artefacts');
        Schema::dropIfExists('hiu_consent_requests');
    }
};
