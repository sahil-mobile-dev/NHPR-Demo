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
        Schema::create('care_contexts', function (Blueprint $table) {
            $table->id();
            $table->string('patient_abha_number')->nullable();
            $table->string('patient_abha_address');
            $table->string('care_context_reference')->unique();
            $table->string('display');
            $table->boolean('is_linked')->default(false);
            $table->timestamps();
        });

        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('care_context_id')->constrained('care_contexts')->onDelete('cascade');
            $table->string('record_type'); // PRESCRIPTION, DIAGNOSTIC_REPORT
            $table->dateTime('record_date');
            $table->json('fhir_data');
            $table->timestamps();
        });

        Schema::create('consent_artefacts', function (Blueprint $table) {
            $table->id();
            $table->string('consent_id')->unique();
            $table->string('status'); // GRANTED, REVOKED, EXPIRED
            $table->string('patient_abha_address');
            $table->json('consent_detail');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_artefacts');
        Schema::dropIfExists('health_records');
        Schema::dropIfExists('care_contexts');
    }
};
