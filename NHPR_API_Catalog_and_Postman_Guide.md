# 📖 NHPR & ABDM API Catalog & Mobile Integration Guide

This guide provides a comprehensive catalog of all **Web REST endpoints** and **ABDM Gateway Integration APIs** included in the **NHPR Demo** project. It accompanies the generated Postman collection: [`nhpr_abdm_postman_collection.json`](file:///d:/PHP%20Laravel%20Projects/NHPR%20Demo/nhpr_abdm_postman_collection.json).

---

## 🚀 1. Quick Start: Postman Collection Import

### Step 1: Import the Collection File
1. Open **Postman**.
2. Click **Import** (top left).
3. Drag and drop [`nhpr_abdm_postman_collection.json`](file:///d:/PHP%20Laravel%20Projects/NHPR%20Demo/nhpr_abdm_postman_collection.json) or click **Files** and select it.
4. Click **Import**.

### Step 2: Configure Collection / Environment Variables
The collection uses standard Postman variables. You can set them in your active Environment or directly under **Collection Settings -> Variables**:

| Variable Name | Example Local Value | Example ABDM Sandbox Value | Description |
| :--- | :--- | :--- | :--- |
| `base_url` | `http://localhost:8000` | `https://your-domain.com` | Base URL of your Laravel Web App backend server. |
| `gateway_base_url` | `https://dev.abdm.gov.in` | `https://dev.abdm.gov.in` | ABDM Official Gateway base endpoint URL. |
| `client_id` | `SBX_001234` | `SBX_001234` | Your HIMS Client ID registered on ABDM Sandbox. |
| `client_secret` | `sec_abc123xyz` | `sec_abc123xyz` | Your HIMS Client Secret for Gateway authentication. |
| `access_token` | *(auto-populated)* | *(auto-populated)* | Bearer OAuth token obtained from Session API. |
| `user_token` | *(auto-populated)* | *(auto-populated)* | User Session token (`X-Token`) obtained during ABHA login. |
| `x_cm_id` | `sbx` | `sbx` | ABDM Consent Manager ID (usually `sbx`). |
| `txn_id` | *(dynamic)* | *(dynamic)* | Transaction UUID returned during multi-step OTP flows. |
| `hpr_id` | `doctor@hpr.abdm` | `doctor@hpr.abdm` | HPR practitioner username handle. |
| `abha_address` | `patient@sbx` | `patient@sbx` | Patient PHR address. |

---

## 📑 2. API Catalog by Module

---

### 🔑 Module 1: Gateway Token & Credentials Management

Handles obtaining OAuth2 Bearer tokens from the ABDM Gateway and managing client credentials dynamically.

| # | Endpoint | Method | Purpose |
| :- | :--- | :- | :--- |
| 1 | `/nhpr/token` | `GET` | Render credentials configuration web view. |
| 2 | `/nhpr/token` | `POST` | Generate ABDM Gateway Bearer session token via backend proxy. |
| 3 | `/nhpr/token/credentials` | `POST` | Save Client ID, Client Secret, Base URL, and X-CM-ID to session/config. |
| 4 | `/nhpr/token/credentials/clear` | `POST` | Clear stored credentials from session. |
| 5 | `/api/hiecm/gateway/v3/sessions` | `POST` | Direct ABDM Gateway OAuth2 token generation call. |

#### Sample Request Payload (Generate Token):
```json
{
  "clientId": "SBX_001234",
  "clientSecret": "sec_abc123xyz"
}
```

---

### 🩺 Module 2: NHPR Practitioner Onboarding (Doctor & Nurse)

Coordinates multi-step practitioner registration, Aadhaar identity verification, eKYC fetch, document attachment uploads, and application tracking.

| # | Endpoint | Method | Key Parameters |
| :- | :--- | :- | :--- |
| 1 | `/nhpr/register` | `GET` | Render Onboarding Stepper Wizard interface. |
| 2 | `/nhpr/register/toggle-mode` | `POST` | `real_api_mode` (boolean) to switch live/simulated mode. |
| 3 | `/nhpr/register/aadhaar/generate-link` | `POST` | `aadhaarNumber` (12 digits) |
| 4 | `/nhpr/register/aadhaar/check-status` | `POST` | `refNo` |
| 5 | `/nhpr/register/masters/ministries` | `POST` | Returns Govt Ministries master list. |
| 6 | `/nhpr/register/aadhaar/send-otp` | `POST` | `aadhaarNumber` |
| 7 | `/nhpr/register/aadhaar/verify-otp` | `POST` | `txnId`, `otp` |
| 8 | `/nhpr/register/mobile/verify` | `POST` | `mobile` (Demographic match check) |
| 9 | `/nhpr/register/mobile/verify-otp` | `POST` | `txnId`, `otp` (Fallback mobile OTP) |
| 10 | `/nhpr/register/suggestions` | `POST` | `firstName`, `lastName` |
| 11 | `/nhpr/register/create-id` | `POST` | `hprId`, `password` |
| 12 | `/nhpr/register/facility/search` | `POST` | `query`, `stateCode`, `districtCode` |
| 13 | `/nhpr/register/professional/submit` | `POST` | `degree`, `councilName`, `registrationNumber`, `facilityId` |
| 14 | `/nhpr/register/documents/fetch` | `POST` | Returns required document upload checklist. |
| 15 | `/nhpr/register/documents/upload` | `POST` | `docType`, Base64 encoded `file` |
| 16 | `/nhpr/register/fetch-hpr-profile` | `POST` | `hprId` |
| 17 | `/nhpr/register/link-existing-hpr` | `POST` | `hprId` |
| 18 | `/nhpr/register/link-existing-hpr/send-otp` | `POST` | `hprId` |
| 19 | `/nhpr/register/link-existing-hpr/verify-mobile-otp` | `POST` | `txnId`, `otp` |
| 20 | `/nhpr/track` | `GET/POST` | `applicationId` or `hprId` (Returns approval stages & status) |

---

### 🏥 Module 3: HFR (Health Facility Registry) & Master Data

Enables searching, registering, and tracking health facilities, along with LGD geographic codes (States, Districts, Subdistricts) and medical specialties masters.

| # | Endpoint | Method | Description |
| :- | :--- | :- | :--- |
| 1 | `/nhpr/hfr` | `GET` | Facility Manager Dashboard. |
| 2 | `/nhpr/hfr/search` | `POST` | Search facility by name, state, and district. |
| 3 | `/nhpr/hfr/create` | `POST` | Register a new healthcare facility in HFR. |
| 4 | `/nhpr/hfr/link` | `POST` | Link facility to ABDM HIMS Bridge ID. |
| 5 | `/nhpr/hfr/track` | `POST` | Track status of facility registration application. |
| 6 | `/nhpr/hfr/hpr-login` | `POST` | Facility Manager login using HPR credentials. |
| 7 | `/nhpr/hfr/hpr-logout` | `POST` | Logout facility manager. |
| 8 | `/nhpr/hfr/masters/types` | `GET` | List facility master categories. |
| 9 | `/nhpr/hfr/masters/data` | `GET` | Fetch general master data options. |
| 10 | `/nhpr/hfr/masters/states` | `GET` | LGD States list. |
| 11 | `/nhpr/hfr/masters/districts` | `GET` | LGD Districts list (`?stateCode=05`). |
| 12 | `/nhpr/hfr/masters/subdistricts` | `GET` | LGD Subdistricts list (`?districtCode=001`). |
| 13 | `/nhpr/hfr/masters/facility-types` | `POST` | Facility types filtered by system of medicine. |
| 14 | `/nhpr/hfr/masters/owner-subtypes` | `POST` | Owner categories list. |
| 15 | `/nhpr/hfr/masters/specialities` | `POST` | Medical specialties master. |
| 16 | `/nhpr/hfr/masters/facility-subtypes` | `POST` | Subtypes for selected facility type. |

---

### 🪪 Module 4: ABHA Card & Address (Enrollment, Finder & Verifier)

Provides ABHA Milestone 1 (V3) integration for patient health card creation, mobile search, and login verification.

| # | Endpoint | Method | Payload / Details |
| :- | :--- | :- | :--- |
| 1 | `/abha/create/request-otp` | `POST` | `{"aadhaarNumber": "999999999999"}` |
| 2 | `/abha/create/verify-otp` | `POST` | `{"txnId": "...", "otp": "123456"}` |
| 3 | `/abha/create/request-mobile-otp` | `POST` | `{"txnId": "...", "mobile": "9876543210"}` |
| 4 | `/abha/create/verify-mobile-otp` | `POST` | `{"txnId": "...", "otp": "123456"}` |
| 5 | `/abha/card/download` | `POST` | `{"userToken": "..."}` -> Returns ABHA Card image & QR code. |
| 6 | `/abha/find/search-mobile` | `POST` | `{"mobile": "9876543210"}` -> Lists linked ABHA cards. |
| 7 | `/abha/find/select` | `POST` | `{"healthId": "91-1234-5678-9012"}` |
| 8 | `/abha/verify/search` | `POST` | `{"abhaAddress": "patient@sbx"}` -> Queries auth methods. |
| 9 | `/abha/verify/request-otp` | `POST` | `{"abhaAddress": "...", "otpSystem": "aadhaar"}` |
| 10 | `/abha/verify/confirm` | `POST` | `{"txnId": "...", "otp": "123456"}` -> Returns session `X-Token`. |
| 11 | `/abha/verify/qr` | `POST` | `{"qrData": "..."}` -> Scans and verifies ABHA QR code. |
| 12 | `/abha/verify/demographics` | `POST` | `{"name": "...", "gender": "M", "dob": "...", "mobile": "..."}` |

---

### 🏥 Module 5: HIP (Health Information Provider) Care Context Linking & Exchange

Allows hospitals/clinics to register clinical visits as care contexts under patient ABHA numbers and process health data requests.

| # | Endpoint | Method | Description |
| :- | :--- | :- | :--- |
| 1 | `/hip/record/create` | `POST` | Create visit record locally (`patientId`, `careContextName`, `abhaAddress`). |
| 2 | `/hip/link` | `POST` | Trigger care context linking with Gateway (`abhaAddress`, `careContexts`). |
| 3 | `/hip/consents` | `GET` | Fetch list of consent policies and audit trail logs. |
| 4 | `/hip/consents/register` | `POST` | Simulate/Register active consent on HIP. |
| 5 | `/hip/consents/exchange` | `POST` | Encrypt and push FHIR bundle to HIU push URL. |
| 6 | `/hip/simulator/trigger-discovery` | `POST` | Test discovery callback locally. |
| 7 | `/api/hiecm/gateway/v3/hip/link/care-contexts` | `POST` | Outbound ABDM Gateway call for linking patient care contexts. |
| 8 | `/api/hiecm/gateway/v3/hip/on-discover` | `POST` | Outbound ABDM Gateway call sending discovered patient matches. |

---

### 📥 Module 6: HIU (Health Information User) Consent Manager & Health Data Requests

Allows doctors/hospitals to request patient medical records from other health facilities with patient consent.

| # | Endpoint | Method | Payload / Details |
| :- | :--- | :- | :--- |
| 1 | `/hiu/consent/request` | `POST` | `{"patientAbha": "patient@sbx", "purpose": "REFERRAL", "hiTypes": ["Prescription"], "dateFrom": "...", "dateTo": "..."}` |
| 2 | `/hiu/consent/fetch/{id}` | `POST` | Fetch signed consent artefact by ID. |
| 3 | `/hiu/health-information/request` | `POST` | `{"consentArtefactId": "...", "dateFrom": "...", "dateTo": "..."}` |
| 4 | `/hiu/records/{abha_address}` | `GET` | Returns decrypted FHIR clinical records for a patient. |
| 5 | `/hiu/consent/revoke/{consentId}` | `POST` | Revokes consent status locally. |
| 6 | `/hiu/simulator/approve-consent` | `POST` | Simulates patient approving consent. |
| 7 | `/hiu/simulator/deny-consent` | `POST` | Simulates patient denying consent. |
| 8 | `/hiu/simulator/revoke-consent` | `POST` | Simulates consent revocation. |
| 9 | `/hiu/simulator/push-health-data` | `POST` | Simulates pushing FHIR health data package. |

---

### ⚡ Module 7: ABDM Webhook Callbacks (v3 / api/v3)

Public callback endpoints exposed by the server to receive asynchronous webhooks from ABDM Gateway. CSRF protection is bypassed for this route group (`/v3/*` and `/api/v3/*`).

| Endpoint | Method | Event Triggered by Gateway |
| :--- | :--- | :--- |
| `/v3/hip/discover` | `POST` | ABDM Gateway queries HIP for matching patient records. |
| `/v3/hip/link/init` | `POST` | Gateway initiates care context linking & OTP dispatch. |
| `/v3/hip/link/confirm` | `POST` | Gateway confirms OTP and links context. |
| `/v3/consents/hip/notify` | `POST` | Gateway notifies HIP of newly granted/revoked consent. |
| `/v3/health-information/hip/request` | `POST` | Gateway requests HIP to package & encrypt FHIR data. |
| `/v3/consent/on-init` | `POST` | Gateway returns generated Consent Request ID. |
| `/v3/consent/notify` | `POST` | Gateway notifies HIU when patient approves consent. |
| `/v3/health-information/on-request` | `POST` | Gateway/HIP pushes encrypted health data to HIU callback. |

---

## 📱 3. Mobile App Integration Instructions (Flutter / Android / iOS)

### 1. Mandatory Headers for Outbound Gateway Requests
When making direct calls or proxy calls to ABDM Gateway, the following headers are strictly required:

```http
Authorization: Bearer <access_token>
Content-Type: application/json
Accept: application/json
X-CM-ID: sbx
REQUEST-ID: <unique-uuid-v4>
TIMESTAMP: <ISO8601-UTC-timestamp>
```

- In **Flutter** (using `dio` or `http`):
  ```dart
  Map<String, String> getAbdmHeaders(String accessToken) {
    return {
      'Authorization': 'Bearer $accessToken',
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CM-ID': 'sbx',
      'REQUEST-ID': const Uuid().v4(),
      'TIMESTAMP': DateTime.now().toUtc().toIso8601String(),
    };
  }
  ```

### 2. Token Reuse & Expiry Handling
- Gateway access tokens expire in **3600 seconds (1 hour)**.
- Cache the `accessToken` in secure storage (e.g. `flutter_secure_storage` or `EncryptedSharedPreferences`).
- Refresh token automatically when receiving HTTP status `401 Unauthorized`.

### 3. Asynchronous Webhooks & Push Notifications
- ABDM relies heavily on **asynchronous request-response architecture**.
- When an API request (such as Consent Request or Discovery) returns HTTP `202 Accepted`, the actual result will arrive via incoming webhook (`/v3/consent/notify` or `/v3/hip/discover`).
- For mobile applications, pair backend webhooks with **Firebase Cloud Messaging (FCM)** or WebSocket channels to push status updates to the mobile UI in real time.

---

## 🛡️ Security & PSR-12 Compliance
- Secrets, client credentials, and access tokens are read dynamically from `config()` / `.env` and must never be committed to repository code.
- All requests adhere to strict validation and exception logging standards.
