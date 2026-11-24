<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InvoiceStatsWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $base = Invoice::withoutGlobalScopes();

        $total = (clone $base)->count();
        $fbrSubmitted = (clone $base)->where(function ($q) {
            $q->whereNotNull('fbr_submitted_at')
              ->orWhereIn('status', ['submitted', 'accepted', 'failed']);
        })->count();
        $successful = (clone $base)->where('status', 'accepted')->count();
        $failed = (clone $base)->where('status', 'failed')->count();
        $pending = (clone $base)->whereIn('status', ['draft', 'pending', 'needs_reconciliation'])->count();
        $cancelled = (clone $base)->where('status', 'cancelled')->count();
        $totalAmount = (float) (clone $base)->where('status', '!=', 'cancelled')->sum('total_invoice_value');
        $totalSalesTax = (float) (clone $base)->where('status', '!=', 'cancelled')->sum('sales_tax_amount');

        return [
            Stat::make('Total Invoices', number_format($total))
                ->description('Across all businesses')
                ->icon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('FBR Submitted Invoices', number_format($fbrSubmitted))
                ->description('Transmitted to PRAL gateway')
                ->icon('heroicon-o-paper-airplane')
                ->color('info'),

            Stat::make('Successful Invoices', number_format($successful))
                ->description('Valid & fiscalized by FBR')
                ->icon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Failed Invoices', number_format($failed))
                ->description('Rejected or validation error')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Pending Invoices', number_format($pending))
                ->description('Draft, pending, or reconciling')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Cancelled Invoices', number_format($cancelled))
                ->description('Voided / cancelled records')
                ->icon('heroicon-o-archive-box-x-mark')
                ->color('gray'),

            Stat::make('Total Invoice Amount', 'PKR ' . number_format($totalAmount, 2))
                ->description('Gross active invoice volume')
                ->icon('heroicon-o-banknotes')
                ->color('primary'),

            Stat::make('Total Sales Tax Collected', 'PKR ' . number_format($totalSalesTax, 2))
                ->description('Cumulative sales tax')
                ->icon('heroicon-o-calculator')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
