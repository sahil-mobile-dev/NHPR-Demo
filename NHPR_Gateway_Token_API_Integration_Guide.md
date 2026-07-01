# NHPR Gateway Token API Integration Guide (Laravel)

## Objective

Implement the **NHPR Gateway Token** API in a Laravel application. This
API generates an Access Token using the Client ID and Client Secret. The
generated token will be used to authenticate all subsequent NHPR/ABDM
API requests.

------------------------------------------------------------------------

# API Details

-   **API Name:** Generate Gateway Token
-   **Method:** `POST`
-   **Environment:** Sandbox (`sbx`) / Production (`abdm`)

### Sandbox Endpoint

``` text
https://dev.abdm.gov.in/api/hiecm/gateway/v3/sessions
```

------------------------------------------------------------------------

# Required Headers

  Header         Value
  -------------- ---------------------------------------
  REQUEST-ID     UUID generated for every request
  TIMESTAMP      Current timestamp in ISO-8601 format
  X-CM-ID        `sbx` (Sandbox) / `abdm` (Production)
  Content-Type   `application/json`

------------------------------------------------------------------------

# Request Body

``` json
{
  "clientId": "YOUR_CLIENT_ID",
  "clientSecret": "YOUR_CLIENT_SECRET",
  "grantType": "client_credentials"
}
```

------------------------------------------------------------------------

# Success Response

``` json
{
  "accessToken": "...",
  "expiresIn": 36000,
  "refreshExpiresIn": 1800,
  "refreshToken": "...",
  "tokenType": "bearer"
}
```

------------------------------------------------------------------------

# Laravel Project Structure

``` text
app/
 ├── Http/
 │    └── Controllers/
 │         └── NhprController.php
 │
 ├── Services/
 │    └── NhprService.php
 │
config/
 └── services.php

resources/
 └── views/
      └── nhpr/
           └── token.blade.php

routes/
 └── web.php

.env
```

------------------------------------------------------------------------

# Implementation Tasks

## 1. Configuration

Add the following variables to `.env`:

``` env
NHPR_BASE_URL=https://dev.abdm.gov.in
NHPR_CLIENT_ID=YOUR_CLIENT_ID
NHPR_CLIENT_SECRET=YOUR_CLIENT_SECRET
NHPR_X_CM_ID=sbx
```

Map them inside `config/services.php`.

------------------------------------------------------------------------

## 2. Create Blade View

Create:

``` text
resources/views/nhpr/token.blade.php
```

Include: - Generate Token button - API response section - Success/Error
messages

> Prefer reading Client ID and Client Secret from `.env` instead of user
> input.

------------------------------------------------------------------------

## 3. Add Routes

File:

``` text
routes/web.php
```

Routes:

-   GET `/nhpr/token` → Display page
-   POST `/nhpr/token` → Generate token

------------------------------------------------------------------------

## 4. Create Controller

Create:

``` text
app/Http/Controllers/NhprController.php
```

Responsibilities: - Display Blade view - Call `NhprService` - Return API
response - Handle errors

------------------------------------------------------------------------

## 5. Create Service

Create:

``` text
app/Services/NhprService.php
```

Responsibilities: - Generate UUID (REQUEST-ID) - Generate ISO8601
timestamp - Read credentials from config - Build request headers - Build
request payload - Call NHPR endpoint using Laravel HTTP Client - Parse
response - Return structured data

------------------------------------------------------------------------

## 6. HTTP Request

Use Laravel HTTP Client.

Headers:

-   REQUEST-ID
-   TIMESTAMP
-   X-CM-ID
-   Content-Type

Payload:

``` json
{
  "clientId": "...",
  "clientSecret": "...",
  "grantType": "client_credentials"
}
```

POST to:

``` text
https://dev.abdm.gov.in/api/hiecm/gateway/v3/sessions
```

------------------------------------------------------------------------

## 7. Handle Response

Extract:

-   accessToken
-   refreshToken
-   expiresIn
-   refreshExpiresIn
-   tokenType

Display meaningful error messages for HTTP 400, 401, 500 and network
failures.

------------------------------------------------------------------------

## 8. Store Token

Store the generated token using Session or Cache. Reuse it until
expiration for all subsequent NHPR API calls.

------------------------------------------------------------------------

# Flow

``` text
User
   ↓
Blade View
   ↓
Route
   ↓
Controller
   ↓
NhprService
   ↓
Generate UUID + Timestamp
   ↓
POST NHPR API
   ↓
Receive Token
   ↓
Store Token
   ↓
Return Response
   ↓
Blade View
```

------------------------------------------------------------------------

# Deliverables

-   [ ] Blade UI (`token.blade.php`)
-   [ ] Routes
-   [ ] Controller
-   [ ] Service
-   [ ] `.env` configuration
-   [ ] `config/services.php`
-   [ ] HTTP integration
-   [ ] Response handling
-   [ ] Token storage
-   [ ] Error handling
-   [ ] Clean, reusable code following Laravel best practices

## Notes

-   Generate a new `REQUEST-ID` for every request.
-   `TIMESTAMP` must be ISO-8601.
-   Use `sbx` in the sandbox environment.
-   Read secrets from `.env`; never hard-code credentials.
-   Keep business logic inside the Service layer.
