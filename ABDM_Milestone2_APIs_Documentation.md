# 🗺️ ABDM Milestone 2 (HIP & Health Information Exchange) — 9 Integration APIs

This document records the exact **9 ABDM integration APIs** implemented in this project for Milestone 2 HIP certification.

---

## 🔑 1. Outgoing APIs (HIP Calls ABDM Gateway)

*   **Service:** [HipLinkingService.php](file:///d:/PHP%20Laravel%20Projects/NHPR%20Demo/app/Services/HipLinkingService.php)

### 1. Link Care Contexts
Allows the HIP to link new patient clinical visit references to their ABHA.
*   **Method:** `POST`
*   **Gateway Endpoint:** `/v3/hip/link/care-contexts`
*   **Call Logic:** `linkCareContext($userToken, $patientAbhaAddress, $careContexts)`

### 2. On-Discover Response
Sends matching patient contexts back to ABDM Gateway asynchronously after discovery.
*   **Method:** `POST`
*   **Gateway Endpoint:** `/v3/hip/on-discover`
*   **Call Logic:** `onDiscoverResponse($txnId, $patientMatches)`

### 3. Notify Health Information Transfer Complete
Notifies ABDM Gateway that HIU records transfer has concluded.
*   **Method:** `POST`
*   **Gateway Endpoint:** `/v3/hip/health-information/notify`
*   **Call Logic:** `notifyHealthInformationTransfer($transactionId, $status)`

### 4. Notify Patient via SMS (Optional/Outgoing)
*   **Method:** `POST`
*   **Gateway Endpoint:** `/v3/hip/link/notify-sms`
*   **Call Logic:** `notifyPatientSms($mobile, $message)`

---

## 📲 2. Incoming Callback APIs (ABDM Calls HIP)

*   **Controller:** [HipController.php](file:///d:/PHP%20Laravel%20Projects/NHPR%20Demo/app/Http/Controllers/HipController.php)
*   **CSRF Exclusion:** Enabled for `v3/*` in `bootstrap/app.php`.

### 5. Discover Patient Care Contexts
*   **Method:** `POST`
*   **Local Endpoint:** `/v3/hip/discover`
*   **Action:** Looks up matching patient records and calls the outbound `onDiscoverResponse` callback.

### 6. Link Init (Link Initialization Request)
*   **Method:** `POST`
*   **Local Endpoint:** `/v3/hip/link/init`
*   **Action:** Simulates initialization and OTP dispatch.

### 7. Link Confirm (Link OTP Confirmation)
*   **Method:** `POST`
*   **Local Endpoint:** `/v3/hip/link/confirm`
*   **Action:** Validates OTP and links contexts.

### 8. Consent Notification
*   **Method:** `POST`
*   **Local Endpoint:** `/v3/consents/hip/notify`
*   **Action:** Saves/updates the Consent policy in the database.

### 9. Health Information Request
*   **Method:** `POST`
*   **Local Endpoint:** `/v3/health-information/hip/request`
*   **Action:** Checks consent status, packages FHIR bundle data, runs native ECDH key derivation, encrypts using AES-GCM-256, POSTs data to HIU push URL, and sends transfer notification.
