# ABDM Milestone 3 - API Calls & Endpoints Specification

This document provides a detailed specification of all outbound APIs, incoming Gateway webhooks/callbacks, simulation endpoints, configuration parameters, and architectural patterns used under **Milestone 3: Health Information User (HIU)** integration for the ParaCare+ HIMS.

---

## 1. Environment & Configuration Variables

Access credentials and base URLs are configured in `.env` and loaded dynamically via `config/services.php`:

| Config Path | Env Variable | Default Value | Description |
| :--- | :--- | :--- | :--- |
| `services.nhpr.base_url` | `NHPR_BASE_URL` | `https://dev.abdm.gov.in` | Base Gateway URL for outbound calls. |
| `services.nhpr.client_id` | `NHPR_CLIENT_ID` | — | HIMS Client ID registered on ABDM Sandbox. |
| `services.nhpr.client_secret` | `NHPR_CLIENT_SECRET` | — | HIMS Client Secret for token generation. |
| `services.nhpr.x_cm_id` | `NHPR_X_CM_ID` | `sbx` | Consent Manager ID (usually `sbx`). |
| `services.nhpr.real_api_mode` | `NHPR_REAL_API_MODE` | `false` | Switches dynamically between live and simulated modes. |

---

## 2. Outbound APIs (HIMS $\rightarrow$ ABDM Gateway)

All outbound requests require a secure token generated from the session endpoint.

### 2.1 Gateway session token generation
- **HTTP Method**: `POST`
- **Endpoint**: `{{base_url}}/api/hiecm/gateway/v3/sessions`
- **Request Body**:
  ```json
  {
    "clientId": "your-client-id",
    "clientSecret": "your-client-secret"
  }
  ```
- **Response Body**:
  ```json
  {
    "accessToken": "eyJhbGciOi...",
    "expiresIn": 3600,
    "tokenType": "bearer"
  }
  ```

### 2.2 Create Consent Request
- **HTTP Method**: `POST`
- **Endpoint**: `{{base_url}}/api/hiecm/gateway/v3/consent/request`
- **Headers**:
  ```http
  Authorization: Bearer <accessToken>
  REQUEST-ID: <uuid>
  TIMESTAMP: <ISO8601-timestamp>
  X-CM-ID: sbx
  Content-Type: application/json
  ```
- **Request Body**:
  ```json
  {
    "consent": {
      "purpose": {
        "code": "REFERRAL",
        "text": "General Consultation"
      },
      "patient": {
        "id": "patient@sbx"
      },
      "hiu": {
        "id": "your-client-id"
      },
      "requester": {
        "name": "Dr. Uttarakhand HIMS",
        "identifier": {
          "type": "REGNO",
          "value": "UK-HIMS-99",
          "system": "https://healthid.ndhm.gov.in"
        }
      },
      "hiTypes": ["Prescription", "DiagnosticReport"],
      "permission": {
        "accessMode": "VIEW",
        "dateRange": {
          "from": "2025-01-01T00:00:00Z",
          "to": "2026-07-01T00:00:00Z"
        },
        "dataEraseAt": "2026-07-10T00:00:00Z",
        "frequency": {
          "unit": "HOUR",
          "value": 1,
          "repeats": 0
        }
      }
    }
  }
  ```
  *(Note: The Purpose code is dynamically mapped from HIMS workflows: `General Consultation`/`Referral` $\rightarrow$ `REFERRAL`, `Emergency Care` $\rightarrow$ `BTG`, `Chronic Monitoring` $\rightarrow$ `CAREMGT`.)*

### 2.3 Fetch Consent Artefact
- **HTTP Method**: `POST`
- **Endpoint**: `{{base_url}}/api/hiecm/gateway/v3/consent/fetch`
- **Headers**: Same as 2.2.
- **Request Body**:
  ```json
  {
    "consentId": "consent-artefact-uuid-123"
  }
  ```

