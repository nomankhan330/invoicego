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

// 2. Screen 2: Sign-in & Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
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
        Route::get('/{uuid}', [InvoiceController::class, 'show'])->name('show');
        Route::post('/{uuid}/submit', [InvoiceController::class, 'submitToFbr'])->name('submit');
        Route::post('/{uuid}/reconcile', [InvoiceController::class, 'reconcile'])->name('reconcile');
        Route::get('/{uuid}/print', [InvoiceController::class, 'printA4'])->name('print');
    });

    // Customers Module
    Route::prefix('customers')->name('tenant.customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
    });

    // Products Module
    Route::prefix('products')->name('tenant.products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
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
        Route::post('/{code}/run', [SandboxScenarioController::class, 'run'])->name('.run');
    });

    // FBR Integration Settings
    Route::prefix('fbr-integration')->name('tenant.fbr-integration')->group(function () {
        Route::get('/', [FbrIntegrationController::class, 'index']);
        Route::post('/', [FbrIntegrationController::class, 'update'])->name('.update');
    });

    // Business Settings
    Route::prefix('settings')->name('tenant.settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::post('/', [SettingsController::class, 'update'])->name('.update');
    });
});
