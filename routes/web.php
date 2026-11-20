<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\NupRegistrationController;
use App\Http\Controllers\UnitSelectionController;
use App\Http\Controllers\KprScreeningController;
use App\Http\Controllers\KprPaymentController;
use App\Http\Controllers\SprDocumentController;
use App\Http\Controllers\UtkPaymentController;
use App\Http\Controllers\ScreeningDecisionController;
use App\Http\Controllers\HousingUnitManageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\WhatsAppTestController;
use App\Http\Controllers\DokuNotificationController;
use App\Http\Controllers\Auth\DeveloperRegisterController;
use App\Http\Controllers\Admin\DeveloperManageController;
use App\Http\Controllers\Admin\HousingProjectManageController;

// Document Download Route (must be first to avoid filesystem serving conflicts)
Route::get('/download-document/{documentId}', [KprScreeningController::class, 'downloadDocument'])->name('documents.download')->middleware('auth');
Route::get('/admin/nup/{registration}/documents/{type}', [NupRegistrationController::class, 'downloadDocument'])
    ->name('admin.nup.documents.download')
    ->middleware('auth');

Route::get('/', function () {
    $projects = \App\Models\HousingProject::where('is_active', true)->withCount('housingUnits')->take(4)->get();
    return view('welcome', compact('projects'));
})->name('home');

Route::post('/doku/notifications', DokuNotificationController::class)->name('doku.notifications');
Route::post('/nup/payment/callback', DokuNotificationController::class)->name('nup.payment.callback');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

// Developer / Operator Registration Routes
Route::get('/register-developer', [DeveloperRegisterController::class, 'showRegistrationForm'])->name('developer.register');
Route::post('/register-developer', [DeveloperRegisterController::class, 'register'])->name('developer.register.post');
Route::get('/developer/status', [DeveloperRegisterController::class, 'checkStatus'])->name('developer.status');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// Google OAuth Routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Dashboard Route
Route::get('/dashboard', [NupRegistrationController::class, 'dashboard'])->name('dashboard')->middleware('auth');

// NUP Registration Routes
Route::prefix('nup')->middleware('auth')->group(function () {
    Route::get('/register', [NupRegistrationController::class, 'create'])->name('nup.register');
    Route::post('/register', [NupRegistrationController::class, 'store'])->name('nup.store');
    Route::get('/{id}', [NupRegistrationController::class, 'show'])->name('nup.show');
    Route::post('/{id}/payment', [NupRegistrationController::class, 'processPayment'])->name('nup.payment');
    Route::post('/{id}/mock-payment-success', [NupRegistrationController::class, 'mockPaymentSuccess'])->name('nup.mock-payment-success');
    Route::get('/{id}/status', [NupRegistrationController::class, 'checkPaymentStatus'])->name('nup.status');
});

// Unit Selection Routes
Route::prefix('units')->middleware('auth')->group(function () {
    Route::get('/', [UnitSelectionController::class, 'index'])->name('units.index');
    Route::get('/select/{nupRegistrationId}', [UnitSelectionController::class, 'create'])->name('units.create');
    Route::post('/select/{nupRegistrationId}', [UnitSelectionController::class, 'store'])->name('units.store');
    Route::get('/selection/{id}', [UnitSelectionController::class, 'show'])->name('units.show');
    Route::post('/{id}/confirm', [UnitSelectionController::class, 'confirm'])->name('units.confirm')->middleware('admin');
    Route::post('/{id}/cancel', [UnitSelectionController::class, 'cancel'])->name('units.cancel');
    Route::post('/{id}/mitra/reupload', [UnitSelectionController::class, 'reuploadMitra'])->name('units.mitra.reupload');
    Route::post('/{id}/mitra/switch', [UnitSelectionController::class, 'switchSchemeFromMitra'])->name('units.mitra.switch');
    Route::get('/queue', [UnitSelectionController::class, 'queue'])->name('units.queue');
});

