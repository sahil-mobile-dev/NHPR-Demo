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
        Schema::create('security_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->string('consent_id');
            $table->string('hiu_id');
            $table->string('patient_abha_address');
            $table->integer('records_transferred');
            $table->string('status'); // SUCCESS, FAILED
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_audit_logs');
    }
};
