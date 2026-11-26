<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Contracts\TenantServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $tenantService
    ) {}

    public function index(Request $request)
    {
        $business = $this->tenantService->getActiveBusiness();
        $pktNow = Carbon::now('Asia/Karachi');

        $period = $request->get('period', 'this_month');
        $environment = $request->get('environment', 'production');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Base accepted query scoped by environment selection
        $baseQuery = $business->invoices()->with('customer')->where('status', 'accepted');
        if (in_array($environment, ['production', 'sandbox'], true)) {
            $baseQuery->where('environment', $environment);
        }

        // Daily / Monthly / Quarterly / Yearly summary strip
        $todayStr = $pktNow->toDateString();
        $monthStart = $pktNow->copy()->startOfMonth()->toDateString();
        $monthEnd = $pktNow->copy()->endOfMonth()->toDateString();
        $quarterStart = $pktNow->copy()->firstOfQuarter()->toDateString();
        $quarterEnd = $pktNow->copy()->lastOfQuarter()->toDateString();
        $yearStart = $pktNow->copy()->startOfYear()->toDateString();
        $yearEnd = $pktNow->copy()->endOfYear()->toDateString();

        $buildStats = function (string $from, string $to, string $label) use ($baseQuery): array {
            $q = (clone $baseQuery)->whereDate('invoice_date', '>=', $from)->whereDate('invoice_date', '<=', $to);
            return [
                'label' => $label,
                'count' => (clone $q)->count(),
                'sales' => (float) (clone $q)->sum('sales_value_excl_tax'),
                'tax' => (float) (clone $q)->sum('sales_tax_amount') + (float) (clone $q)->sum('further_tax_amount'),
                'gross' => (float) (clone $q)->sum('total_invoice_value'),
            ];
        };

        $periodBreakdown = [
            'daily' => $buildStats($todayStr, $todayStr, 'Today (' . $pktNow->format('d M') . ')'),
            'monthly' => $buildStats($monthStart, $monthEnd, 'This Month (' . $pktNow->format('M Y') . ')'),
            'quarterly' => $buildStats($quarterStart, $quarterEnd, 'Q' . $pktNow->quarter . ' ' . $pktNow->year),
            'yearly' => $buildStats($yearStart, $yearEnd, 'Year ' . $pktNow->year),
        ];

        // Apply active period / custom date filters for the register table & hero KPIs
        $filteredQuery = clone $baseQuery;
        if ($dateFrom || $dateTo) {
            $period = 'custom';
            if ($dateFrom) {
                $filteredQuery->whereDate('invoice_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $filteredQuery->whereDate('invoice_date', '<=', $dateTo);
            }
            $periodLabel = trim(($dateFrom ?: 'Start') . ' to ' . ($dateTo ?: 'Today'));
        } else {
            match ($period) {
                'today' => $filteredQuery->whereDate('invoice_date', $todayStr),
                'this_quarter' => $filteredQuery->whereDate('invoice_date', '>=', $quarterStart)->whereDate('invoice_date', '<=', $quarterEnd),
                'this_year' => $filteredQuery->whereDate('invoice_date', '>=', $yearStart)->whereDate('invoice_date', '<=', $yearEnd),
                'all_time' => null,
                default => $filteredQuery->whereDate('invoice_date', '>=', $monthStart)->whereDate('invoice_date', '<=', $monthEnd),
            };
            $periodLabel = match ($period) {
                'today' => 'Today (' . $pktNow->format('d M Y') . ')',
                'this_quarter' => 'Q' . $pktNow->quarter . ' ' . $pktNow->year,
                'this_year' => 'Year ' . $pktNow->year,
                'all_time' => 'All Time',
                default => $pktNow->format('M Y'),
            };
        }

        $acceptedInvoices = $filteredQuery->latest('invoice_date')->latest('id')->get();

        // Stream CSV export if requested
        if ($request->get('export') === 'csv') {
            $filename = 'invoicego-sales-register-' . $pktNow->format('Y-m-d') . '.csv';
            return response()->streamDownload(function () use ($acceptedInvoices) {
                $out = fopen('php://output', 'w');
                fputcsv($out, [
                    'Date',
                    'Invoice #',
                    'Environment',
                    'Customer Name',
                    'Buyer NTN/CNIC',
                    'FBR Invoice #',
                    'Sales Value (Excl. Tax)',
                    'Sales Tax',
                    'Further Tax',
                    'Gross Total (PKR)',
                ]);
                foreach ($acceptedInvoices as $inv) {
                    fputcsv($out, [
                        $inv->invoice_date?->format('Y-m-d'),
                        $inv->invoice_number,
                        strtoupper($inv->environment ?? 'production'),
                        $inv->buyer_name ?: ($inv->customer->name ?? 'N/A'),
                        $inv->buyer_ntn_cnic ?: ($inv->customer->ntn_cnic ?? ''),
                        $inv->fbr_invoice_number ?? '',
                        number_format((float) $inv->sales_value_excl_tax, 2, '.', ''),
                        number_format((float) $inv->sales_tax_amount, 2, '.', ''),
                        number_format((float) $inv->further_tax_amount, 2, '.', ''),
                        number_format((float) $inv->total_invoice_value, 2, '.', ''),
                    ]);
                }
                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        $monthlyTotalSales = (float) $acceptedInvoices->sum('sales_value_excl_tax');
        $monthlySalesTax = (float) $acceptedInvoices->sum('sales_tax_amount');
        $monthlyFurtherTax = (float) $acceptedInvoices->sum('further_tax_amount');
        $monthlyTotalTax = $monthlySalesTax + $monthlyFurtherTax;
        $monthlyGross = (float) $acceptedInvoices->sum('total_invoice_value');
        $acceptedCount = $acceptedInvoices->count();
        $avgInvoiceValue = $acceptedCount > 0 ? ($monthlyGross / $acceptedCount) : 0.0;

        return view('tenant.reports.index', compact(
            'business',
            'acceptedInvoices',
            'monthlyTotalSales',
            'monthlySalesTax',
            'monthlyFurtherTax',
            'monthlyTotalTax',
            'monthlyGross',
            'acceptedCount',
            'avgInvoiceValue',
            'period',
            'periodLabel',
            'environment',
            'periodBreakdown'
        ));
    }
}
