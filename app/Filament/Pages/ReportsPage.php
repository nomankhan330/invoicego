<?php

namespace App\Filament\Pages;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reports & Analytics';
    protected static ?string $navigationGroup = 'Overview';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.reports-page';

    public function getHeading(): string
    {
        return 'Executive Reports & Analytics';
    }

    public function getSubheading(): string
    {
        return 'Comprehensive metrics on platform registrations, subscription lifecycles, revenue streams, and FBR DI API fiscalization.';
    }

    public function getViewData(): array
    {
        $todayRegistrations = User::whereDate('created_at', Carbon::today())->count();
        $weekRegistrations = User::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $monthRegistrations = User::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count();

        $activeSubscriptions = Subscription::where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now()))
            ->count();

        $expiredSubscriptions = Subscription::where('status', 'expired')
            ->orWhere(fn ($q) => $q->where('expires_at', '<', Carbon::now()))
            ->count();

        $monthlyRevenue = Payment::where('status', 'successful')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        $totalRevenue = Payment::where('status', 'successful')->sum('amount');

        $acceptedInvoices = Invoice::where('status', 'accepted')->count();
        $failedInvoices = Invoice::where('status', 'failed')->count();
        $pendingInvoices = Invoice::whereIn('status', ['pending', 'draft', 'needs_reconciliation'])->count();
        $totalInvoices = Invoice::count();
        $successRate = $totalInvoices > 0 ? round(($acceptedInvoices / $totalInvoices) * 100, 1) : 100;

        // User / Business invoice breakdown
        $businessReports = Business::withCount(['invoices as total_invoices'])
            ->withCount(['invoices as accepted_invoices' => fn ($q) => $q->where('status', 'accepted')])
            ->withCount(['invoices as failed_invoices' => fn ($q) => $q->where('status', 'failed')])
            ->withSum('invoices as total_sales', 'total_invoice_value')
            ->get();

        return [
            'todayRegistrations' => $todayRegistrations,
            'weekRegistrations' => $weekRegistrations,
            'monthRegistrations' => $monthRegistrations,
            'activeSubscriptions' => $activeSubscriptions,
            'expiredSubscriptions' => $expiredSubscriptions,
            'monthlyRevenue' => $monthlyRevenue,
            'totalRevenue' => $totalRevenue,
            'acceptedInvoices' => $acceptedInvoices,
            'failedInvoices' => $failedInvoices,
            'pendingInvoices' => $pendingInvoices,
            'totalInvoices' => $totalInvoices,
            'successRate' => $successRate,
            'businessReports' => $businessReports,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_csv')
                ->label('Export User Report (CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        $businesses = Business::withCount(['invoices as total_invoices'])
            ->withCount(['invoices as accepted_invoices' => fn ($q) => $q->where('status', 'accepted')])
            ->withCount(['invoices as failed_invoices' => fn ($q) => $q->where('status', 'failed')])
            ->withSum('invoices as total_sales', 'total_invoice_value')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="invoicego_user_reports_' . date('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($businesses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Business Name', 'NTN/CNIC', 'Province', 'Sector', 'Total Invoices', 'FBR Accepted', 'FBR Failed', 'Total Sales (PKR)', 'Status']);

            foreach ($businesses as $b) {
                fputcsv($handle, [
                    $b->name,
                    $b->ntn_cnic,
                    $b->province,
                    $b->sector,
                    $b->total_invoices ?? 0,
                    $b->accepted_invoices ?? 0,
                    $b->failed_invoices ?? 0,
                    number_format((float) ($b->total_sales ?? 0), 2),
                    $b->status,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