// Broker Portal Routes
Route::prefix('broker')->middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Broker\BrokerPortalController::class, 'dashboard'])->name('broker.dashboard');
    Route::get('/units', [\App\Http\Controllers\Broker\BrokerPortalController::class, 'units'])->name('broker.units');
    Route::get('/leads', [\App\Http\Controllers\Broker\BrokerPortalController::class, 'leads'])->name('broker.leads');
    Route::get('/profile', [\App\Http\Controllers\Broker\BrokerPortalController::class, 'profile'])->name('broker.profile');
    Route::post('/profile', [\App\Http\Controllers\Broker\BrokerPortalController::class, 'updateProfile'])->name('broker.profile.update');
});

// KPR Screening Routes
Route::prefix('kpr')->middleware('auth')->group(function () {
    Route::get('/create/{unitSelectionId}', [KprScreeningController::class, 'create'])->name('kpr.create');
    Route::post('/store/{unitSelectionId}', [KprScreeningController::class, 'store'])->name('kpr.store');
    Route::get('/create-from-nup/{nupRegistrationId}', [KprScreeningController::class, 'createFromNup'])->name('kpr.create-from-nup');
    Route::post('/store-from-nup/{nupRegistrationId}', [KprScreeningController::class, 'storeFromNup'])->name('kpr.store-from-nup');
    Route::get('/{id}', [KprScreeningController::class, 'show'])->name('kpr.show');
    Route::post('/{id}/submit', [KprScreeningController::class, 'submit'])->name('kpr.submit');
    Route::post('/{id}/upload-screening-fee', [KprScreeningController::class, 'uploadScreeningFeeProof'])->name('kpr.upload-screening-fee');
    Route::post('/{id}/upload', [KprScreeningController::class, 'uploadDocument'])->name('kpr.upload');
    Route::post('/{id}/approve', [KprScreeningController::class, 'approve'])->name('kpr.approve')->middleware('admin');
    Route::post('/{id}/reject', [KprScreeningController::class, 'reject'])->name('kpr.reject')->middleware('admin');
    Route::post('/{id}/process', [KprScreeningController::class, 'process'])->name('kpr.process')->middleware('admin');
    Route::get('/{id}/outcome', [KprScreeningController::class, 'showOutcome'])->name('kpr.outcome');
    Route::post('/{id}/outcome', [KprScreeningController::class, 'handleOutcome'])->name('kpr.handle-outcome');
    Route::post('/{id}/make-decision', [KprScreeningController::class, 'makeDecision'])->name('kpr.make-decision');
});

// Admin KPR Routes (must be before participant routes to avoid conflicts)
Route::prefix('admin/kpr')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [KprScreeningController::class, 'adminIndex'])->name('admin.kpr.index');
    Route::get('/{id}', [KprScreeningController::class, 'adminShow'])->name('admin.kpr.show');
    Route::post('/{id}/verify-screening-fee', [KprScreeningController::class, 'verifyScreeningFee'])->name('admin.kpr.verify-screening-fee');
    Route::post('/{id}/pefindo', [KprScreeningController::class, 'savePefindoScore'])->name('admin.kpr.pefindo');
    Route::post('/documents/{documentId}/verify', [KprScreeningController::class, 'verifyDocument'])->name('admin.kpr.verify-document');
});

// KPR Payment Routes
Route::prefix('kpr-payment')->middleware('auth')->group(function () {
    Route::get('/', [KprPaymentController::class, 'list'])->name('kpr-payment.list');
    Route::get('/{sprId}', [KprPaymentController::class, 'index'])->name('kpr-payment.index');
    Route::get('/create/{sprId}', [KprPaymentController::class, 'create'])->name('kpr-payment.create');
    Route::post('/store/{sprId}', [KprPaymentController::class, 'store'])->name('kpr-payment.store');
    Route::get('/show/{paymentId}', [KprPaymentController::class, 'show'])->name('kpr-payment.show');
    Route::get('/mock-success/{paymentId}', [KprPaymentController::class, 'mockSuccess'])->name('kpr-payment.mock-success');
});

