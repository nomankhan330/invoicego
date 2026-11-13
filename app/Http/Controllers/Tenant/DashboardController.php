<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Contracts\InvoiceServiceInterface;
use App\Services\Contracts\TenantServiceInterface;

class DashboardController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $tenantService,
        protected InvoiceServiceInterface $invoiceService
    ) {}

    public function index()
    {
        $business = $this->tenantService->getActiveBusiness();

        if (!$business) {
            return redirect()->route('register');
        }

        $metrics = $this->invoiceService->getDashboardMetrics($business);
        $recentInvoices = $this->invoiceService->getRecentInvoices($business, 5);
        $recentFbrActivity = $this->invoiceService->getRecentFbrActivity($business, 5);

        return view('tenant.dashboard', compact('business', 'metrics', 'recentInvoices', 'recentFbrActivity'));
    }
}
