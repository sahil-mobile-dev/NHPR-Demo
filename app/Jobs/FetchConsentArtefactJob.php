<?php

namespace App\Jobs;

use App\Models\HiuConsentArtefact;
use App\Models\HiuConsentRequest;
use App\Services\HiuConsentService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchConsentArtefactJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $consentId;

    protected string $consentReqId;

    protected string $patientAddress;

    protected array $notificationPayload;

    /**
     * Create a new job instance.
     */
    public function __construct(string $consentId, string $consentReqId, string $patientAddress, array $notificationPayload)
    {
        $this->consentId = $consentId;
        $this->consentReqId = $consentReqId;
        $this->patientAddress = $patientAddress;
        $this->notificationPayload = $notificationPayload;
    }

    /**
     * Execute the job.
     */
    public function handle(HiuConsentService $consentService): void
    {
        Log::info("FetchConsentArtefactJob: Processing for ID {$this->consentId}");

        try {
            // Update/Create artefact locally (initially PENDING_ARTEFACT)
            $artefact = HiuConsentArtefact::updateOrCreate(
                ['consent_id' => $this->consentId],
                [
                    'consent_request_id' => $this->consentReqId,
                    'status' => 'GRANTED',
                    'patient_abha_address' => $this->patientAddress,
                    'consent_detail' => $this->notificationPayload,
                ]
            );

            // Fetch actual details from ABDM Gateway
            $response = $consentService->fetchConsentArtefact($this->consentId);

            // Update with full fetched payload
            $artefact->update([
                'consent_detail' => $response,
            ]);

            // Update request status
            HiuConsentRequest::where('consent_request_id', $this->consentReqId)
                ->update(['status' => 'GRANTED']);

            Log::info("FetchConsentArtefactJob: Successfully fetched and cached artefact for ID {$this->consentId}");

        } catch (Exception $e) {
            Log::error('FetchConsentArtefactJob failed: '.$e->getMessage());
            // Fail job and allow Laravel Queue to handle retries
            throw $e;
        }
    }
}
