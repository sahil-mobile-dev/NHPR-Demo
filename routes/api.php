<?php

use App\Http\Controllers\Api\V1\AbhaApiController;
use App\Http\Controllers\Api\V1\HfrApiController;
use App\Http\Controllers\Api\V1\HipApiController;
use App\Http\Controllers\Api\V1\HiuApiController;
use App\Http\Controllers\Api\V1\HprRegistrationApiController;
use App\Http\Controllers\Api\V1\NhprApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Mobile App (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ABDM Gateway Token & Credentials
    Route::prefix('token')->group(function () {
        Route::get('/', [NhprApiController::class, 'token']);
        Route::post('/credentials', [NhprApiController::class, 'saveCredentials']);
        Route::post('/credentials/clear', [NhprApiController::class, 'clearCredentials']);
    });

    // NHPR / HPR Registration & Onboarding
    Route::prefix('nhpr')->group(function () {
        Route::post('/aadhaar/send-otp', [HprRegistrationApiController::class, 'sendAadhaarOtp']);
        Route::post('/aadhaar/verify-otp', [HprRegistrationApiController::class, 'verifyAadhaarOtp']);
        Route::post('/mobile/send-otp', [HprRegistrationApiController::class, 'sendMobileOtp']);
        Route::post('/mobile/verify-otp', [HprRegistrationApiController::class, 'verifyMobileOtp']);
        Route::post('/suggestions', [HprRegistrationApiController::class, 'getUsernameSuggestions']);
        Route::post('/create-id', [HprRegistrationApiController::class, 'createHprId']);
        Route::post('/professional/submit', [HprRegistrationApiController::class, 'submitProfessionalRegistration']);
        Route::post('/track', [HprRegistrationApiController::class, 'trackStatus']);
    });

    // ABHA Enrollment & Verification
    Route::prefix('abha')->group(function () {
        Route::post('/enroll/request-otp', [AbhaApiController::class, 'enrollRequestOtp']);
        Route::post('/enroll/verify-otp', [AbhaApiController::class, 'enrollVerifyOtp']);
        Route::post('/find/search-mobile', [AbhaApiController::class, 'findByMobile']);
        Route::post('/card/download', [AbhaApiController::class, 'downloadCard']);
    });

    // HFR - Health Facility Registry
    Route::prefix('hfr')->group(function () {
        Route::post('/search', [HfrApiController::class, 'search']);
        Route::get('/masters/states', [HfrApiController::class, 'getStates']);
        Route::get('/masters/districts', [HfrApiController::class, 'getDistricts']);
        Route::get('/masters/facility-types', [HfrApiController::class, 'getFacilityTypes']);
        Route::get('/masters/specialities', [HfrApiController::class, 'getSpecialities']);
    });

    // HIP - Health Information Provider
    Route::prefix('hip')->group(function () {
        Route::post('/care-context/link', [HipApiController::class, 'linkCareContext']);
    });

    // HIU - Health Information User
    Route::prefix('hiu')->group(function () {
        Route::post('/consent/request', [HiuApiController::class, 'requestConsent']);
        Route::post('/health-data/request', [HiuApiController::class, 'requestHealthData']);
    });
});
