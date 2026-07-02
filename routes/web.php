<?php

use App\Http\Controllers\AbhaController;
use App\Http\Controllers\NhprController;
use App\Http\Controllers\NhprRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/nhpr/token', [NhprController::class, 'show'])->name('nhpr.token.show');
Route::post('/nhpr/token', [NhprController::class, 'generate'])->name('nhpr.token.generate');
Route::post('/nhpr/token/credentials', [NhprController::class, 'saveCredentials'])->name('nhpr.token.credentials.save');
Route::post('/nhpr/token/credentials/clear', [NhprController::class, 'clearCredentials'])->name('nhpr.token.credentials.clear');

Route::prefix('nhpr/register')->name('nhpr.register.')->group(function () {
    Route::get('/', [NhprRegistrationController::class, 'showWizard'])->name('wizard');
    Route::post('/toggle-mode', [NhprRegistrationController::class, 'toggleMode'])->name('toggle-mode');
    Route::post('/aadhaar/send-otp', [NhprRegistrationController::class, 'sendAadhaarOtp'])->name('aadhaar.send-otp');
    Route::post('/aadhaar/verify-otp', [NhprRegistrationController::class, 'verifyAadhaarOtp'])->name('aadhaar.verify-otp');
    Route::post('/mobile/verify', [NhprRegistrationController::class, 'verifyMobile'])->name('mobile.verify');
    Route::post('/mobile/verify-otp', [NhprRegistrationController::class, 'verifyMobileOtp'])->name('mobile.verify-otp');
    Route::post('/suggestions', [NhprRegistrationController::class, 'getUsernameSuggestions'])->name('suggestions');
    Route::post('/create-id', [NhprRegistrationController::class, 'createHprId'])->name('create-id');
    Route::post('/facility/search', [NhprRegistrationController::class, 'searchFacility'])->name('facility.search');
    Route::post('/professional/submit', [NhprRegistrationController::class, 'submitProfessionalRegistration'])->name('professional.submit');
    Route::post('/documents/fetch', [NhprRegistrationController::class, 'fetchDocuments'])->name('documents.fetch');
    Route::post('/documents/upload', [NhprRegistrationController::class, 'uploadDocuments'])->name('documents.upload');
});

Route::get('/nhpr/track', [NhprRegistrationController::class, 'showTracker'])->name('nhpr.track.show');
Route::post('/nhpr/track', [NhprRegistrationController::class, 'trackStatus'])->name('nhpr.track.post');

Route::prefix('abha')->name('abha.')->group(function () {
    Route::get('/', [AbhaController::class, 'showDashboard'])->name('dashboard');
    Route::get('/create', [AbhaController::class, 'showCreator'])->name('create');
    Route::post('/create/request-otp', [AbhaController::class, 'enrollRequestOtp'])->name('create.request-otp');
    Route::post('/create/verify-otp', [AbhaController::class, 'enrollVerifyOtp'])->name('create.verify-otp');
    Route::post('/create/request-mobile-otp', [AbhaController::class, 'enrollRequestMobileOtp'])->name('create.request-mobile-otp');
    Route::post('/create/verify-mobile-otp', [AbhaController::class, 'enrollVerifyMobileOtp'])->name('create.verify-mobile-otp');
    Route::post('/card/download', [AbhaController::class, 'downloadCard'])->name('card.download');

    Route::get('/find', [AbhaController::class, 'showFinder'])->name('find');
    Route::post('/find/search-mobile', [AbhaController::class, 'findSearchByMobile'])->name('find.search-mobile');
    Route::post('/find/select', [AbhaController::class, 'findSelectProfile'])->name('find.select');

    Route::get('/verify', [AbhaController::class, 'showVerifier'])->name('verify');
    Route::post('/verify/search', [AbhaController::class, 'verifySearch'])->name('verify.search');
    Route::post('/verify/request-otp', [AbhaController::class, 'verifyRequestOtp'])->name('verify.request-otp');
    Route::post('/verify/confirm', [AbhaController::class, 'verifyConfirm'])->name('verify.confirm');
    Route::post('/verify/qr', [AbhaController::class, 'verifyQrCodePost'])->name('verify.qr');
    Route::post('/verify/demographics', [AbhaController::class, 'verifyDemographicsPost'])->name('verify.demographics');
});
