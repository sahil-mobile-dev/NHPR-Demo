# NHPR New Healthcare Professional Registration: Technical Integration Specification

This document details the technical integration specification for the **New Healthcare Professional Registration (Doctor/Nurse) Flow**. It maps the sequence of ABDM Sandbox APIs, request payloads, response schemas, encryption standards, and user interface transitions.

---

## 1. Architectural & Styling Foundation

The registration flow is built as a multi-step wizard following the **Uttarakhand State Health Intelligence Platform design system** detailed in **[NHPR_UI_Design_System.md](file:///d:/PHP%20Laravel%20Projects/NHPR%20Demo/NHPR_UI_Design_System.md)**.

### Screen Flow & State Navigation
We will use a unified wizard view with a sidebar navigation, a sticky government top banner, and a step indicator (`.stepper` / `.step`) showing progress:

```text
Step 1: Aadhaar Input & OTP Generation
   ↓ (Generates Aadhaar OTP)
Step 2: OTP Verification & Profile Verification
   ↓ (Verifies Aadhaar + Checks existing HPR ID)
Step 3: Contact Verification (Demographic check with fallback Mobile OTP)
   ↓ (Verifies Mobile number)
Step 4: Username Selection & Credential Setup
   ↓ (Pulls suggestions, creates HPR ID, & returns HPR Token)
Step 5: Professional & Academic Details
   ↓ (Captures registration council, degrees, categories)
Step 6: Facility Mapping & Work Profile
   ↓ (Searches HFR database & registers practitioner profile)
Step 7: Certificate & Document Upload
   ↓ (Fetches required checklist and uploads base64 documents)
Step 8: Review & Success Screen
```

---

## 2. Global Encryption & Authorization Standards

### Gateway Authorization Token
Every registry API requires the Gateway Access Token generated in Step 1.
- **Header**: `Authorization: Bearer <GATEWAY_ACCESS_TOKEN>`
- **Cache Strategy**: Reuse the token from cache using `GatewayTokenService->getValidToken()` until expiration.

### Data Encryption
Sensitive parameters (**Aadhaar Number**, **Mobile Number**, **Email**, **Password**, and **OTP**) must be encrypted using the public key from the ABDM Certificate API before transmission.
- **Public Certificate Endpoint**: `https://apihspsbx.abdm.gov.in/v4/int/api/v1/auth/cert` (GET)
- **Encryption Algorithm**: RSA
- **Cipher Type**: `RSA/ECB/PKCS1Padding`
- **Signature Algorithm**: `RS512`
- **Output format**: Base64 encoded string

---

## 3. Step-by-Step API Specification

### Step 1: Generate Gateway Access Token
Generates the core authorization token using client credentials.
- **Method**: `POST`
- **Endpoint**: `https://dev.abdm.gov.in/api/hiecm/gateway/v3/sessions`
- **Headers**:
  - `REQUEST-ID`: UUID
  - `TIMESTAMP`: ISO-8601 Timestamp
  - `X-CM-ID`: `sbx`
- **Request Body**:
  ```json
  {
    "clientId": "YOUR_CLIENT_ID",
    "clientSecret": "YOUR_CLIENT_SECRET",
    "grantType": "client_credentials"
  }
  ```
- **Response**: `accessToken`, `expiresIn`, `refreshToken`, `tokenType`

---

### Step 2: Generate Aadhaar OTP
Requests an OTP to the user's Aadhaar-registered mobile number.
- **Method**: `POST`
- **Endpoint**: `https://apihspsbx.abdm.gov.in/v4/int/v2/registration/aadhaar/generateOtp`
- **Request Body**:
  ```json
  {
    "aadhaar": "ENCRYPTED_AADHAAR_NUMBER_BASE64"
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "txnId": "38022xxx-7xxx-43ba-91ff-4471a25e9xxx",
    "mobileNumber": "9999999999"
  }
  ```

---

### Step 3: Verify Aadhaar OTP
Verifies the Aadhaar OTP to retrieve demographic data.
- **Method**: `POST`
- **Endpoint**: `https://apihspsbx.abdm.gov.in/v4/int/v2/registration/aadhaar/verifyOTP`
- **Request Body**:
  ```json
  {
    "domainName": "@hpr.abdm",
    "idType": "hpr_id",
    "otp": "ENCRYPTED_OTP_BASE64",
    "restrictions": "",
    "txnId": "38022xxx-7xxx-43ba-91ff-4471a25e9xxx"
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "txnId": "3802219b-745c-4xxx-91ff-4471a25exxx",
    "mobileNumber": null
  }
  ```

---

### Step 4: Check if HPR ID Already Exists
Checks if the verified Aadhaar is already linked to an HPR account.
- **Method**: `POST`
- **Endpoint**: `https://apihspsbx.abdm.gov.in/v4/int/v1/registration/aadhaar/checkHpIdAccountExist`
- **Request Body**:
  ```json
  {
    "txnId": "3802219b-745c-4xxx-91ff-4471a25exxx"
  }
  ```
- **Response (Existing User - Stop Registration)**:
  ```json
  {
    "hprIdNumber": "71-3563-6824-xxxx",
    "name": "Ramesh Kumar",
    "gender": "M",
    "yearOfBirth": "1978",
    "address": "Rishikesh, Uttarakhand",
    "profilePhoto": "/9j/4AAQSk...",
    "new": false
  }
  ```
- **Response (New User - Continue Flow)**:
  ```json
  {
    "mobile": "89890xxxxx",
    "hprId": "",
    "new": true
  }
  ```

---

### Step 5: Verify Aadhaar Mobile Number (Demographic / OTP fallback)
Verifies if the onboarding mobile number matches the Aadhaar record.

#### Step 5.1: Demographic Auth
- **Method**: `POST`
- **Endpoint**: `https://apihspsbx.abdm.gov.in/v4/int/v2/registration/aadhaar/demographicAuthViaMobile`
- **Request Body**:
  ```json
  {
    "txnId": "TRANSACTION_ID",
    "mobileNumber": "ENCRYPTED_MOBILE_NUMBER_BASE64"
  }
  ```
- **Response**: `{"verified": true}` (Proceed to Step 6) or `{"verified": false}` (Proceed to Step 5.2)

#### Step 5.2: Fallback Mobile OTP Generation
- **Method**: `POST`
- **Endpoint**: `https://apihspsbx.abdm.gov.in/v4/int/v1/registration/aadhaar/generateMobileOTP`
- **Request Body**: `{"mobile": "9999999999", "txnId": "TRANSACTION_ID"}`
- **Response**: `{"txnId": "NEW_TRANSACTION_ID", "mobileNumber": null}`

#### Step 5.3: Fallback Mobile OTP Verification
- **Method**: `POST`
- **Endpoint**: `https://apihspsbx.abdm.gov.in/v4/int/v1/registration/aadhaar/verifyMobileOTP`
- **Request Body**: `{"otp": "ENCRYPTED_OTP_BASE64", "txnId": "TRANSACTION_ID"}`
- **Response**: `{"txnId": "NEW_TRANSACTION_ID", "mobileNumber": null}`

---

### Step 6: Get Suggested HPR Usernames
Retrieves list of recommended usernames based on Aadhaar demographic details.
- **Method**: `POST`
- **Endpoint**: `https://apihspsbx.abdm.gov.in/v4/int/v1/registration/aadhaar/hpid/suggestion`
- **Request Body**: `{"txnId": "TRANSACTION_ID"}`
- **Response (200 OK)**: `["username1", "username2", "username3"]`

---

### Step 7: Create HPR ID
Creates the base identity profile in HPR.
- **Method**: `POST`
- **Endpoint**: `https://apihspsbx.abdm.gov.in/v4/int/v2/registration/aadhaar/createHprIdWithPreVerified`
- **Request Body**:
  ```json
  {
    "txnId": "TRANSACTION_ID",
    "email": "ENCRYPTED_EMAIL_BASE64",
    "idType": "hpr_id",
    "domainName": "@hpr.abdm",
    "firstName": "FirstName",
    "middleName": "",
    "lastName": "LastName",
    "password": "ENCRYPTED_PASSWORD_BASE64",
    "profilePhoto": "BASE64_IMAGE_DATA",
    "hprId": "chosen_username",
    "sourceType": "AADHAAR",
    "hpCategoryCode": 1, 
    "hpSubCategoryCode": 1,
    "clientId": "",
    "stateCode": "27",
    "districtCode": "472",
    "council": false,
    "role": 3
  }
  ```
  *Note: `hpCategoryCode` mappings: `1` = Doctor, `2` = Nurse.*
- **Response (200 OK)**:
  ```json
  {
    "token": "HPR_TOKEN_JWT",
    "hprIdNumber": "7x-18xx-05xx-36xx",
    "name": "User Name",
    "gender": "M",
    "yearOfBirth": "1994",
    "firstName": "FirstName",
    "lastName": "LastName",
    "hprId": "userid@hpr.abdm",
    "new": true
  }
  ```
  *(Important: Store the returned `token` under `hprToken` inside the user session; it is required for professional registration!)*

---

### Step 8: Search Facility (HFR Database)
Allows search of the associated healthcare facility by pincode, name, state, or ID.
- **Method**: `POST`
- **Endpoint**: `https://apihspsbx.abdm.gov.in/v4/int/FacilityManagement/v1.5/facility/search`
- **Request Body**:
  ```json
  {
    "ownershipCode": "P", 
    "subDistrictLGDCode": "",
    "pincode": "",
    "facilityName": "",
    "facilityId": "IN0610090166",
    "page": 1,
    "resultsPerPage": 10,
    "stateLGDCode": "",
    "districtLGDCode": ""
  }
  ```
- **Response**: List of matching facilities containing `facilityId`, `facilityName`, etc.

---

### Step 9: Register Professional
Submits practitioner data, medical council details, degree qualifications, and facility mappings.
- **Method**: `POST`
- **Endpoint**: `https://apihspsbx.abdm.gov.in/v4/int/apis/v1/doctors/register-professional-new`
- **Request Body**:
  ```json
  {
    "hprToken": "HPR_TOKEN_JWT_FROM_STEP_7",
    "practitioner": {
      "healthProfessionalType": "doctor", 
      "apiClientId": "",
      "profilePhoto": "BASE64_IMAGE_DATA",
      "officialMobile": "97624xxxxx",
      "officialEmail": "email@example.com",
      "personalInformation": {
        "salutation": 1,
        "firstName": "First name",
        "lastName": "LastName",
        "nationality": "356",
        "gender": "M",
        "dateOfBirth": "1994-04-24",
        "languagesSpoken": "1,2",
        "category": "C"
      },
      "communicationAddress": {
        "isCommunicationAddressAsPerKYC": "false",
        "address": "Street Info",
        "pincode": "249201"
      },
      "registrationAcademic": {
        "category": 1,
        "registrationData": [
          {
            "registeredWithCouncil": 41,
            "registrationNumber": "REG12032",
            "registrationDate": "2024-12-01",
            "registrationCertificate": {
              "fileType": "pdf",
              "data": "BASE64_PDF_DATA"
            },
            "isPermanentOrRenewable": "Permanent",
            "qualifications": [
              {
                "nameOfDegreeOrDiplomaObtained": 4060, 
                "country": "356",
                "state": "29",
                "college": 1149,
                "university": 7010,
                "yearOfAwardingDegreeDiploma": "2024",
                "degreeCertificate": {
                  "fileType": "pdf",
                  "data": "BASE64_PDF_DATA"
                }
              }
            ]
          }
        ]
      },
      "currentWorkDetails": {
        "currentlyWorking": "1",
        "purposeOfWork": "Practice",
        "chooseWorkStatus": "1", 
        "facilityDeclarationData": {
          "facilityId": "IN2710000059",
          "facilityName": "Apollo Hospital",
          "facilityAddress": "Dehradun",
          "facilityPincode": "765435",
          "state": "27",
          "district": "500",
          "facilityType": "Hospital"
        }
      }
    }
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "body": {
      "referenceNumber": "8806aa4a-013b-xxxx-8839-1f0878dxxxxxx",
      "status": "true",
      "message": "Congratulations! Your profile has been submitted successfully for verification.",
      "hprId": "71-3563-6824-xxxx"
    },
    "statusCode": "OK",
    "statusCodeValue": 200
  }
  ```

---

### Step 10: Fetch Required Documents
Retrieves the list of document block identifiers required for uploads.
- **Method**: `POST`
- **Endpoint**: `https://apihspsbx.abdm.gov.in/v4/int/apis/v1/doctors/fetch-documents-list`
- **Request Body**: `{"hprid": "71-3563-6824-xxxx"}`
- **Response**:
  ```json
  {
    "documentList": {
      "profilePhoto": { "id": 40169 },
      "degreeCertificate": { "id": 13953 },
      "registrationCertificate": { "id": 27409 }
    }
  }
  ```

---

### Step 11: Upload Documents
Uploads the base64 files mapped to their respective document block identifiers.
- **Method**: `POST`
- **Endpoint**: `https://apihspsbx.abdm.gov.in/v4/int/apis/v1/uploads/upload-document`
- **Headers**:
  - `Authorization`: Bearer <Gateway Access Token>
  - `hpr_token`: <HPR Token JWT from Step 7>
- **Request Body**:
  ```json
  {
    "hpr_token": "HPR_TOKEN_JWT",
    "document": [
      {
        "document_id": 40169,
        "document_type": "profilePhoto",
        "fileType": "image/jpeg",
        "data": "BASE64_IMAGE_STRING"
      },
      {
        "document_id": 13953,
        "document_type": "degreeCertificate",
        "fileType": "certificate.pdf",
        "data": "BASE64_PDF_STRING"
      }
    ]
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "profilePhoto": { "status": "pass", "msg": "Profile pic has been uploaded." },
    "degreeCertificate": { "status": "pass", "msg": "Degree certificate updated" }
  }
  ```

---

## 4. Key Category Codes & Reference Constants

### Professional Type Codes
- **`hpCategoryCode`**: `1` = Doctor, `2` = Nurse
- **`healthProfessionalType`**: `"doctor"` or `"nurse"`

### Doctor Subcategory Mapping
- `1` = Modern Medicine (Allopathy)
- `2` = Dentist
- `3` = Ayurveda
- `4` = Unani
- `5` = Siddha
- `6` = Homoeopathy
- `89` = Sowa-Rigpa

### Nurse Subcategory Mapping
- `7` = Registered Auxiliary Nurse Midwife (RANM)
- `8` = Registered Nurse (RN)
- `9` = Registered Nurse and Registered Midwife (RN & RM)
- `10` = Registered Lady Health Visitor (RLHV)

### Degree Qualification Codes
- `4060` = MBBS (Bachelor of Medicine and Bachelor of Surgery)
- `4074` = BDS (Bachelor of Dental Surgery)
- `4079` = BAMS (Bachelor of Ayurvedic Medicine and Surgery)
- `4082` = BUMS (Bachelor of Unani Medicine & Surgery)
- `61` = BSMS (Bachelor of Siddha Medicine and Surgery)
- `74` = BTMS (Bachelor of Tibetan Medicine Studies)
- `40` = BHMS (Bachelor of Homoeopathic Medicine and Surgery)
