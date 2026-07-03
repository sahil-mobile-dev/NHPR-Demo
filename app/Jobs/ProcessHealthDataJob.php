<?php

namespace App\Jobs;

use App\Models\HiuConsentArtefact;
use App\Models\HiuTransaction;
use App\Services\FhirParserService;
use App\Services\FideliusService;
use App\Services\HiuAuditService;
use App\Services\HiuHealthInformationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessHealthDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $txnId;

    protected array $entries;

    protected array $keyMaterial;

    /**
     * Create a new job instance.
     */
    public function __construct(string $txnId, array $entries, array $keyMaterial)
    {
        $this->txnId = $txnId;
        $this->entries = $entries;
        $this->keyMaterial = $keyMaterial;
    }

    /**
     * Execute the job.
     */
    public function handle(
        FideliusService $fideliusService,
        FhirParserService $fhirParserService,
        HiuHealthInformationService $healthService,
        HiuAuditService $auditService
    ): void {
        Log::info("ProcessHealthDataJob: Start processing for Transaction ID {$this->txnId}");

        $transaction = HiuTransaction::where('transaction_id', $this->txnId)->first();
        if (! $transaction) {
            Log::error("ProcessHealthDataJob: Transaction not found: {$this->txnId}");

            return;
        }

        $consent = HiuConsentArtefact::where('consent_id', $transaction->consent_id)->first();
        $patientAddress = $consent?->patient_abha_address ?? 'unknown';

        try {
            $importedCount = 0;

            foreach ($this->entries as $entry) {
                $ciphertext = $entry['content'];
                $senderPublicKey = $this->keyMaterial['dhPublicKey']['keyValue'];
                $senderNonce = $this->keyMaterial['nonce'];
                $receiverPrivateKey = $transaction->private_key;
                $iv = $this->keyMaterial['dhPublicKey']['parameters'] ?? $transaction->nonce;

                // Decrypt
                $decryptedText = $fideliusService->decrypt(
                    $ciphertext,
                    $senderPublicKey,
                    $senderNonce, // tag
                    $receiverPrivateKey,
                    $iv
                );

                $decryptedJson = json_decode($decryptedText, true);
                $auditService->logRecordProcessing($patientAddress, 'DECRYPT', 'Decrypted records successfully.', 'SUCCESS');

                if ($decryptedJson) {
                    // Parse FHIR bundle and save elements
                    $importedCount += $fhirParserService->parseAndStore($decryptedJson, $patientAddress);
                    $auditService->logRecordProcessing($patientAddress, 'PARSE', "Parsed and stored {$importedCount} clinical elements.", 'SUCCESS');
                } else {
                    throw new Exception('Decrypted text is not valid JSON.');
                }
            }

            // Update transaction status
            $transaction->update(['status' => 'DELIVERED']);

            // Notify HIP/ABDM of success
            $healthService->notifyDataReceived($this->txnId, 'DELIVERED');

            // Log Transaction Audit
            $auditService->logTransaction($this->txnId, $transaction->consent_id, 'HIU_REQUEST', 'SUCCESS', $importedCount);

            Log::info("ProcessHealthDataJob: Successfully imported {$importedCount} resources for Transaction {$this->txnId}");

        } catch (Exception $e) {
            Log::error("ProcessHealthDataJob failed for Transaction {$this->txnId}: ".$e->getMessage());

            $transaction->update(['status' => 'FAILED']);
            $healthService->notifyDataReceived($this->txnId, 'FAILED');

            $auditService->logRecordProcessing($patientAddress, 'DECRYPT', 'Decryption/Parsing failed: '.$e->getMessage(), 'FAILED');
            $auditService->logTransaction($this->txnId, $transaction->consent_id, 'HIU_REQUEST', 'FAILED', 0, $e->getMessage());

            throw $e; // Re-throw to trigger Laravel Queue retry behavior
        }
    }
}
