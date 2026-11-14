<?php

namespace App\Filament\Widgets;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $expiredSubscriptions = Subscription::where('status', 'expired')
            ->orWhere(function ($q) {
                $q->where('expires_at', '<', now());
            })->count();

        $totalInvoices = Invoice::count();
        $acceptedInvoices = Invoice::where('status', 'accepted')->count();
        $failedInvoices = Invoice::where('status', 'failed')->count();

        $totalRevenue = Payment::where('status', 'successful')->sum('amount');
        $monthlyRevenue = Payment::where('status', 'successful')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return [
            Stat::make('Total Registered Users', number_format($totalUsers))
                ->description("{$activeUsers} active accounts")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->chart([3, 7, 10, 15, 20, 24, max(1, $totalUsers)]),

            Stat::make('Active Users', number_format($activeUsers))
                ->description('Platform verified users')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Expired Subscriptions', number_format($expiredSubscriptions))
                ->description('Require renewal notification')
                ->descriptionIcon('heroicon-m-clock')
                ->color($expiredSubscriptions > 0 ? 'warning' : 'gray'),

            Stat::make('Total Invoices Created', number_format($totalInvoices))
                ->description("{$acceptedInvoices} accepted by FBR")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info')
                ->chart([5, 12, 18, 25, 30, max(1, $totalInvoices)]),

            Stat::make('FBR Accepted Invoices', number_format($acceptedInvoices))
                ->description('Successfully validated & fiscalized')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('success'),

            Stat::make('Failed FBR Invoices', number_format($failedInvoices))
                ->description('Requires review or retry')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($failedInvoices > 0 ? 'danger' : 'gray'),

            Stat::make('Total Revenue', 'Rs ' . number_format($totalRevenue, 2))
                ->description('All successful payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([1000, 3000, 5000, 10000, max(1, (int)$totalRevenue)]),

            Stat::make('Monthly Revenue', 'Rs ' . number_format($monthlyRevenue, 2))
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
        ];
    }
}
