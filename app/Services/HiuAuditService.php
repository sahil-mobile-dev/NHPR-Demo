<?php

namespace App\Services;

use App\Models\ConsentLog;
use App\Models\HealthRecordLog;
use App\Models\HiuAbdmTransaction;
use App\Models\RequestLog;
use Illuminate\Support\Facades\Log;

/**
 * Class HiuAuditService
 *
 * Handles standard-compliant security and interaction logging for the ABDM HIU activities.
 */
class HiuAuditService
{
    /**
     * Log a consent lifecycle event.
     */
    public function logConsent(
        string $action,
        ?string $requestId,
        ?string $consentId,
        ?string $patientAbhaAddress,
        array $payload
    ): void {
        try {
            ConsentLog::create([
                'action' => $action,
                'consent_request_id' => $requestId,
                'consent_id' => $consentId,
                'patient_abha_address' => $patientAbhaAddress,
                'payload' => $payload,
            ]);
            Log::info("HIU Audit Logged Consent Action: {$action}", ['consent_id' => $consentId]);
        } catch (\Exception $e) {
            Log::error('HIU Audit failed to log consent action: '.$e->getMessage());
        }
    }

    /**
     * Log a health information transfer request/response.
     */
    public function logRequest(
        string $action,
        ?string $transactionId,
        ?string $consentId,
        array $payload
    ): void {
        try {
            RequestLog::create([
                'action' => $action,
                'transaction_id' => $transactionId,
                'consent_id' => $consentId,
                'payload' => $payload,
            ]);
            Log::info("HIU Audit Logged Request Action: {$action}", ['transaction_id' => $transactionId]);
        } catch (\Exception $e) {
            Log::error('HIU Audit failed to log request action: '.$e->getMessage());
        }
    }

    /**
     * Log the status of an ABDM transaction.
     */
    public function logTransaction(
        string $transactionId,
        string $consentId,
        string $type,
        string $status,
        int $recordsCount = 0,
        ?string $error = null
    ): void {
        try {
            HiuAbdmTransaction::create([
                'transaction_id' => $transactionId,
                'consent_id' => $consentId,
                'type' => $type,
                'status' => $status,
                'records_count' => $recordsCount,
                'error' => $error,
            ]);
            Log::info("HIU Audit Logged Transaction Status: {$status}", [
                'transaction_id' => $transactionId,
                'type' => $type,
            ]);
        } catch (\Exception $e) {
            Log::error('HIU Audit failed to log transaction: '.$e->getMessage());
        }
    }

    /**
     * Log clinical decryption or parsing details.
     */
    public function logRecordProcessing(
        string $patientAbhaAddress,
        string $action,
        string $details,
        string $status
    ): void {
        try {
            HealthRecordLog::create([
                'patient_abha_address' => $patientAbhaAddress,
                'action' => $action,
                'details' => $details,
                'status' => $status,
            ]);
            Log::info("HIU Audit Logged Record Processing: {$action} - {$status}");
        } catch (\Exception $e) {
            Log::error('HIU Audit failed to log record processing: '.$e->getMessage());
        }
    }
}
