@extends('layouts.tenant')

@section('title', 'Sales Tax Reports')

@section('content')
<div class="mb-4">
    <h1 class="font-display fw-bold text-dark fs-3 mb-1">Fiscal Sales Reports</h1>
    <div class="text-muted small">
        Daily, monthly, quarterly and yearly breakdown of sales tax accepted by FBR
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="ig-kpi-card">
            <div class="kpi-label">Month Sales (Excl. Tax)</div>
            <div class="kpi-value">Rs {{ number_format($monthlyTotalSales, 2) }}</div>
            <div class="kpi-subtext">{{ date('M Y') }} accepted production invoices</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ig-kpi-card">
            <div class="kpi-label">Sales Tax Collected</div>
            <div class="kpi-value text-green">Rs {{ number_format($monthlyTotalTax, 2) }}</div>
            <div class="kpi-subtext">Verified 18% GST + Further Tax</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ig-kpi-card">
            <div class="kpi-label">Gross Value</div>
            <div class="kpi-value">Rs {{ number_format($monthlyGross, 2) }}</div>
            <div class="kpi-subtext">Total fiscalized revenue</div>
        </div>
    </div>
</div>

<div class="ig-content-card p-0">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Production Sales Register (FBR Accepted)</h5>
        <button onclick="window.print()" class="btn btn-sm btn-ig-outline">
            <i class="bi bi-printer me-1"></i> Export / Print
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="bg-light text-muted text-uppercase" style="font-size: 11px;">
                <tr>
                    <th class="py-3 px-4">Date</th>
                    <th class="py-3">Invoice #</th>
                    <th class="py-3">Customer</th>
                    <th class="py-3">FBR Invoice #</th>
                    <th class="py-3 text-end">Excl. Tax</th>
                    <th class="py-3 text-end">Sales Tax</th>
                    <th class="py-3 text-end px-4">Gross Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($acceptedInvoices as $inv)
                    <tr>
                        <td class="py-3 px-4 text-muted">{{ $inv->invoice_date->format('d M Y') }}</td>
                        <td class="py-3 fw-bold">{{ $inv->invoice_number }}</td>
                        <td class="py-3">{{ $inv->customer->name ?? 'N/A' }}</td>
                        <td class="py-3 font-monospace text-primary fw-bold">{{ $inv->fbr_invoice_number }}</td>
                        <td class="py-3 text-end">Rs {{ number_format($inv->sales_value_excl_tax, 2) }}</td>
                        <td class="py-3 text-end">Rs {{ number_format($inv->sales_tax_amount + $inv->further_tax_amount, 2) }}</td>
                        <td class="py-3 text-end px-4 fw-bold">Rs {{ number_format($inv->total_invoice_value, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            No production invoices accepted by FBR yet for this period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