// Screening Decision Routes
Route::prefix('screening')->middleware('auth')->group(function () {
    Route::get('/', [ScreeningDecisionController::class, 'index'])->name('screening.index');
    Route::get('/decision/{screeningId}', [ScreeningDecisionController::class, 'showDecision'])->name('screening.decision');
    Route::post('/decision/{screeningId}', [ScreeningDecisionController::class, 'processDecision'])->name('screening.process-decision');
    Route::get('/select-unit/{screeningId}', [ScreeningDecisionController::class, 'showUnitSelection'])->name('screening.select-unit');
    Route::get('/select-unit/{screeningId}/{projectId}', [ScreeningDecisionController::class, 'showProjectUnits'])->name('screening.select-project');
    Route::get('/spr-clauses/{screeningId}/{unitId}', [ScreeningDecisionController::class, 'showSprClauses'])->name('screening.spr-clauses');
    Route::post('/spr-clauses/{screeningId}/{unitId}', [ScreeningDecisionController::class, 'processSprAgreement'])->name('screening.process-spr-agreement');
    Route::get('/commitment-payment/{screeningId}', [ScreeningDecisionController::class, 'showCommitmentPayment'])->name('screening.commitment-payment');
    Route::post('/commitment-payment/{screeningId}', [ScreeningDecisionController::class, 'processCommitmentPayment'])->name('screening.process-commitment-payment');
    Route::post('/payment-callback', [ScreeningDecisionController::class, 'paymentCallback'])->name('screening.payment-callback');
    Route::get('/mock-payment-success/{paymentId}', [ScreeningDecisionController::class, 'mockPaymentSuccess'])->name('screening.mock-payment-success');
    Route::get('/result/{screeningId}', [ScreeningDecisionController::class, 'showResult'])->name('screening.result');
});

// UTK Payment Routes
Route::prefix('utk')->middleware('auth')->group(function () {
    Route::get('/payment/{screeningId}', [UtkPaymentController::class, 'createFromScreening'])->name('utk.payment');
    Route::post('/payment/{screeningId}', [UtkPaymentController::class, 'processFromScreening'])->name('utk.payment.process');
    Route::get('/create/{unitSelectionId}', [UtkPaymentController::class, 'create'])->name('utk.create');
    Route::post('/process/{unitSelectionId}', [UtkPaymentController::class, 'process'])->name('utk.process');
    Route::get('/show/{utkPaymentId}', [UtkPaymentController::class, 'show'])->name('utk.show');
    Route::get('/mock-success/{utkPaymentId}', [UtkPaymentController::class, 'mockPaymentSuccess'])->name('utk.mock-success');
    Route::post('/callback', [UtkPaymentController::class, 'paymentCallback'])->name('utk.callback');
    Route::get('/admin', [UtkPaymentController::class, 'adminIndex'])->name('utk.admin')->middleware('admin');
    Route::post('/confirm-cash/{utkPaymentId}', [UtkPaymentController::class, 'confirmCash'])->name('utk.confirm-cash')->middleware('admin');
    Route::post('/reject-cash/{utkPaymentId}', [UtkPaymentController::class, 'rejectCash'])->name('utk.reject-cash')->middleware('admin');
    Route::post('/confirm-transfer/{utkPaymentId}', [UtkPaymentController::class, 'approveTransfer'])->name('utk.confirm-transfer')->middleware('admin');
    Route::post('/reject-transfer/{utkPaymentId}', [UtkPaymentController::class, 'rejectTransfer'])->name('utk.reject-transfer')->middleware('admin');
});

