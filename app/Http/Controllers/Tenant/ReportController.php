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
        $startOfMonth = $pktNow->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $pktNow->copy()->endOfMonth()->format('Y-m-d');

        $acceptedInvoices = $business->invoices()
            ->where('status', 'accepted')
            ->where('environment', 'production')
            ->latest('invoice_date')
            ->get();

        $monthlyTotalSales = $acceptedInvoices->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])->sum('sales_value_excl_tax');
        $monthlyTotalTax = $acceptedInvoices->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])->sum('sales_tax_amount');
        $monthlyGross = $acceptedInvoices->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])->sum('total_invoice_value');

        return view('tenant.reports.index', compact('business', 'acceptedInvoices', 'monthlyTotalSales', 'monthlyTotalTax', 'monthlyGross'));
    }
}

