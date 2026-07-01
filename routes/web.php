<?php

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
