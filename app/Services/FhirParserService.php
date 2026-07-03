<?php

namespace App\Services;

use App\Models\DiagnosticReport;
use App\Models\Encounter;
use App\Models\HealthDocument;
use App\Models\Observation;
use App\Models\Prescription;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class FhirParserService
 *
 * Parses HL7 FHIR R4 Bundle documents and extracts structured clinical records.
 */
class FhirParserService
{
    /**
     * Parse a FHIR R4 Bundle (JSON array) and save components to database.
     *
     * @param  array  $bundle  The JSON-decoded FHIR Bundle.
     * @param  string  $patientAbhaAddress  The patient's ABHA Address.
     * @return int Number of resources successfully imported.
     */
    public function parseAndStore(array $bundle, string $patientAbhaAddress): int
    {
        Log::info('FHIR Parser: Starting to parse FHIR bundle', ['patient' => $patientAbhaAddress]);

        if (($bundle['resourceType'] ?? '') !== 'Bundle') {
            // Check if it's a list of bundles
            if (isset($bundle[0]) && is_array($bundle[0])) {
                $count = 0;
                foreach ($bundle as $subBundle) {
                    if (is_array($subBundle)) {
                        $count += $this->parseAndStore($subBundle, $patientAbhaAddress);
                    }
                }

                return $count;
            }
            throw new Exception('FHIR Parser Error: Root resource is not a FHIR Bundle.');
        }

        $entries = $bundle['entry'] ?? [];
        if (empty($entries)) {
            Log::warning('FHIR Parser: Bundle has no entries.');

            return 0;
        }

        // 1. Index resources by Type and ID for fast reference lookup
        $indexed = [];
        foreach ($entries as $entry) {
            $resource = $entry['resource'] ?? null;
            if ($resource && isset($resource['resourceType'], $resource['id'])) {
                $indexed[$resource['resourceType']][$resource['id']] = $resource;
            }
        }

        // 2. Locate Composition and Custodian (Facility) Details
        $facilityName = 'ParaCare+ Facility';
        $authorName = 'Dr. Staff Practitioner';
        $compositions = $indexed['Composition'] ?? [];
        foreach ($compositions as $comp) {
            if (isset($comp['custodian']['display'])) {
                $facilityName = $comp['custodian']['display'];
            }
            if (isset($comp['author'][0]['display'])) {
                $authorName = $comp['author'][0]['display'];
            }
        }

        $importedCount = 0;

        // 3. Parse and store Encounters
        $encounters = $indexed['Encounter'] ?? [];
        foreach ($encounters as $id => $enc) {
            $date = $enc['period']['start'] ?? now()->toIso8601String();
            Encounter::create([
                'patient_abha_address' => $patientAbhaAddress,
                'encounter_date' => $date,
                'encounter_type' => $enc['type'][0]['text'] ?? $enc['type'][0]['coding'][0]['display'] ?? 'General Consultation',
                'class_code' => $enc['class']['code'] ?? 'AMB',
                'status' => $enc['status'] ?? 'finished',
                'facility_name' => $enc['serviceProvider']['display'] ?? $facilityName,
                'doctor_name' => $authorName,
            ]);
            $importedCount++;
        }

        // 4. Parse and store MedicationRequests (Prescriptions)
        $medRequests = $indexed['MedicationRequest'] ?? [];
        if (! empty($medRequests)) {
            $medications = [];
            $prescDate = now()->toIso8601String();
            $docName = $authorName;
            $docHpr = null;

            foreach ($medRequests as $id => $req) {
                $prescDate = $req['authoredOn'] ?? $prescDate;

                // Lookup Practitioner display and HPR ID
                if (isset($req['requester']['reference'])) {
                    $refParts = explode(':', $req['requester']['reference']);
                    $refId = end($refParts);
                    $practitioner = $indexed['Practitioner'][$refId] ?? null;
                    if ($practitioner) {
                        $docName = $practitioner['name'][0]['text'] ?? $req['requester']['display'] ?? $docName;
                        $docHpr = $practitioner['identifier'][0]['value'] ?? null;
                    } else {
                        $docName = $req['requester']['display'] ?? $docName;
                    }
                } else {
                    $docName = $req['requester']['display'] ?? $docName;
                }

                $medName = $req['medicationCodeableConcept']['text'] ??
                           $req['medicationCodeableConcept']['coding'][0]['display'] ??
                           'Unknown Medication';

                $dosage = $req['dosageInstruction'][0]['text'] ?? 'As directed';

                $medications[] = [
                    'name' => $medName,
                    'dosage' => $dosage,
                    'duration' => $req['dosageInstruction'][0]['timing']['repeat']['boundsDuration']['value'] ?? '5 days',
                    'instructions' => $req['dosageInstruction'][0]['additionalInstruction'][0]['text'] ?? 'Take after meals',
                ];
            }

            if (! empty($medications)) {
                Prescription::create([
                    'patient_abha_address' => $patientAbhaAddress,
                    'prescription_date' => $prescDate,
                    'doctor_name' => $docName,
                    'doctor_hpr_id' => $docHpr,
                    'medications' => $medications,
                    'facility_name' => $facilityName,
                ]);
                $importedCount++;
            }
        }

        // 5. Parse and store DiagnosticReports
        $diagReports = $indexed['DiagnosticReport'] ?? [];
        foreach ($diagReports as $id => $report) {
            $reportDate = $report['effectiveDateTime'] ?? now()->toIso8601String();
            $testName = $report['code']['text'] ?? $report['code']['coding'][0]['display'] ?? 'Diagnostic Test';
            $category = $report['category'][0]['text'] ?? $report['category'][0]['coding'][0]['display'] ?? 'Lab';
            $conclusion = $report['conclusion'] ?? 'Report completed.';

            $docName = $authorName;
            if (isset($report['resultsInterpreter'][0]['display'])) {
                $docName = $report['resultsInterpreter'][0]['display'];
            }

            DiagnosticReport::create([
                'patient_abha_address' => $patientAbhaAddress,
                'report_date' => $reportDate,
                'test_name' => $testName,
                'category' => $category,
                'result_status' => $report['status'] ?? 'final',
                'conclusion' => $conclusion,
                'facility_name' => $facilityName,
                'doctor_name' => $docName,
            ]);
            $importedCount++;
        }

        // 6. Parse and store Observations
        $observations = $indexed['Observation'] ?? [];
        foreach ($observations as $id => $obs) {
            $obsDate = $obs['effectiveDateTime'] ?? now()->toIso8601String();
            $code = $obs['code']['coding'][0]['code'] ?? 'OBS';
            $display = $obs['code']['text'] ?? $obs['code']['coding'][0]['display'] ?? 'Observation';

            $value = 'N/A';
            $unit = null;
            if (isset($obs['valueQuantity'])) {
                $value = (string) $obs['valueQuantity']['value'];
                $unit = $obs['valueQuantity']['unit'] ?? null;
            } elseif (isset($obs['valueString'])) {
                $value = $obs['valueString'];
            } elseif (isset($obs['valueCodeableConcept'])) {
                $value = $obs['valueCodeableConcept']['text'] ?? $obs['valueCodeableConcept']['coding'][0]['display'] ?? 'N/A';
            }

            Observation::create([
                'patient_abha_address' => $patientAbhaAddress,
                'observation_date' => $obsDate,
                'code' => $code,
                'display' => $display,
                'value' => $value,
                'unit' => $unit,
                'status' => $obs['status'] ?? 'final',
                'facility_name' => $facilityName,
            ]);
            $importedCount++;
        }

        // 7. Parse and store DocumentReferences (Discharge Summaries, Scanned PDFs, etc.)
        $docRefs = $indexed['DocumentReference'] ?? [];
        foreach ($docRefs as $id => $docRef) {
            $docDate = $docRef['date'] ?? now()->toIso8601String();
            $title = $docRef['description'] ?? 'Attachment Document';
            $docType = $docRef['type']['text'] ?? 'DOCUMENT_REFERENCE';
            $content = $docRef['content'][0]['attachment']['data'] ?? '';

            if (! empty($content)) {
                HealthDocument::create([
                    'patient_abha_address' => $patientAbhaAddress,
                    'document_type' => $docType,
                    'document_date' => $docDate,
                    'title' => $title,
                    'author_name' => $authorName,
                    'facility_name' => $facilityName,
                    'file_content' => $content, // Store raw base64 or plaintext
                ]);
                $importedCount++;
            }
        }

        Log::info('FHIR Parser: Completed parsing. Imported items count: '.$importedCount);

        return $importedCount;
    }
}
