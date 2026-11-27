<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\FbrIntegrationController;
use App\Http\Controllers\Tenant\HsCodeController;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\SandboxScenarioController;
use App\Http\Controllers\Tenant\SettingsController;
use Illuminate\Support\Facades\Route;

// 1. Screen 1: Public Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dynamic Brand Logo endpoint (serves custom uploaded logo from DB or falls back to default PNG)
Route::get('/brand/logo/{variant}', function (string $variant) {
    return app(\App\Services\BrandLogoService::class)->streamResponse($variant);
})->whereIn('variant', ['light', 'dark'])->name('brand.logo');

// Static images fallback for environments like Vercel
Route::get('/images/{filename}', function (string $filename) {
    $path = public_path('images/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mimeTypes = [
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'webp' => 'image/webp',
    ];
    $contentType = $mimeTypes[$extension] ?? 'image/png';
    return response()->file($path, [
        'Content-Type' => $contentType,
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('filename', '.*');

// 2. Screen 2: Sign-in & Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/two-factor-challenge', [AuthController::class, 'showTwoFactorChallenge'])->name('two-factor.challenge');
Route::post('/two-factor-challenge', [AuthController::class, 'verifyTwoFactorChallenge'])->name('two-factor.verify');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. Screen 3: Business Account Registration
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Password Reset Placeholder
Route::get('/forgot-password', function () {
    return back()->with('status', 'Password reset instructions have been sent to your registered email.');
})->name('password.request');

// Business Switcher
Route::post('/switch-business/{uuid}', [AuthController::class, 'switchBusiness'])->name('switch.business');

// Holding Status Views
Route::get('/pending-approval', [AuthController::class, 'pending'])->name('tenant.pending');
Route::post('/pending-approval/approve', [AuthController::class, 'demoApprove'])->name('tenant.pending.approve');
Route::get('/rejected', [AuthController::class, 'rejected'])->name('tenant.rejected');
Route::get('/suspended', [AuthController::class, 'suspended'])->name('tenant.suspended');

// 4. Screen 4 & Tenant Business Portal (Strict Tenant Context Guard)
Route::middleware(['auth', 'tenant'])->group(function () {
    // Screen 4: Tenant Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');

    // Invoices Module
    Route::prefix('invoices')->name('tenant.invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/bulk-upload', [InvoiceController::class, 'bulkUpload'])->name('bulk-upload');
        Route::post('/bulk-upload', [InvoiceController::class, 'processBulkUpload'])->name('process-bulk-upload');
        Route::get('/sample-template', [InvoiceController::class, 'downloadSampleTemplate'])->name('sample-template');
        Route::post('/validate-draft', [InvoiceController::class, 'validateDraft'])->name('validate-draft');
        Route::get('/verify-buyer', [InvoiceController::class, 'verifyBuyer'])->name('verify-buyer');
        Route::get('/{uuid}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{uuid}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{uuid}', [InvoiceController::class, 'update'])->name('update');
        Route::delete('/{uuid}', [InvoiceController::class, 'destroy'])->name('destroy');
        Route::post('/{uuid}/submit', [InvoiceController::class, 'submitToFbr'])->name('submit');
        Route::post('/{uuid}/reconcile', [InvoiceController::class, 'reconcile'])->name('reconcile');
        Route::post('/{uuid}/cancel', [InvoiceController::class, 'cancel'])->name('cancel');
        Route::get('/{uuid}/print', [InvoiceController::class, 'printA4'])->name('print');
    });

    // Customers Module
    Route::prefix('customers')->name('tenant.customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::put('/{id}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('destroy');
    });

    // Products Module
    Route::prefix('products')->name('tenant.products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::put('/{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // HS / PCT Codes Module
    Route::prefix('hs-codes')->name('tenant.hscodes.')->group(function () {
        Route::get('/', [HsCodeController::class, 'index'])->name('index');
    });

    // Reports Module
    Route::get('/reports', [ReportController::class, 'index'])->name('tenant.reports');

    // Sandbox Scenarios Runner (SN001 - SN028)
    Route::prefix('sandbox-scenarios')->name('tenant.sandbox-scenarios')->group(function () {
        Route::get('/', [SandboxScenarioController::class, 'index']);
        Route::post('/validate', [SandboxScenarioController::class, 'validateScenario'])->name('.validate');
        Route::post('/{code}/run', [SandboxScenarioController::class, 'run'])->name('.run');
    });

    // FBR Integration Settings
    Route::prefix('fbr-integration')->name('tenant.fbr-integration')->group(function () {
        Route::get('/', [FbrIntegrationController::class, 'index']);
        Route::post('/', [FbrIntegrationController::class, 'update'])->name('.update');
        Route::post('/sandbox', [FbrIntegrationController::class, 'saveSandbox'])->name('.sandbox.save');
        Route::post('/sandbox/verify', [FbrIntegrationController::class, 'verifySandbox'])->name('.sandbox.verify');
        Route::post('/production', [FbrIntegrationController::class, 'saveProduction'])->name('.production.save');
        Route::post('/production/verify', [FbrIntegrationController::class, 'verifyProduction'])->name('.production.verify');
        Route::post('/production/toggle', [FbrIntegrationController::class, 'toggleProduction'])->name('.production.toggle');
    });

    // Business Settings
    Route::prefix('settings')->name('tenant.settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::post('/', [SettingsController::class, 'update'])->name('.update');
        Route::post('/account', [SettingsController::class, 'updateAccount'])->name('.account');
        Route::post('/notifications', [SettingsController::class, 'updateNotifications'])->name('.notifications');
        Route::post('/two-factor/setup', [SettingsController::class, 'startTwoFactorSetup'])->name('.two-factor.setup');
        Route::post('/two-factor/cancel', [SettingsController::class, 'cancelTwoFactorSetup'])->name('.two-factor.cancel');
        Route::post('/two-factor/confirm', [SettingsController::class, 'confirmTwoFactor'])->name('.two-factor.confirm');
        Route::post('/two-factor/disable', [SettingsController::class, 'disableTwoFactor'])->name('.two-factor.disable');
        Route::post('/two-factor/recovery-codes', [SettingsController::class, 'regenerateRecoveryCodes'])->name('.two-factor.recovery-codes');
    });
});
