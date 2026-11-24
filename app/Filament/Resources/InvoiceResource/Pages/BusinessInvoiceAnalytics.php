<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Business;
use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;

class BusinessInvoiceAnalytics extends Page
{
    protected static string $resource = InvoiceResource::class;

    protected static string $view = 'filament.resources.invoice-resource.pages.business-invoice-analytics';

    protected static ?string $title = 'Business-Wise Invoice Management & Analytics';

    public ?int $selectedBusinessId = null;

    public function mount(): void
    {
        $requested = request()->query('business_id');
        if ($requested && is_numeric($requested)) {
            $this->selectedBusinessId = (int) $requested;
        }
    }

    public function getViewData(): array
    {
        $businesses = Business::orderBy('name')->get(['id', 'name', 'ntn_cnic', 'province', 'status']);
        $selectedBusiness = $this->selectedBusinessId ? $businesses->firstWhere('id', $this->selectedBusinessId) : null;

        $baseQuery = Invoice::withoutGlobalScopes();
        if ($selectedBusiness) {
            $baseQuery->where('business_id', $selectedBusiness->id);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'successful' => (clone $baseQuery)->where('status', 'accepted')->count(),
            'failed' => (clone $baseQuery)->where('status', 'failed')->count(),
            'pending' => (clone $baseQuery)->whereIn('status', ['draft', 'pending', 'needs_reconciliation'])->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'total_sales' => (float) (clone $baseQuery)->where('status', '!=', 'cancelled')->sum('total_invoice_value'),
            'total_tax' => (float) (clone $baseQuery)->where('status', '!=', 'cancelled')->sum('sales_tax_amount'),
        ];

        $pktNow = Carbon::now('Asia/Karachi');
        $dailyQuery = (clone $baseQuery)->whereDate('invoice_date', $pktNow->toDateString());
        $monthlyQuery = (clone $baseQuery)->whereYear('invoice_date', $pktNow->year)->whereMonth('invoice_date', $pktNow->month);
        $yearlyQuery = (clone $baseQuery)->whereYear('invoice_date', $pktNow->year);

        $periodStats = [
            'daily' => [
                'label' => 'Today (' . $pktNow->format('d M Y') . ')',
                'count' => (clone $dailyQuery)->count(),
                'accepted' => (clone $dailyQuery)->where('status', 'accepted')->count(),
                'failed' => (clone $dailyQuery)->where('status', 'failed')->count(),
                'sales' => (float) (clone $dailyQuery)->where('status', '!=', 'cancelled')->sum('total_invoice_value'),
                'tax' => (float) (clone $dailyQuery)->where('status', '!=', 'cancelled')->sum('sales_tax_amount'),
            ],
            'monthly' => [
                'label' => 'This Month (' . $pktNow->format('M Y') . ')',
                'count' => (clone $monthlyQuery)->count(),
                'accepted' => (clone $monthlyQuery)->where('status', 'accepted')->count(),
                'failed' => (clone $monthlyQuery)->where('status', 'failed')->count(),
                'sales' => (float) (clone $monthlyQuery)->where('status', '!=', 'cancelled')->sum('total_invoice_value'),
                'tax' => (float) (clone $monthlyQuery)->where('status', '!=', 'cancelled')->sum('sales_tax_amount'),
            ],
            'yearly' => [
                'label' => 'This Year (' . $pktNow->year . ')',
                'count' => (clone $yearlyQuery)->count(),
                'accepted' => (clone $yearlyQuery)->where('status', 'accepted')->count(),
                'failed' => (clone $yearlyQuery)->where('status', 'failed')->count(),
                'sales' => (float) (clone $yearlyQuery)->where('status', '!=', 'cancelled')->sum('total_invoice_value'),
                'tax' => (float) (clone $yearlyQuery)->where('status', '!=', 'cancelled')->sum('sales_tax_amount'),
            ],
        ];

        $businessBreakdown = Business::withCount([
            'invoices as total_invoices',
            'invoices as accepted_invoices' => fn ($q) => $q->where('status', 'accepted'),
            'invoices as failed_invoices' => fn ($q) => $q->where('status', 'failed'),
            'invoices as pending_invoices' => fn ($q) => $q->whereIn('status', ['draft', 'pending', 'needs_reconciliation']),
        ])
            ->withSum(['invoices as total_sales_amount' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'total_invoice_value')
            ->withSum(['invoices as total_tax_amount' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'sales_tax_amount')
            ->orderByDesc('total_invoices')
            ->limit(25)
            ->get();

        $recentInvoices = (clone $baseQuery)
            ->with(['business', 'creator', 'customer'])
            ->latest('id')
            ->limit(25)
            ->get();

        return [
            'businesses' => $businesses,
            'selectedBusiness' => $selectedBusiness,
            'stats' => $stats,
            'periodStats' => $periodStats,
            'businessBreakdown' => $businessBreakdown,
            'recentInvoices' => $recentInvoices,
        ];
    }
}
