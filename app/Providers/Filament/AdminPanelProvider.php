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
            ->brandLogo(fn () => view('filament.components.brand-logo'))
            ->brandLogoHeight('2rem')
            ->font('Inter')
            ->colors([
                'primary' => [
                    50 => '#EEF2FF',
                    100 => '#E0E7FF',
                    200 => '#C7D2FE',
                    300 => '#A5B4FC',
                    400 => '#818CF8',
                    500 => '#5850EC', // InvoiceGO Brand Primary
                    600 => '#4F46E5',
                    700 => '#4338CA',
                    800 => '#3730A3',
                    900 => '#312E81',
                    950 => '#1E1B4B',
                ],
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
                    <!-- InvoiceGO Fonts & Design System Styling -->
                    <link rel="preconnect" href="https://fonts.googleapis.com">
                    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
                    <style>
                        :root {
                            --ig-purple-primary: #5850EC;
                            --ig-purple-hover: #4F46E5;
                            --ig-purple-light: #EEF2FF;
                            --ig-bg-light: #F8FAFC;
                            --ig-card-border: #E2E8F0;
                        }

                        body, .fi-body {
                            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
                            -webkit-font-smoothing: antialiased !important;
                        }

                        /* Streamlined, Compact Premium Sidebar (Matching InvoiceGO Tenant Portal) */
                        .fi-sidebar {
                            scrollbar-width: none !important;
                            -ms-overflow-style: none !important;
                            border-right-color: #F1F5F9 !important;
                        }
                        .dark .fi-sidebar {
                            border-right-color: #1E293B !important;
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
                            font-weight: 700 !important;
                            letter-spacing: 0.08em !important;
                            text-transform: uppercase !important;
                            color: #94A3B8 !important;
                            margin-top: 0.35rem !important;
                        }

                        /* Sidebar Item Buttons Styled Like InvoiceGO .nav-link-ig */
                        .fi-sidebar-item-button {
                            padding: 0.4rem 0.65rem !important;
                            border-radius: 8px !important;
                            font-size: 13.5px !important;
                            font-weight: 500 !important;
                            color: #475569 !important;
                            transition: all 0.15s ease !important;
                        }
                        .dark .fi-sidebar-item-button {
                            color: #94A3B8 !important;
                        }
                        .fi-sidebar-item-button:hover {
                            color: #5850EC !important;
                            background-color: #F8FAFC !important;
                            transform: translateX(2px) !important;
                        }
                        .dark .fi-sidebar-item-button:hover {
                            color: #A5B4FC !important;
                            background-color: rgba(88, 80, 236, 0.12) !important;
                        }

                        /* Active Item (Matching InvoiceGO Tenant Sidebar Active State) */
                        .fi-sidebar-item.fi-active .fi-sidebar-item-button {
                            background-color: #EEF2FF !important;
                            color: #5850EC !important;
                            font-weight: 600 !important;
                        }
                        .dark .fi-sidebar-item.fi-active .fi-sidebar-item-button {
                            background-color: rgba(88, 80, 236, 0.22) !important;
                            color: #A5B4FC !important;
                        }
                        .fi-sidebar-item.fi-active .fi-sidebar-item-icon {
                            color: #5850EC !important;
                        }
                        .dark .fi-sidebar-item.fi-active .fi-sidebar-item-icon {
                            color: #A5B4FC !important;
                        }

                        .fi-sidebar-item-icon {
                            width: 1.15rem !important;
                            height: 1.15rem !important;
                            color: #64748B !important;
                            transition: color 0.15s ease !important;
                        }

                        .fi-sidebar-header {
                            height: 4rem !important;
                            padding-left: 1.25rem !important;
                            padding-right: 1.25rem !important;
                            border-bottom: 1px solid #F1F5F9 !important;
                        }
                        .dark .fi-sidebar-header {
                            border-bottom-color: #1E293B !important;
                        }

                        /* Topbar Matching InvoiceGO Tenant Topbar */
                        .fi-topbar {
                            height: 4rem !important;
                            border-bottom: 1px solid #F1F5F9 !important;
                        }
                        .dark .fi-topbar {
                            border-bottom-color: #1E293B !important;
                        }

                        /* Main Content Background */
                        .fi-main-ctn {
                            background-color: #F8FAFC !important;
                        }
                        .dark .fi-main-ctn {
                            background-color: #0B1120 !important;
                        }

                        /* Cards & Sections Rounded Style */
                        .fi-section, .fi-ta-ctn {
                            border-radius: 12px !important;
                            border-color: #E2E8F0 !important;
                            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
                        }
                        .dark .fi-section, .dark .fi-ta-ctn {
                            border-color: #1E293B !important;
                        }

                        /* Primary Buttons Matching .btn-ig-primary */
                        .fi-btn-primary {
                            background-color: #5850EC !important;
                            border-radius: 8px !important;
                            font-weight: 600 !important;
                            transition: all 0.2s ease !important;
                        }
                        .fi-btn-primary:hover {
                            background-color: #4F46E5 !important;
                            box-shadow: 0 4px 12px rgba(88, 80, 236, 0.25) !important;
                        }

                        /* Table Header Styling */
                        .fi-ta-header-cell {
                            background-color: #F8FAFC !important;
                            font-size: 11px !important;
                            font-weight: 700 !important;
                            letter-spacing: 0.05em !important;
                            text-transform: uppercase !important;
                            color: #475569 !important;
                        }
                        .dark .fi-ta-header-cell {
                            background-color: #0F172A !important;
                            color: #94A3B8 !important;
                        }

                        /* User Menu Avatar Matching Tenant Portal */
                        .fi-user-avatar {
                            background-color: #5850EC !important;
                            color: #FFFFFF !important;
                            font-weight: 700 !important;
                        }
                    </style>
                '
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::TOPBAR_START,
                fn (): string => '
                    <div class="hidden lg:flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 pl-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span style="font-size: 12px;">Times shown in Pakistan Standard Time (Asia/Karachi)</span>
                    </div>
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
