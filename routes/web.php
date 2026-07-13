<?php

use App\Http\Controllers\AbhaController;
use App\Http\Controllers\HfrController;
use App\Http\Controllers\HipController;
use App\Http\Controllers\HiuController;
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
    Route::post('/aadhaar/generate-link', [NhprRegistrationController::class, 'generateAadhaarLink'])->name('aadhaar.generate-link');
    Route::post('/aadhaar/check-status', [NhprRegistrationController::class, 'checkAadhaarAuthStatus'])->name('aadhaar.check-status');
    Route::post('/masters/ministries', [NhprRegistrationController::class, 'getMinistries'])->name('masters.ministries');
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

Route::prefix('nhpr/hfr')->name('nhpr.hfr.')->group(function () {
    Route::get('/', [HfrController::class, 'index'])->name('index');
    Route::post('/search', [HfrController::class, 'search'])->name('search');
    Route::post('/create', [HfrController::class, 'store'])->name('create');
    Route::post('/link', [HfrController::class, 'linkBridge'])->name('link');
});

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

Route::prefix('hip')->name('hip.')->group(function () {
    Route::get('/', [HipController::class, 'showDashboard'])->name('dashboard');
    Route::get('/milestone2', [HipController::class, 'milestone2Features'])->name('milestone2');
    Route::post('/record/create', [HipController::class, 'createRecordStore'])->name('record.create');
    Route::post('/link', [HipController::class, 'linkContextPost'])->name('link');

    // HIMS Consent & Security Audit Portal
    Route::get('/consents', [HipController::class, 'showConsentsAndAuditLogs'])->name('consents');
    Route::post('/consents/register', [HipController::class, 'simulateConsent'])->name('consents.register');
    Route::post('/consents/exchange', [HipController::class, 'simulateHealthRequest'])->name('consents.exchange');
    Route::post('/simulator/trigger-discovery', [HipController::class, 'simulateDiscovery'])->name('simulator.discovery');
});

// Health Information User (HIU) Portal
Route::prefix('hiu')->name('hiu.')->group(function () {
    Route::get('/', [HiuController::class, 'showDashboard'])->name('dashboard');
    Route::post('/consent/request', [HiuController::class, 'requestConsent'])->name('consent.request');
    Route::post('/consent/fetch/{id}', [HiuController::class, 'fetchArtefact'])->name('consent.fetch');
    Route::post('/health-information/request', [HiuController::class, 'requestHealthData'])->name('health-information.request');
    Route::get('/records/{abha_address}', [HiuController::class, 'showRecords'])->name('records');
    Route::post('/consent/revoke/{consentId}', [HiuController::class, 'revokeConsentLocal'])->name('consent.revoke');

    // Simulations
    Route::post('/simulator/approve-consent', [HiuController::class, 'simulateApproveConsent'])->name('simulator.approve-consent');
    Route::post('/simulator/deny-consent', [HiuController::class, 'simulateDenyConsent'])->name('simulator.deny-consent');
    Route::post('/simulator/revoke-consent', [HiuController::class, 'simulateRevokeConsent'])->name('simulator.revoke-consent');
    Route::post('/simulator/push-health-data', [HiuController::class, 'simulatePushHealthData'])->name('simulator.push-health-data');
});

// Official ABDM Gateway Callback Routes
$callbackRoutes = function () {
    // HIP Discovery Callbacks
    Route::post('/hip/discover', [HipController::class, 'apiDiscover']);
    Route::post('/hip/patient/care-context/discover', [HipController::class, 'apiDiscover']);

    // HIP Linking Callbacks
    Route::post('/hip/link/init', [HipController::class, 'apiLinkInit']);
    Route::post('/hip/link/care-context/init', [HipController::class, 'apiLinkInit']);
    Route::post('/hip/link/confirm', [HipController::class, 'apiLinkConfirm']);
    Route::post('/hip/link/care-context/confirm', [HipController::class, 'apiLinkConfirm']);

    // HIP Consent Callbacks
    Route::post('/consents/hip/notify', [HipController::class, 'apiConsentNotify']);
    Route::post('/consent/request/hip/notify', [HipController::class, 'apiConsentNotify']);

    // HIP Data Flow Request Callbacks
    Route::post('/health-information/hip/request', [HipController::class, 'apiHealthRequest']);
    Route::post('/hip/health-information/request', [HipController::class, 'apiHealthRequest']);

    // New HIP Certification Webhooks
    Route::post('/hip/token/on-generate-token', [HipController::class, 'apiLinkTokenOnGenerate']);
    Route::post('/link/on-carecontext', [HipController::class, 'apiLinkOnCareContext']);
    Route::post('/link/on_carecontext', [HipController::class, 'apiLinkOnCareContext']);
    Route::post('/links/context/on-notify', [HipController::class, 'apiLinkContextOnNotify']);
    Route::post('/patients/sms/on-notify', [HipController::class, 'apiPatientsSmsOnNotify']);
    Route::post('/hip/patient/share', [HipController::class, 'apiPatientShare']);

    // HIU Callbacks
    Route::post('/consent/on-init', [HiuController::class, 'apiConsentOnInit']);
    Route::post('/consent/notify', [HiuController::class, 'apiConsentNotify']);
    Route::post('/health-information/on-request', [HiuController::class, 'apiReceiveHealthData']);
    Route::post('/hiu/health-information/on-request', [HiuController::class, 'apiReceiveHealthData']);

    // New HIU Certification Webhooks
    Route::post('/hiu/patient/care-context/on-discover', [HiuController::class, 'apiPatientCareContextOnDiscover']);
    Route::post('/hiu/patient/care-context/on-init', [HiuController::class, 'apiPatientCareContextOnInit']);
    Route::post('/hiu/patient/care-context/on-confirm', [HiuController::class, 'apiPatientCareContextOnConfirm']);
    Route::post('/hiu/patient/on-share', [HiuController::class, 'apiPatientOnShare']);
};

Route::prefix('v3')->group($callbackRoutes);
Route::prefix('api/v3')->group($callbackRoutes);