// SPR Document Routes
Route::prefix('spr')->middleware('auth')->group(function () {
    Route::get('/create/{unitSelectionId}', [SprDocumentController::class, 'create'])->name('spr.create');
    Route::post('/store/{unitSelectionId}', [SprDocumentController::class, 'store'])->name('spr.store');
    Route::get('/{id}', [SprDocumentController::class, 'show'])->name('spr.show');
    Route::get('/{id}/pdf', [SprDocumentController::class, 'generatePdf'])->name('spr.pdf');
    Route::post('/{id}/issue', [SprDocumentController::class, 'issue'])->name('spr.issue')->middleware('admin');
    Route::post('/{id}/cancel', [SprDocumentController::class, 'cancel'])->name('spr.cancel')->middleware('admin');
    Route::post('/{id}/bank-submission', [SprDocumentController::class, 'updateBankSubmission'])->name('spr.bank-submission')->middleware('admin');
    Route::post('/{id}/sp3k', [SprDocumentController::class, 'saveSp3k'])->name('spr.sp3k')->middleware('admin');
    Route::post('/{id}/akad', [SprDocumentController::class, 'saveAkad'])->name('spr.akad')->middleware('admin');
    Route::post('/{id}/complete-akad', [SprDocumentController::class, 'completeAkad'])->name('spr.complete-akad')->middleware('admin');
    Route::post('/{id}/confirm-mitra-disbursement', [SprDocumentController::class, 'confirmMitraDisbursement'])->name('spr.confirm-mitra-disbursement')->middleware('admin');
    Route::post('/{id}/bast', [SprDocumentController::class, 'saveBast'])->name('spr.bast')->middleware('admin');
    Route::post('/{id}/rescue-switch', [SprDocumentController::class, 'rescueSwitch'])->name('spr.rescue-switch');
});

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Admin Realtime Notification Feed
    Route::get('/notifications/feed', function () {
        return response()->json([
            'count'         => \App\Services\AdminNotificationService::getPendingCount(),
            'notifications' => \App\Services\AdminNotificationService::getPendingNotifications(10),
            'recent'        => \App\Services\AdminNotificationService::getRecentCompletedNotifications(5),
        ]);
    })->name('admin.notifications.feed');

    // NUP Management
    Route::get('/nup', [NupRegistrationController::class, 'adminIndex'])->name('admin.nup.index');
    Route::get('/nup/{id}', [NupRegistrationController::class, 'adminShow'])->name('admin.nup.show');
    Route::post('/nup/payment-transactions/{transactionId}/confirm-cash', [NupRegistrationController::class, 'confirmCashOfficePayment'])->name('admin.nup.confirm-cash');
    Route::post('/nup/payment-transactions/{transactionId}/reject-cash', [NupRegistrationController::class, 'rejectCashOfficePayment'])->name('admin.nup.reject-cash');

    // Promo codes for NUP DOKU payments (Super Admin only)
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/promo-codes', [\App\Http\Controllers\Admin\PromoCodeController::class, 'index'])->name('admin.promo-codes.index');
        Route::post('/promo-codes', [\App\Http\Controllers\Admin\PromoCodeController::class, 'store'])->name('admin.promo-codes.store');
        Route::put('/promo-codes/{promoCode}', [\App\Http\Controllers\Admin\PromoCodeController::class, 'update'])->name('admin.promo-codes.update');
        Route::delete('/promo-codes/{promoCode}', [\App\Http\Controllers\Admin\PromoCodeController::class, 'destroy'])->name('admin.promo-codes.destroy');
    });

    // Mitra Developer Companies (Super Admin)
    Route::get('/developers', [DeveloperManageController::class, 'index'])->name('admin.developers.index');
    Route::get('/developers/create', [DeveloperManageController::class, 'create'])->name('admin.developers.create');
    Route::post('/developers', [DeveloperManageController::class, 'store'])->name('admin.developers.store');
    Route::get('/developers/{id}', [DeveloperManageController::class, 'show'])->name('admin.developers.show');
    Route::post('/developers/{id}/approve', [DeveloperManageController::class, 'approve'])->name('admin.developers.approve');
    Route::post('/developers/{id}/reject', [DeveloperManageController::class, 'reject'])->name('admin.developers.reject');
    Route::post('/developers/{id}/reset-password', [DeveloperManageController::class, 'resetPassword'])->name('admin.developers.reset-password');

    // Rekening Bank Developer (Multi-Company)
    Route::resource('/bank-accounts', \App\Http\Controllers\Admin\DeveloperBankAccountController::class, ['names' => 'admin.bank-accounts'])->except(['create', 'edit', 'show']);

    // Brokers & Marketing Management (Master TTD Broker)
    Route::resource('/brokers', \App\Http\Controllers\Admin\BrokerManageController::class, ['names' => 'admin.brokers'])->only(['index', 'store', 'update', 'destroy']);

    // Housing Projects (Perumahan) Management (Operator / Super Admin)
    Route::resource('/housing-projects', HousingProjectManageController::class, ['names' => 'admin.housing-projects'])->except(['show']);

    // Housing Units (Stok Perumahan) Management
    Route::get('/housing-units', [HousingUnitManageController::class, 'index'])->name('admin.housing-units.index');
    Route::get('/housing-units/create', [HousingUnitManageController::class, 'create'])->name('admin.housing-units.create');
    Route::post('/housing-units', [HousingUnitManageController::class, 'store'])->name('admin.housing-units.store');
    Route::get('/housing-units/{id}/edit', [HousingUnitManageController::class, 'edit'])->name('admin.housing-units.edit');
    Route::put('/housing-units/{id}', [HousingUnitManageController::class, 'update'])->name('admin.housing-units.update');
    Route::delete('/housing-units/{id}', [HousingUnitManageController::class, 'destroy'])->name('admin.housing-units.destroy');

    // Unit Selections (Pemilihan Unit)
    Route::get('/units', [UnitSelectionController::class, 'adminIndex'])->name('admin.units.index');
    Route::post('/units/{id}/restore', [UnitSelectionController::class, 'restore'])->name('admin.units.restore');
    Route::post('/units/{id}/cancel', [UnitSelectionController::class, 'cancel'])->name('admin.units.cancel');
    Route::post('/units/{id}/mitra/approve', [UnitSelectionController::class, 'approveMitra'])->name('admin.units.mitra.approve');
    Route::post('/units/{id}/mitra/reject', [UnitSelectionController::class, 'rejectMitra'])->name('admin.units.mitra.reject');

    // KPR Screening
    Route::get('/kpr', [KprScreeningController::class, 'adminIndex'])->name('admin.kpr.index');

    // SPR Management
    Route::get('/spr', [SprDocumentController::class, 'adminIndex'])->name('admin.spr.index');
    Route::get('/spr/pending', [SprDocumentController::class, 'pending'])->name('admin.spr.pending');
    Route::get('/spr/create/{unitSelectionId}', [SprDocumentController::class, 'adminCreate'])->name('admin.spr.create');
    Route::post('/spr/store/{unitSelectionId}', [SprDocumentController::class, 'adminStore'])->name('admin.spr.store');
    Route::resource('/spr-clauses', \App\Http\Controllers\Admin\SprClauseController::class, ['names' => 'admin.spr-clauses'])->except(['show']);

    // UTK Payments Admin
    Route::get('/utk-payments', [UtkPaymentController::class, 'adminIndex'])->name('admin.utk-payments.index');

    // KPR Payments Admin
    Route::get('/kpr-payments', [KprPaymentController::class, 'adminIndex'])->name('admin.kpr-payments.index');
    Route::get('/kpr-payments/{id}', [KprPaymentController::class, 'adminShow'])->name('admin.kpr-payments.show');
    Route::post('/kpr-payments/{id}/approve', [KprPaymentController::class, 'adminApprove'])->name('admin.kpr-payments.approve');
    Route::post('/kpr-payments/{id}/reject', [KprPaymentController::class, 'adminReject'])->name('admin.kpr-payments.reject');

    // WhatsApp Test
    Route::get('/wa-test', [WhatsAppTestController::class, 'index'])->name('admin.wa.test');
    Route::post('/wa-test/send', [WhatsAppTestController::class, 'send'])->name('admin.wa.send');

    // Role-specific dashboards
    Route::get('/marketing', [AdminDashboardController::class, 'marketing'])->name('admin.marketing');
    Route::get('/finance', [AdminDashboardController::class, 'finance'])->name('admin.finance');
    Route::get('/screening', [AdminDashboardController::class, 'screening'])->name('admin.screening');
});

// Storage fallback route for cPanel / Shared Hosting (when symlink is missing or broken)
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (file_exists($filePath)) {
        return response()->file($filePath);
    }
    $altPath = public_path('storage/' . $path);
    if (file_exists($altPath)) {
        return response()->file($altPath);
    }
    abort(404);
})->where('path', '.*');

Route::get('/public/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (file_exists($filePath)) {
        return response()->file($filePath);
    }
    $altPath = public_path('storage/' . $path);
    if (file_exists($altPath)) {
        return response()->file($altPath);
    }
    abort(404);
})->where('path', '.*');
