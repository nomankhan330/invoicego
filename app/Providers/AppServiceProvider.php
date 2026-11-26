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
use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrap();

        if (
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Record login activity & last_login_at for Filament /admin/login (and any non-AuthController logins)
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function (\Illuminate\Auth\Events\Login $event) {
            if (request()->routeIs('login.post')) {
                return; // AuthController::login() already logs this login
            }

            $user = $event->user;
            if (!$user instanceof \App\Models\User) {
                return;
            }

            try {
                $user->forceFill(['last_login_at' => now()])->saveQuietly();

                $isAdmin = $user->is_super_admin || in_array($user->admin_role, ['super_admin', 'admin', 'support_staff'], true);
                \App\Models\ActivityLog::record(
                    'login',
                    $isAdmin
                        ? "Admin ({$user->email} - {$user->effectiveAdminRole()}) logged into Admin Panel."
                        : "User {$user->email} logged in.",
                    [
                        'email' => $user->email,
                        'role' => $user->effectiveAdminRole(),
                        'panel' => $isAdmin ? 'admin' : 'tenant',
                    ],
                    null,
                    $user->id
                );
            } catch (\Throwable) {
                // Never block login if activity logging fails
            }
        });
    }
}
