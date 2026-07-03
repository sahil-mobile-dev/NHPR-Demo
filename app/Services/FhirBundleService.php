<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Class FhirBundleService
 *
 * Generates fully compliant HL7 FHIR R4 Document Bundles for ABDM Health Information Exchange.
 */
class FhirBundleService
{
    /**
     * Build a FHIR R4 Prescription Document Bundle.
     *
     * @param  array  $data  Input values (patient details, doctor details, medications list).
     * @return array Compliant FHIR R4 Bundle payload.
     */
    public function buildPrescriptionBundle(array $data): array
    {
        $bundleId = (string) Str::uuid();
        $compositionId = (string) Str::uuid();
        $patientId = (string) Str::uuid();
        $practitionerId = (string) Str::uuid();
        $now = now()->toIso8601String();

        $medicationEntries = [];
        $medicationRelations = [];

        $medications = $data['medications'] ?? [];
        foreach ($medications as $idx => $med) {
            $medId = (string) Str::uuid();
            $medicationEntries[] = [
                'fullUrl' => 'urn:uuid:'.$medId,
                'resource' => [
                    'resourceType' => 'MedicationRequest',
                    'id' => $medId,
                    'status' => 'active',
                    'intent' => 'order',
                    'medicationCodeableConcept' => [
                        'coding' => [
                            [
                                'system' => 'http://snomed.info/sct',
                                'code' => '373873005',
                                'display' => $med['name'] ?? 'Pharmaceutical Product',
                            ],
                        ],
                        'text' => $med['name'] ?? 'Medication',
                    ],
                    'subject' => [
                        'reference' => 'urn:uuid:'.$patientId,
                        'display' => $data['patient_name'] ?? 'Patient',
                    ],
                    'authoredOn' => $now,
                    'requester' => [
                        'reference' => 'urn:uuid:'.$practitionerId,
                        'display' => $data['doctor_name'] ?? 'Practitioner',
                    ],
                    'dosageInstruction' => [
                        [
                            'text' => ($med['dosage'] ?? '').' - '.($med['duration'] ?? '').' ('.($med['instructions'] ?? 'Take as directed').')',
                        ],
                    ],
                ],
            ];

            $medicationRelations[] = [
                'reference' => 'urn:uuid:'.$medId,
                'display' => $med['name'] ?? 'Medication Request',
            ];
        }

        $bundle = [
            'resourceType' => 'Bundle',
            'id' => $bundleId,
            'meta' => [
                'versionId' => '1',
                'lastUpdated' => $now,
                'profile' => [
                    'https://nrces.in/ndhm/fhir/r4/StructureDefinition/DocumentBundle',
                ],
            ],
            'identifier' => [
                'system' => 'http://hip.in/bundle',
                'value' => 'BND-'.Str::random(12),
            ],
            'type' => 'document',
            'timestamp' => $now,
            'entry' => [
                // 1. Composition Resource (Must be first entry)
                [
                    'fullUrl' => 'urn:uuid:'.$compositionId,
                    'resource' => [
                        'resourceType' => 'Composition',
                        'id' => $compositionId,
                        'status' => 'final',
                        'type' => [
                            'coding' => [
                                [
                                    'system' => 'http://loinc.org',
                                    'code' => '57133-1',
                                    'display' => 'Referral Note',
                                ],
                            ],
                            'text' => 'Prescription Record Composition',
                        ],
                        'subject' => [
                            'reference' => 'urn:uuid:'.$patientId,
                            'display' => $data['patient_name'] ?? 'Patient',
                        ],
                        'date' => $now,
                        'author' => [
                            [
                                'reference' => 'urn:uuid:'.$practitionerId,
                                'display' => $data['doctor_name'] ?? 'Practitioner',
                            ],
                        ],
                        'title' => 'Prescription',
                        'section' => [
                            [
                                'title' => 'Medications Prescribed',
                                'code' => [
                                    'coding' => [
                                        [
                                            'system' => 'http://loinc.org',
                                            'code' => '57833-6',
                                            'display' => 'Prescription',
                                        ],
                                    ],
                                ],
                                'entry' => $medicationRelations,
                            ],
                        ],
                    ],
                ],
                // 2. Patient Resource
                [
                    'fullUrl' => 'urn:uuid:'.$patientId,
                    'resource' => [
                        'resourceType' => 'Patient',
                        'id' => $patientId,
                        'identifier' => [
                            [
                                'type' => [
                                    'coding' => [
                                        [
                                            'system' => 'http://terminology.hl7.org/CodeSystem/v2-0203',
                                            'code' => 'MR',
                                        ],
                                    ],
                                ],
                                'system' => 'https://healthid.ndhm.gov.in',
                                'value' => $data['patient_abha_address'] ?? 'patient@sbx',
                            ],
                        ],
                        'name' => [
                            [
                                'text' => $data['patient_name'] ?? 'Patient',
                            ],
                        ],
                        'gender' => strtolower($data['patient_gender'] ?? 'M') === 'f' ? 'female' : 'male',
                        'birthDate' => $data['patient_dob'] ?? '1990-01-01',
                    ],
                ],
                // 3. Practitioner Resource
                [
                    'fullUrl' => 'urn:uuid:'.$practitionerId,
                    'resource' => [
                        'resourceType' => 'Practitioner',
                        'id' => $practitionerId,
                        'identifier' => [
                            [
                                'system' => 'https://hpr.abdm.gov.in',
                                'value' => $data['doctor_hpr_id'] ?? 'doc@hpr',
                            ],
                        ],
                        'name' => [
                            [
                                'text' => $data['doctor_name'] ?? 'Dr. HIMS Practitioner',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // Merge medication requests into entries
        $bundle['entry'] = array_merge($bundle['entry'], $medicationEntries);

        return $bundle;
    }

    /**
     * Build a FHIR R4 Diagnostic Report Document Bundle.
     *
     * @param  array  $data  Input parameters (patient details, doctor details, test results).
     * @return array Compliant FHIR R4 Bundle payload.
     */
    public function buildDiagnosticReportBundle(array $data): array
    {
        $bundleId = (string) Str::uuid();
        $compositionId = (string) Str::uuid();
        $patientId = (string) Str::uuid();
        $practitionerId = (string) Str::uuid();
        $reportId = (string) Str::uuid();
        $observationId = (string) Str::uuid();
        $now = now()->toIso8601String();

        return [
            'resourceType' => 'Bundle',
            'id' => $bundleId,
            'meta' => [
                'versionId' => '1',
                'lastUpdated' => $now,
                'profile' => [
                    'https://nrces.in/ndhm/fhir/r4/StructureDefinition/DocumentBundle',
                ],
            ],
            'identifier' => [
                'system' => 'http://hip.in/bundle',
                'value' => 'BND-'.Str::random(12),
            ],
            'type' => 'document',
            'timestamp' => $now,
            'entry' => [
                // 1. Composition Resource (Must be first entry)
                [
                    'fullUrl' => 'urn:uuid:'.$compositionId,
                    'resource' => [
                        'resourceType' => 'Composition',
                        'id' => $compositionId,
                        'status' => 'final',
                        'type' => [
                            'coding' => [
                                [
                                    'system' => 'http://loinc.org',
                                    'code' => '11502-2',
                                    'display' => 'Laboratory Report',
                                ],
                            ],
                            'text' => 'Diagnostic Laboratory Report Composition',
                        ],
                        'subject' => [
                            'reference' => 'urn:uuid:'.$patientId,
                            'display' => $data['patient_name'] ?? 'Patient',
                        ],
                        'date' => $now,
                        'author' => [
                            [
                                'reference' => 'urn:uuid:'.$practitionerId,
                                'display' => $data['doctor_name'] ?? 'Practitioner',
                            ],
                        ],
                        'title' => 'Diagnostic Lab Report',
                        'section' => [
                            [
                                'title' => 'Diagnostic Results',
                                'code' => [
                                    'coding' => [
                                        [
                                            'system' => 'http://loinc.org',
                                            'code' => '30954-2',
                                            'display' => 'Relevant diagnostic tests',
                                        ],
                                    ],
                                ],
                                'entry' => [
                                    [
                                        'reference' => 'urn:uuid:'.$reportId,
                                        'display' => $data['test_name'] ?? 'Lab Test',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // 2. Patient Resource
                [
                    'fullUrl' => 'urn:uuid:'.$patientId,
                    'resource' => [
                        'resourceType' => 'Patient',
                        'id' => $patientId,
                        'identifier' => [
                            [
                                'type' => [
                                    'coding' => [
                                        [
                                            'system' => 'http://terminology.hl7.org/CodeSystem/v2-0203',
                                            'code' => 'MR',
                                        ],
                                    ],
                                ],
                                'system' => 'https://healthid.ndhm.gov.in',
                                'value' => $data['patient_abha_address'] ?? 'patient@sbx',
                            ],
                        ],
                        'name' => [
                            [
                                'text' => $data['patient_name'] ?? 'Patient',
                            ],
                        ],
                        'gender' => strtolower($data['patient_gender'] ?? 'M') === 'f' ? 'female' : 'male',
                        'birthDate' => $data['patient_dob'] ?? '1990-01-01',
                    ],
                ],
                // 3. Practitioner Resource
                [
                    'fullUrl' => 'urn:uuid:'.$practitionerId,
                    'resource' => [
                        'resourceType' => 'Practitioner',
                        'id' => $practitionerId,
                        'identifier' => [
                            [
                                'system' => 'https://hpr.abdm.gov.in',
                                'value' => $data['doctor_hpr_id'] ?? 'doc@hpr',
                            ],
                        ],
                        'name' => [
                            [
                                'text' => $data['doctor_name'] ?? 'Dr. HIMS Practitioner',
                            ],
                        ],
                    ],
                ],
                // 4. DiagnosticReport Resource
                [
                    'fullUrl' => 'urn:uuid:'.$reportId,
                    'resource' => [
                        'resourceType' => 'DiagnosticReport',
                        'id' => $reportId,
                        'status' => 'final',
                        'category' => [
                            [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/v2-0074',
                                        'code' => $data['test_category'] ?? 'LAB',
                                        'display' => 'Laboratory',
                                    ],
                                ],
                            ],
                        ],
                        'code' => [
                            'coding' => [
                                [
                                    'system' => 'http://loinc.org',
                                    'code' => '24331-1',
                                    'display' => $data['test_name'] ?? 'Laboratory Report Panel',
                                ],
                            ],
                            'text' => $data['test_name'] ?? 'Lab Panel',
                        ],
                        'subject' => [
                            'reference' => 'urn:uuid:'.$patientId,
                        ],
                        'issued' => $now,
                        'performer' => [
                            [
                                'reference' => 'urn:uuid:'.$practitionerId,
                                'display' => $data['doctor_name'] ?? 'Practitioner',
                            ],
                        ],
                        'result' => [
                            [
                                'reference' => 'urn:uuid:'.$observationId,
                            ],
                        ],
                    ],
                ],
                // 5. Observation Resource (Contains quantitative result value)
                [
                    'fullUrl' => 'urn:uuid:'.$observationId,
                    'resource' => [
                        'resourceType' => 'Observation',
                        'id' => $observationId,
                        'status' => 'final',
                        'code' => [
                            'coding' => [
                                [
                                    'system' => 'http://loinc.org',
                                    'code' => '24331-1',
                                    'display' => $data['test_name'] ?? 'Laboratory Observation',
                                ],
                            ],
                            'text' => $data['test_name'] ?? 'Lab Observation',
                        ],
                        'subject' => [
                            'reference' => 'urn:uuid:'.$patientId,
                        ],
                        'effectiveDateTime' => $now,
                        'valueQuantity' => [
                            'value' => (float) ($data['test_result_value'] ?? 0.0),
                            'unit' => $data['test_result_unit'] ?? '',
                            'system' => 'http://unitsofmeasure.org',
                            'code' => $data['test_result_unit'] ?? '',
                        ],
                        'interpretation' => [
                            [
                                'coding' => [
                                    [
                                        'system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation',
                                        'code' => $data['test_result_interpretation'] ?? 'N',
                                        'display' => 'Normal',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
