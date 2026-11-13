<?php

namespace App\Providers;

use App\Services\Contracts\FbrIntegrationServiceInterface;
use App\Services\Contracts\FbrValidationServiceInterface;
use App\Services\Contracts\InvoiceServiceInterface;
use App\Services\Contracts\TenantServiceInterface;
use App\Services\FbrIntegrationService;
use App\Services\FbrValidationService;
use App\Services\InvoiceService;
use App\Services\TenantService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantServiceInterface::class, TenantService::class);
        $this->app->singleton(FbrValidationServiceInterface::class, FbrValidationService::class);
        $this->app->singleton(FbrIntegrationServiceInterface::class, FbrIntegrationService::class);
        $this->app->singleton(InvoiceServiceInterface::class, InvoiceService::class);
    }

    public function boot(): void
    {
        if (
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