### 2.4 Request Health Information
- **HTTP Method**: `POST`
- **Endpoint**: `{{base_url}}/api/hiecm/gateway/v3/health-information/request`
- **Headers**: Same as 2.2.
- **Request Body**:
  ```json
  {
    "hiRequest": {
      "consent": {
        "id": "consent-artefact-uuid-123"
      },
      "dateRange": {
        "from": "2025-01-01T00:00:00Z",
        "to": "2026-07-01T00:00:00Z"
      },
      "dataPushUrl": "http://your-hims-domain/v3/health-information/on-request",
      "keyMaterial": {
        "cryptoAlg": "ECDH",
        "curve": "Curve25519",
        "dhPublicKey": {
          "expiry": "2026-07-10T00:00:00Z",
          "parameters": "Curve25519/32byte",
          "keyValue": "base64-encoded-public-key"
        },
        "nonce": "base64-encoded-random-nonce"
      }
    }
  }
  ```

### 2.5 Health Information Transfer Notification
- **HTTP Method**: `POST`
- **Endpoint**: `{{base_url}}/api/hiecm/gateway/v3/health-information/notify`
- **Headers**: Same as 2.2.
- **Request Body**:
  ```json
  {
    "notification": {
      "transactionId": "transaction-uuid-456",
      "status": "DELIVERED",
      "decryptionKeyStatus": "OK"
    }
  }
  ```

---

## 3. Incoming Callback Webhooks (ABDM / HIPs $\rightarrow$ HIMS)

These are public endpoints exposed by our HIMS web server to receive responses from ABDM. CSRF token validation is bypassed in `bootstrap/app.php` for this route group.

### 3.1 Consent On-Init Callback
- **HTTP Method**: `POST`
- **Endpoint**: `/v3/consent/on-init`
- **Payload**:
  ```json
  {
    "requestId": "callback-uuid-789",
    "timestamp": "2026-07-03T12:00:00Z",
    "consentRequest": {
      "id": "consent-request-uuid-from-abdm"
    },
    "resp": {
      "requestId": "original-local-request-id"
    }
  }
  ```

### 3.2 Consent Notify Callback
- **HTTP Method**: `POST`
- **Endpoint**: `/v3/consent/notify`
- **Payload**:
  ```json
  {
    "requestId": "notify-uuid-888",
    "timestamp": "2026-07-03T12:00:05Z",
    "notification": {
      "consentRequestId": "consent-request-uuid-from-abdm",
      "status": "GRANTED",
      "consentArtefacts": [
        {
          "id": "consent-artefact-uuid-123"
        }
      ]
    }
  }
  ```
  *(Note: When a status of `GRANTED` is received, the HIMS automatically dispatches `FetchConsentArtefactJob` to query the Gateway in the background.)*

### 3.3 Receive Health Data Callback
- **HTTP Method**: `POST`
- **Endpoint**: `/v3/health-information/on-request`
- **Payload**:
  ```json
  {
    "pageNumber": 1,
    "pageCount": 1,
    "transactionId": "transaction-uuid-456",
    "entries": [
      {
        "content": "encrypted-aes-gcm-base64-ciphertext-here",
        "media": "application/fhir+json",
        "checksum": "checksum-string"
      }
    ],
    "keyMaterial": {
      "cryptoAlg": "ECDH",
      "curve": "Curve25519",
      "dhPublicKey": {
        "keyValue": "sender-public-key-base64",
        "parameters": "sender-iv-base64"
      },
      "nonce": "sender-tag-base64"
    }
  }
  ```
  *(Note: Upon receipt of this payload, the HIMS immediately returns a `202 ACCEPTED` response and offloads the decryption, signature check, FHIR bundle parsing, and database storage to the asynchronous queue via `ProcessHealthDataJob`.)*

---

## 4. Architectural & Implementation Specifications

