# 🗺️ ABDM ABHA (Milestone 1) Implemented APIs

This document details all the Ayushman Bharat Digital Mission (ABDM) ABHA Milestone 1 V3 API integrations implemented in this project. All endpoints are fully integrated into the interactive ABHA wizard, supporting both **Real ABDM Gateway Integration Mode** and **Simulated/Mock Offline Mode**.

---

## 🔑 1. Session Access Token API
Used to obtain the OAuth2 Bearer session token from the ABDM gateway.

*   **Service:** `App\Services\GatewayTokenService.php`
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /api/hiecm/gateway/v3/sessions
    ```

---

## 👤 2. Create ABHA Number (Enrollment) APIs
These APIs handle the multi-step registration wizard for creating a new ABHA health card.

*   **Service:** `App\Services\AbhaEnrollmentService.php`

### Generate Aadhaar OTP
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v3/enrollment/request/otp
    ```
*   **Payload Schema:**
    ```json
    {
      "scope": ["abha-enrol", "mobile-verify"],
      "loginHint": "aadhaar",
      "loginId": "{encryptedAadhaar}",
      "otpSystem": "aadhaar"
    }
    ```

### Verify Aadhaar OTP & Enroll
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v3/enrollment/enrol/byAadhaar
    ```
*   **Payload Schema:**
    ```json
    {
      "authData": {
        "authMethods": ["otp"],
        "otp": {
          "txnId": "{txnId}",
          "otpValue": "{encryptedOtp}"
        }
      },
      "consent": {
        "code": "abha-enrollment",
        "version": "1.4"
      }
    }
    ```

### Generate Mobile OTP (Communication Verification)
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v3/enrollment/request/otp
    ```
*   **Headers:** Includes `T-TOKEN: {txnId}`
*   **Payload Schema:**
    ```json
    {
      "scope": ["mobile-verify"],
      "loginHint": "mobile",
      "loginId": "{encryptedMobile}",
      "otpSystem": "abdm"
    }
    ```

### Verify Mobile OTP & Link
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v3/enrollment/enrol/byMobile
    ```
*   **Headers:** Includes `T-TOKEN: {txnId}`
*   **Payload Schema:**
    ```json
    {
      "otp": "{encryptedOtp}"
    }
    ```

---

## 🔍 3. Find Existing ABHA APIs
Used to locate existing patient records within ABDM databases.

*   **Service:** `App\Services\AbhaVerificationService.php`

### Search Existing ABHA by Mobile
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v3/profile/account/abha/search
    ```
*   **Payload Schema:**
    ```json
    {
      "scope": ["search-abha"],
      "mobile": "{encryptedMobile}"
    }
    ```

---

## 🔐 4. Verify ABHA Address (PHR Login) APIs
Verifies ownership of a custom ABHA address (`name@sbx`) via multiple channels to obtain the Linking Token.

*   **Service:** `App\Services\AbhaVerificationService.php`

### Search ABHA Address Methods
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v3/phr/web/login/abha/search
    ```
*   **Payload Schema:**
    ```json
    {
      "abhaAddress": "{abhaAddress}"
    }
    ```

### Request Verification OTP
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v3/phr/web/login/abha/request/otp
    ```
*   **Payload Schema:**
    ```json
    {
      "scope": ["abha-address-login"],
      "loginHint": "abha-address",
      "loginId": "{abhaAddress}",
      "otpSystem": "{aadhaar|abdm}"
    }
    ```

### Verify OTP & Get Session Token
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v3/phr/web/login/abha/verify
    ```
*   **Payload Schema:**
    ```json
    {
      "otp": "{encryptedOtp}",
      "txnId": "{txnId}"
    }
    ```

### Fetch Verified PHR Card Details
*   **Method:** `GET`
*   **Endpoint:**
    ```http
    /v3/phr/web/login/profile/abha/phr-card
    ```
*   **Headers:** Includes `X-token: Bearer {userSessionToken}`

---

## 📁 5. ABHA Card Download & QR Verify APIs

### Get/Download ABHA Card
*   **Service:** `App\Services\AbhaEnrollmentService.php`
*   **Method:** `GET`
*   **Endpoint:**
    ```http
    /v3/profile/account/qrCode
    ```
*   **Headers:** Includes `X-token: Bearer {userToken}`

### Demographic Match Verification
*   **Service:** `App\Services\AbhaVerificationService.php`
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v3/profile/login/verify
    ```

### QR Code Match Verification
*   **Service:** `App\Services\AbhaVerificationService.php`
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v3/profile/account/qrCode
    ```

---

## 📌 Implementation Summary

| Module | ABDM Sandbox V3 APIs | Status |
| :--- | :--- | :--- |
| **Gateway Authentication** | `/v3/sessions` | ✅ Implemented |
| **Create ABHA Number** | `/v3/enrollment/request/otp`<br>`/v3/enrollment/enrol/byAadhaar` | ✅ Implemented |
| **Mobile OTP Fallback** | `/v3/enrollment/enrol/byMobile` | ✅ Implemented |
| **Download ABHA Card** | `/v3/profile/account/qrCode` | ✅ Implemented |
| **Find Existing ABHA** | `/v3/profile/account/abha/search` | ✅ Implemented |
| **Verify ABHA Address** | `/v3/phr/web/login/abha/search`<br>`/v3/phr/web/login/abha/verify` | ✅ Implemented |
| **Demographics Verify** | `/v3/profile/login/verify` | ✅ Implemented |
| **QR Code Verification** | `/v3/profile/account/qrCode` | ✅ Implemented |
| **Real ABDM Gateway Mode** | Fully Integrated | ✅ Implemented |
| **Simulation Sandbox Mode** | Fully Integrated | ✅ Implemented |
