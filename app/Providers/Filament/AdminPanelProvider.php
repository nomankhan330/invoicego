<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('InvoiceGO Admin')
            ->colors([
                'primary' => Color::Indigo,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ])
            ->darkMode(true)
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '
                    <style>
                        /* Streamlined, Compact Premium Sidebar Without Scrollbar */
                        .fi-sidebar {
                            scrollbar-width: none !important;
                            -ms-overflow-style: none !important;
                        }
                        .fi-sidebar::-webkit-scrollbar {
                            display: none !important;
                            width: 0 !important;
                            height: 0 !important;
                        }
                        .fi-sidebar-nav {
                            scrollbar-width: none !important;
                            -ms-overflow-style: none !important;
                            scrollbar-gutter: auto !important;
                            gap: 0.5rem !important;
                            padding: 0.75rem 0.875rem !important;
                        }
                        .fi-sidebar-nav::-webkit-scrollbar {
                            display: none !important;
                            width: 0 !important;
                            height: 0 !important;
                        }
                        .fi-sidebar-nav-groups {
                            gap: 0.5rem !important;
                            margin: 0 !important;
                        }
                        .fi-sidebar-group {
                            gap: 0.125rem !important;
                        }
                        .fi-sidebar-group-button {
                            padding: 0.2rem 0.5rem !important;
                        }
                        .fi-sidebar-group-label {
                            font-size: 0.65rem !important;
                            font-weight: 800 !important;
                            letter-spacing: 0.08em !important;
                            text-transform: uppercase !important;
                            opacity: 0.75 !important;
                        }
                        .fi-sidebar-item-button {
                            padding: 0.35rem 0.625rem !important;
                            border-radius: 0.5rem !important;
                            font-size: 0.8125rem !important;
                            transition: all 0.15s ease !important;
                        }
                        .fi-sidebar-item-button:hover {
                            transform: translateX(2px) !important;
                        }
                        .fi-sidebar-item-icon {
                            width: 1.125rem !important;
                            height: 1.125rem !important;
                        }
                        .fi-sidebar-header {
                            height: 3.5rem !important;
                            padding-left: 1rem !important;
                            padding-right: 1rem !important;
                        }
                    </style>
                '
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => \Illuminate\Support\Facades\Blade::render('<div class="mr-2 flex items-center"><x-filament-panels::theme-switcher /></div>')
            )
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Overview',
                'Users & Billing',
                'Invoices & FBR',
                'System & Support',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverviewWidget::class,
                \App\Filament\Widgets\RecentActivitiesWidget::class,
                \App\Filament\Widgets\RecentInvoicesWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