### 4.1 Internal Service Layers
To keep controllers lightweight and maintain SOC (Separation of Concerns), the following backend services are deployed:
- **`GatewayTokenService`**: Caches and validates the ABDM authentication bearer tokens, managing auto-renewal on token expiry.
- **`HiuConsentService`**: Handles consent request creation, status inquiries, and artefact fetching.
- **`HiuHealthInformationService`**: Conducts data-key creation, data request posts, and logs processed transfers.
- **`FideliusService`**: Manages secure ECDH keypair derivation and AES-GCM decryption.
- **`FhirParserService`**: Dissects HL7 FHIR R4 Bundle envelopes into relational records.
- **`HiuAuditService`**: Logs all incoming/outgoing payloads for sandbox tracking.

### 4.2 State Machine Transitions
Consent and Health Records transaction states transition as follows:
```text
[CREATE REQUEST] ➔ CREATED ➔ PENDING (Waiting on patient approval)
                    │
                    ├─➔ DENIED (Patient rejects request)
                    │
                    └─➔ GRANTED ➔ ARTEFACT_FETCHED (Active policy retrieved)
                                     │
                                     ├─➔ REQUESTED (HIU requests health records)
                                     │     │
                                     │     ├─➔ DATA_RECEIVED (Encrypted FHIR pushed)
                                     │     │     │
                                     │     │     └─➔ DECRYPTED ➔ COMPLETED (Fully parsed)
                                     │     │
                                     │     └─➔ FAILED (Decryption error or timeout)
                                     │
                                     └─➔ REVOKED (Wiped locally / revoked by patient)
```

### 4.3 FHIR Resource Mapping & Relational Tables
Decrypted bundles are parsed and distributed into the following database tables:
- **`prescriptions`**: Extracts `MedicationRequest` code, display name, dosage instructions, timing parameters, author details, and facility displays.
- **`diagnostic_reports`**: Extracts `DiagnosticReport` test name, category, status (`result_status`), conclusions, and diagnostic doctor.
- **`observations`**: Extracts `Observation` code, display unit, value, date, and status (e.g., vital signs, lab values).
- **`encounters`**: Extracts `Encounter` start/end date, class code, practitioner reference, and clinic metadata.
- **`health_documents`**: Extracts `DocumentReference` titles, file contents (base64/PDF/HTML), and composition details.

### 4.4 Audit Logs
ABDM interactions are fully tracked in these logging tables:
- **`hiu_consent_logs`**: Logs request/response payloads for `/v3/consent` endpoints.
- **`hiu_request_logs`**: Logs transactions for `/v3/health-information` records exchange.
- **`hiu_abdm_transactions`**: Audit table monitoring record count, request directions, and cryptographic parameters.

---

## 5. UI-Level AJAX & Simulation APIs

Used internally by HIMS interface views to trigger actions and run simulation flows.

| Method | Endpoint | Controller Action | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/hiu` | `HiuController@showDashboard` | Renders console dashboards and tables. |
| `POST` | `/hiu/consent/request` | `HiuController@requestConsent` | Form submission to request consent. |
| `POST` | `/hiu/consent/fetch/{id}` | `HiuController@fetchArtefact` | Fetches artefacts from Gateway. |
| `POST` | `/hiu/health-information/request` | `HiuController@requestHealthData` | Submits outgoing transaction requests. |
| `GET` | `/hiu/records/{abha_address}` | `HiuController@showRecords` | Timeline clinical records viewer. |
| `POST` | `/hiu/consent/revoke/{consentId}` | `HiuController@revokeConsentLocal` | Local revocation & wipes database files. |
| `POST` | `/hiu/simulator/approve-consent` | `HiuController@simulateApproveConsent` | Simulator: Patient GRANTED webhook dispatch. |
| `POST` | `/hiu/simulator/deny-consent` | `HiuController@simulateDenyConsent` | Simulator: Patient DENIED webhook dispatch. |
| `POST` | `/hiu/simulator/revoke-consent` | `HiuController@simulateRevokeConsent` | Simulator: Patient REVOKED webhook dispatch. |
| `POST` | `/hiu/simulator/push-health-data` | `HiuController@simulatePushHealthData` | Simulator: Encrypted data push webhook dispatch. |
