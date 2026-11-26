@extends('layouts.tenant')

@section('title', 'Sales Tax Reports')

@push('styles')
<style>
    .rep-hero-header {
        background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFF 100%);
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    }
    .rep-hero-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, #1E50FF 0%, #1541D9 100%);
        color: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
        box-shadow: 0 6px 16px rgba(30, 80, 255, 0.22);
        flex-shrink: 0;
    }
    .rep-kpi-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 18px 20px;
        height: 100%;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
    }
    .rep-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        border-color: #CBD5E1;
    }
    .rep-kpi-card.tax-accent {
        background: linear-gradient(135deg, #FFFFFF 0%, #F0FDF7 100%);
        border-color: #BBF7D0;
    }
    .rep-kpi-card.gross-accent {
        background: linear-gradient(135deg, #FFFFFF 0%, #F5F8FF 100%);
        border-color: #DBEAFE;
    }
    .rep-kpi-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #64748B;
        margin-bottom: 5px;
    }
    .rep-kpi-val {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 22px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.15;
    }
    .rep-kpi-sub {
        font-size: 11.5px;
        color: #64748B;
        margin-top: 5px;
    }
    .rep-kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .rep-period-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 15px 17px;
        height: 100%;
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }
    .rep-period-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
    }
    .rep-period-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 6px;
        padding-bottom: 9px;
        margin-bottom: 9px;
        border-bottom: 1px solid #F1F5F9;
    }
    .rep-period-title {
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #1E50FF;
    }
    .rep-register-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.035);
        overflow: hidden;
    }
    .rep-filter-bar {
        padding: 14px 20px;
        background: #FAFBFD;
        border-bottom: 1px solid #E2E8F0;
    }
    .rep-tab-pill {
        display: inline-flex;
        align-items: center;
        padding: 6px 13px;
        border-radius: 999px;
        font-size: 12.5px;
        font-weight: 600;
        color: #475569;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .rep-tab-pill:hover {
        border-color: #BFDBFE;
        color: #1E50FF;
        background: #EFF6FF;
    }
    .rep-tab-pill.active {
        background: #1E50FF;
        border-color: #1E50FF;
        color: #FFFFFF;
        box-shadow: 0 4px 10px rgba(30, 80, 255, 0.22);
    }
    .rep-table thead th {
        background: #F8FAFC;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        border-bottom: 1px solid #E2E8F0;
        padding: 13px 16px;
        white-space: nowrap;
    }
    .rep-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #F1F5F9;
        font-size: 13px;
    }
    .rep-table tbody tr:hover {
        background: #F8FAFF;
    }
    .rep-table tfoot td {
        background: #F8FAFF;
        border-top: 2px solid #DBEAFE;
        padding: 13px 16px;
        font-weight: 800;
        font-size: 13.5px;
    }
    .rep-fbr-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 7px;
        background: #EFF6FF;
        color: #1D4ED8;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 11.5px;
        font-weight: 700;
        border: 1px solid #DBEAFE;
    }
    @media print {
        .tenant-sidebar, .tenant-topbar, .alert, .no-print { display: none !important; }
        .tenant-main-content { margin-left: 0 !important; padding: 0 !important; }
        .rep-hero-header, .rep-kpi-card, .rep-register-card { box-shadow: none !important; border: 1px solid #CBD5E1 !important; }
    }
</style>
@endpush

@section('content')
<!-- Executive Page Header -->
<div class="rep-hero-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="rep-hero-icon">
            <i class="bi bi-bar-chart-line-fill"></i>
        </div>
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <h1 class="font-display fw-bold text-dark fs-3 mb-0">Fiscal Sales Reports</h1>
                <span class="badge rounded-pill" style="background: #EFF6FF; color: #1E50FF; border: 1px solid #DBEAFE; font-size: 11.5px; font-weight: 700;">
                    {{ $periodLabel }} &middot; {{ ucfirst($environment) }}
                </span>
            </div>
            <div class="text-muted small mt-1">
                Daily, monthly, quarterly and yearly breakdown of sales tax accepted by FBR for <strong class="text-dark">{{ $business->name }}</strong>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center flex-wrap gap-2 no-print">
        <a href="{{ route('tenant.reports', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-ig-outline d-flex align-items-center gap-2" style="border-radius: 10px; font-size: 13.5px;">
            <i class="bi bi-file-earmark-spreadsheet text-success"></i>
            <span>Export CSV</span>
        </a>
        <button onclick="window.print()" class="btn btn-ig-primary d-flex align-items-center gap-2 px-3 py-2 shadow-sm" style="border-radius: 10px;">
            <i class="bi bi-printer"></i>
            <span>Export / Print</span>
        </button>
    </div>
</div>

<!-- 4 Executive KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="rep-kpi-card">
            <div style="min-width: 0;">
                <div class="rep-kpi-label">Month Sales (Excl. Tax)</div>
                <div class="rep-kpi-val">Rs {{ number_format($monthlyTotalSales, 2) }}</div>
                <div class="rep-kpi-sub">{{ $periodLabel }} accepted {{ $environment === 'all' ? '' : $environment . ' ' }}invoices</div>
            </div>
            <div class="rep-kpi-icon" style="background: #EFF6FF; color: #1E50FF;">
                <i class="bi bi-cash-coin"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="rep-kpi-card tax-accent">
            <div style="min-width: 0;">
                <div class="rep-kpi-label text-success">Sales Tax Collected</div>
                <div class="rep-kpi-val text-success">Rs {{ number_format($monthlyTotalTax, 2) }}</div>
                <div class="rep-kpi-sub">GST Rs {{ number_format($monthlySalesTax, 0) }} &middot; FT Rs {{ number_format($monthlyFurtherTax, 0) }}</div>
            </div>
            <div class="rep-kpi-icon" style="background: #ECFDF5; color: #059669;">
                <i class="bi bi-bank2"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="rep-kpi-card gross-accent">
            <div style="min-width: 0;">
                <div class="rep-kpi-label text-primary">Gross Value</div>
                <div class="rep-kpi-val text-primary">Rs {{ number_format($monthlyGross, 2) }}</div>
                <div class="rep-kpi-sub">Total fiscalized revenue</div>
            </div>
            <div class="rep-kpi-icon" style="background: #EFF6FF; color: #1E50FF;">
                <i class="bi bi-wallet2"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="rep-kpi-card">
            <div style="min-width: 0;">
                <div class="rep-kpi-label">Accepted Invoices</div>
                <div class="rep-kpi-val">{{ number_format($acceptedCount) }}</div>
                <div class="rep-kpi-sub">Avg: Rs {{ number_format($avgInvoiceValue, 2) }} / inv</div>
            </div>
            <div class="rep-kpi-icon" style="background: #FFFBEB; color: #D97706;">
                <i class="bi bi-patch-check-fill"></i>
            </div>
        </div>
    </div>
</div>

<!-- Daily / Monthly / Quarterly / Yearly Breakdown Strip -->
<div class="row g-3 mb-4">
    @php
        $icons = [
            'daily' => 'bi-calendar-event',
            'monthly' => 'bi-calendar-month',
            'quarterly' => 'bi-calendar3-week',
            'yearly' => 'bi-calendar-range',
        ];
    @endphp
    @foreach ($periodBreakdown as $pKey => $pData)
        <div class="col-6 col-xl-3">
            <div class="rep-period-card">
                <div class="rep-period-head">
                    <span class="rep-period-title">
                        <i class="bi {{ $icons[$pKey] ?? 'bi-calendar' }} me-1"></i>{{ $pData['label'] }}
                    </span>
                    <span class="badge bg-light text-dark border rounded-pill" style="font-size: 10.5px;">{{ $pData['count'] }} inv</span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Excl. Tax:</span>
                    <strong class="text-dark">Rs {{ number_format($pData['sales'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Total Tax:</span>
                    <strong class="text-success">Rs {{ number_format($pData['tax'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between small pt-1 border-top">
                    <span class="text-muted">Gross:</span>
                    <strong class="text-primary">Rs {{ number_format($pData['gross'], 2) }}</strong>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Production Sales Register Card -->
<div class="rep-register-card">
    <!-- Register Header + Quick Tabs -->
    <div class="px-4 py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3" style="width: 34px; height: 34px; background: #EFF6FF; color: #1E50FF;">
                <i class="bi bi-journal-check"></i>
            </div>
            <div>
                <h5 class="font-display fw-bold text-dark fs-6 mb-0">
                    {{ $environment === 'sandbox' ? 'Sandbox Sales Register (FBR Accepted)' : ($environment === 'all' ? 'All Accepted Sales Register' : 'Production Sales Register (FBR Accepted)') }}
                </h5>
                <div class="text-muted" style="font-size: 11.5px;">Verified FBR tax register ready for monthly sales tax return filing</div>
            </div>
        </div>

        <!-- Quick Period Tabs -->
        <div class="d-flex flex-wrap gap-1 no-print">
            @foreach (['today' => 'Today', 'this_month' => 'This Month', 'this_quarter' => 'Quarter', 'this_year' => 'This Year', 'all_time' => 'All Time'] as $key => $label)
                <a href="{{ route('tenant.reports', ['period' => $key, 'environment' => $environment]) }}"
                   class="rep-tab-pill {{ $period === $key ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="rep-filter-bar no-print">
        <form method="GET" action="{{ route('tenant.reports') }}" class="row g-2 align-items-end">
            <input type="hidden" name="period" value="{{ $period }}">
            <div class="col-md-3">
                <label class="form-label text-uppercase fw-bold text-muted mb-1" style="font-size: 10.5px; letter-spacing: 0.04em;">Environment</label>
                <select name="environment" class="form-select form-select-sm" style="border-radius: 9px;" onchange="this.form.submit()">
                    <option value="production" {{ $environment === 'production' ? 'selected' : '' }}>Production Only (Official)</option>
                    <option value="sandbox" {{ $environment === 'sandbox' ? 'selected' : '' }}>Sandbox Test Only</option>
                    <option value="all" {{ $environment === 'all' ? 'selected' : '' }}>All Environments</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-uppercase fw-bold text-muted mb-1" style="font-size: 10.5px; letter-spacing: 0.04em;">From Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm" style="border-radius: 9px;" value="{{ request('date_from') }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-uppercase fw-bold text-muted mb-1" style="font-size: 10.5px; letter-spacing: 0.04em;">To Date</label>
                <input type="date" name="date_to" class="form-control form-control-sm" style="border-radius: 9px;" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3 d-flex justify-content-md-end gap-2">
                <button type="submit" class="btn btn-sm btn-ig-primary px-3" style="border-radius: 9px; height: 32px;">
                    <i class="bi bi-funnel me-1"></i> Apply
                </button>
                <a href="{{ route('tenant.reports') }}" class="btn btn-sm btn-ig-outline px-3 d-inline-flex align-items-center" style="border-radius: 9px; height: 32px;">Reset</a>
            </div>
        </form>
    </div>

    <!-- Sales Register Table -->
    <div class="table-responsive">
        <table class="table rep-table mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Date</th>
                    <th>Invoice #</th>
                    <th>Customer</th>
                    <th>FBR Invoice #</th>
                    <th class="text-end">Excl. Tax</th>
                    <th class="text-end">Sales Tax</th>
                    <th class="text-end pe-4">Gross Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($acceptedInvoices as $inv)
                    <tr>
                        <td class="ps-4 text-muted text-nowrap small">
                            <i class="bi bi-calendar3 me-1 opacity-75"></i>{{ $inv->invoice_date->format('d M Y') }}
                        </td>
                        <td>
                            <a href="{{ route('tenant.invoices.show', $inv->uuid) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $inv->invoice_number }}
                            </a>
                            <div style="font-size: 10px;" class="{{ $inv->environment === 'production' ? 'text-success' : 'text-warning' }} fw-bold">
                                {{ strtoupper($inv->environment) }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $inv->buyer_name ?: ($inv->customer->name ?? 'N/A') }}</div>
                            @if ($inv->buyer_ntn_cnic || $inv->customer?->ntn_cnic)
                                <div class="text-muted font-monospace" style="font-size: 11px;">{{ $inv->buyer_ntn_cnic ?: $inv->customer?->ntn_cnic }}</div>
                            @endif
                        </td>
                        <td>
                            @if ($inv->fbr_invoice_number)
                                <span class="rep-fbr-pill">
                                    <i class="bi bi-patch-check-fill" style="font-size: 10px;"></i>
                                    {{ $inv->fbr_invoice_number }}
                                </span>
                            @else
                                <span class="text-muted small">&mdash;</span>
                            @endif
                        </td>
                        <td class="text-end fw-medium text-dark text-nowrap">Rs {{ number_format($inv->sales_value_excl_tax, 2) }}</td>
                        <td class="text-end fw-semibold text-success text-nowrap">Rs {{ number_format($inv->sales_tax_amount + $inv->further_tax_amount, 2) }}</td>
                        <td class="text-end pe-4 fw-bold text-primary text-nowrap">Rs {{ number_format($inv->total_invoice_value, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="mx-auto mb-3 rounded-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); border: 1px solid #BFDBFE;">
                                <i class="bi bi-file-earmark-bar-graph fs-2 text-primary"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">No {{ $environment === 'sandbox' ? 'sandbox' : 'production' }} invoices accepted by FBR yet for this period.</h6>
                            <p class="text-muted small mx-auto mb-3" style="max-width: 420px;">
                                Once invoices are fiscalized and accepted by FBR PRAL, your complete sales tax register and totals will appear automatically here.
                            </p>
                            <div class="d-flex justify-content-center gap-2 no-print">
                                <a href="{{ route('tenant.invoices.create') }}" class="btn btn-sm btn-ig-primary px-3 py-2" style="border-radius: 9px;">
                                    <i class="bi bi-file-earmark-plus me-1"></i> Create Invoice
                                </a>
                                @if ($environment === 'production')
                                    <a href="{{ route('tenant.reports', ['period' => 'all_time', 'environment' => 'sandbox']) }}" class="btn btn-sm btn-ig-outline px-3 py-2" style="border-radius: 9px;">
                                        <i class="bi bi-flask me-1 text-primary"></i> View Sandbox Report
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($acceptedInvoices->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="4" class="ps-4 text-uppercase text-muted small">Total ({{ $acceptedCount }} Accepted {{ $acceptedCount === 1 ? 'Invoice' : 'Invoices' }})</td>
                        <td class="text-end text-dark text-nowrap">Rs {{ number_format($monthlyTotalSales, 2) }}</td>
                        <td class="text-end text-success text-nowrap">Rs {{ number_format($monthlyTotalTax, 2) }}</td>
                        <td class="text-end pe-4 text-primary text-nowrap">Rs {{ number_format($monthlyGross, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
