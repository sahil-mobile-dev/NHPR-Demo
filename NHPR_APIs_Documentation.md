# 🗺️ ABDM HPR (Doctor & Nurse) Implemented APIs

This document details all the Ayushman Bharat Digital Mission (ABDM) Healthcare Professional Registry (HPR) API integrations implemented in this project. All endpoints are fully integrated into the multi-step HPR Onboarding Wizard, supporting both **Real ABDM Gateway Integration Mode** and **Simulated/Mock Offline Mode**.

---

### 🔑 1. Gateway Access Token API
Used to obtain the OAuth2 Bearer token from the ABDM gateway using `clientId` and `clientSecret`.

*   **Service:** `App\Services\GatewayTokenService.php`
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /api/hiecm/gateway/v3/sessions
    ```

---

### 🪪 2. Aadhaar Verification APIs
Handles requesting and verifying OTPs sent to Aadhaar-registered mobile numbers.

*   **Service:** `App\Services\AadhaarOTPService.php`

#### Generate Aadhaar OTP
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v4/int/v2/registration/aadhaar/generateOtp
    ```

#### Verify Aadhaar OTP & Pull KYC
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v4/int/v2/registration/aadhaar/verifyOTP
    ```

---

### 📱 3. Mobile Number Verification APIs
Compares the practitioner's workspace mobile with Aadhaar, with an OTP fallback check if demographic validation fails.

*   **Service:** `App\Services\MobileOTPService.php`

#### Match Mobile (Demographic Auth)
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v4/int/v2/registration/aadhaar/demographicAuthViaMobile
    ```

#### Generate Mobile OTP (Fallback)
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v4/int/v1/registration/aadhaar/generateMobileOTP
    ```

#### Verify Mobile OTP (Fallback)
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v4/int/v1/registration/aadhaar/verifyMobileOTP
    ```

---

### 👤 4. HPR ID Profile & Account APIs
Checks account existence, gets domain name suggestions, and creates the practitioner `@hpr.abdm` identifier.

*   **Service:** `App\Services\HprAccountService.php`

#### Check if HPR ID Already Exists
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v4/int/v1/registration/aadhaar/checkHpIdAccountExist
    ```

#### HPR Username Suggestions
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v4/int/v1/registration/aadhaar/hpid/suggestion
    ```

#### Create HPR ID (Pre-Verified)
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v4/int/v2/registration/aadhaar/createHprIdWithPreVerified
    ```

---

### 🏥 5. Health Facility Registry (HFR) API
Used to search registered health facilities to map the practitioner's workplace.

*   **Service:** `App\Services\HfrFacilityService.php`
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v4/int/FacilityManagement/v1.5/facility/search
    ```

---

### 🎓 6. Practitioner Registration API
Submits academic degrees, state registration council credentials, and links work details.

*   **Service:** `App\Services\HfrFacilityService.php`
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v4/int/apis/v1/doctors/register-professional-new
    ```

---

### 📁 7. Document Upload APIs
Retrieves document checklist block identifiers and uploads Base64 degree/registration files.

*   **Service:** `App\Services\HprDocumentService.php`

#### Fetch Document Blocks Checklist
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v4/int/apis/v1/doctors/fetch-documents-list
    ```

#### Upload Document Attachment
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /v4/int/apis/v1/uploads/upload-document
    ```

---

### 🔍 8. Application Status Tracking API
Used to retrieve live verification stages and progress steps of an onboarding application.

*   **Service:** `App\Http\Controllers\NhprRegistrationController.php`
*   **Method:** `POST`
*   **Endpoint:**
    ```http
    /nhpr/track
    ```

---

## 📌 Implementation Summary

| Module | Status |
| :--- | :--- |
| **Gateway Authentication** | ✅ Implemented |
| **Aadhaar OTP Generation & Verification** | ✅ Implemented |
| **Mobile Verification & OTP** | ✅ Implemented |
| **HPR Username Suggestions** | ✅ Implemented |
| **HPR ID Creation** | ✅ Implemented |
| **HFR Facility Search** | ✅ Implemented |
| **Practitioner Registration** | ✅ Implemented |
| **Document Fetch & Upload** | ✅ Implemented |
| **Application Status Tracking** | ✅ Implemented |
| **Real ABDM Gateway Mode** | ✅ Implemented |
| **Mock/Offline Simulation Mode** | ✅ Implemented |

