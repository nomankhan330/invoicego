@extends('layouts.tenant')

@section('title', 'Invoices')

@push('styles')
<style>
    .inv-hero-header {
        background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFF 100%);
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 18px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    }
    .inv-hero-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, #1E50FF 0%, #1541D9 100%);
        color: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        box-shadow: 0 6px 16px rgba(30, 80, 255, 0.22);
        flex-shrink: 0;
    }
    .inv-quota-card {
        background: linear-gradient(90deg, #FFFFFF 0%, #F8FAFF 100%);
        border: 1px solid #DBEAFE;
        border-radius: 14px;
        padding: 14px 18px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(30, 80, 255, 0.04);
    }
    .inv-kpi-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 16px 18px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
    }
    .inv-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        border-color: #CBD5E1;
    }
    .inv-kpi-card.amount-card {
        background: linear-gradient(135deg, #FFFFFF 0%, #F5F8FF 100%);
        border-color: #DBEAFE;
    }
    .inv-kpi-card.tax-card {
        background: linear-gradient(135deg, #FFFFFF 0%, #F0FDF7 100%);
        border-color: #BBF7D0;
    }
    .inv-kpi-label {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #64748B;
        margin-bottom: 5px;
    }
    .inv-kpi-val {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 22px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.15;
    }
    .inv-kpi-icon {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .inv-period-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 16px 18px;
        height: 100%;
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }
    .inv-period-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
    }
    .inv-period-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        padding-bottom: 10px;
        margin-bottom: 10px;
        border-bottom: 1px solid #F1F5F9;
    }
    .inv-period-title {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #1E50FF;
    }
    .inv-period-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 999px;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #334155;
        white-space: nowrap;
    }
    .inv-workspace-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.035);
        overflow: hidden;
    }
    .inv-tabs-bar {
        padding: 14px 20px;
        background: #FFFFFF;
        border-bottom: 1px solid #E2E8F0;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .inv-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 13px;
        border-radius: 999px;
        font-size: 12.5px;
        font-weight: 600;
        color: #475569;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .inv-status-pill:hover {
        border-color: #BFDBFE;
        color: #1E50FF;
        background: #EFF6FF;
    }
    .inv-status-pill.active {
        background: #1E50FF;
        border-color: #1E50FF;
        color: #FFFFFF;
        box-shadow: 0 4px 10px rgba(30, 80, 255, 0.22);
    }
    .inv-pill-count {
        font-size: 11px;
        font-weight: 700;
        padding: 1px 7px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.07);
    }
    .inv-status-pill.active .inv-pill-count {
        background: rgba(255, 255, 255, 0.24);
        color: #FFFFFF;
    }
    .inv-filter-toolbar {
        padding: 16px 20px;
        background: #FAFBFD;
        border-bottom: 1px solid #E2E8F0;
    }
    .inv-filter-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748B;
        margin-bottom: 4px;
    }
    .inv-search-wrap {
        position: relative;
    }
    .inv-search-wrap .bi-search {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 13px;
        pointer-events: none;
    }
    .inv-search-wrap input {
        padding-left: 32px;
        border-radius: 9px;
        border-color: #CBD5E1;
    }
    .inv-filter-control {
        border-radius: 9px !important;
        border-color: #CBD5E1 !important;
    }
    .inv-search-wrap input:focus,
    .inv-filter-control:focus {
        border-color: #1E50FF !important;
        box-shadow: 0 0 0 3px rgba(30, 80, 255, 0.12) !important;
    }
    .inv-table thead th {
        background: #F8FAFC;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        border-bottom: 1px solid #E2E8F0;
        padding: 13px 14px;
        white-space: nowrap;
    }
    .inv-table tbody tr {
        transition: background-color 0.12s ease;
    }
    .inv-table tbody tr:hover {
        background-color: #F8FAFF;
    }
    .inv-table tbody td {
        padding: 14px;
        vertical-align: middle;
        border-bottom: 1px solid #F1F5F9;
        font-size: 13px;
    }
    .inv-id-pill {
        display: inline-flex;
        padding: 3px 8px;
        border-radius: 6px;
        background: #F1F5F9;
        color: #334155;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 11.5px;
        font-weight: 700;
        border: 1px solid #E2E8F0;
    }
    .inv-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
        white-space: nowrap;
    }
    .inv-badge-success { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
    .inv-badge-danger  { background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; }
    .inv-badge-warning { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }
    .inv-badge-info    { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
    .inv-badge-muted   { background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; }
    .inv-action-btn {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        color: #475569;
        text-decoration: none;
        transition: all 0.15s ease;
        padding: 0;
    }
    .inv-action-btn.view:hover   { background: #EFF6FF; border-color: #BFDBFE; color: #1E50FF; }
    .inv-action-btn.edit:hover   { background: #F1F5F9; border-color: #CBD5E1; color: #0F172A; }
    .inv-action-btn.print:hover  { background: #F8FAFC; border-color: #94A3B8; color: #0F172A; }
    .inv-action-btn.cancel:hover { background: #FEF2F2; border-color: #FECACA; color: #DC2626; }
</style>
@endpush

@section('content')
<!-- Executive Page Header -->
<div class="inv-hero-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="inv-hero-icon">
            <i class="bi bi-receipt-cutoff"></i>
        </div>
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <h1 class="font-display fw-bold text-dark fs-3 mb-0">Invoice Management</h1>
                @if (($business->current_environment ?? 'sandbox') === 'production' && $business->production_submissions_enabled)
                    <span class="badge rounded-pill" style="background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; font-size: 11px; font-weight: 700;">
                        <i class="bi bi-circle-fill me-1" style="font-size: 7px;"></i> Live Production
                    </span>
                @else
                    <span class="badge rounded-pill" style="background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; font-size: 11px; font-weight: 700;">
                        <i class="bi bi-circle-fill me-1" style="font-size: 7px;"></i> Sandbox Mode
                    </span>
                @endif
            </div>
            <div class="text-muted small mt-1">Manage, filter, fiscalize, and analyze sales tax invoices for <strong class="text-dark">{{ $business->name }}</strong></div>
        </div>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2">
        @if (!empty($invoiceQuota['reached']))
            <button type="button" class="btn btn-ig-outline d-flex align-items-center gap-2 disabled" style="border-radius: 10px;" disabled title="Invoice limit reached ({{ $invoiceQuota['used'] }} / {{ $invoiceQuota['limit'] }})">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                <span>Bulk Upload (Excel)</span>
            </button>
            <button type="button" class="btn btn-secondary d-flex align-items-center gap-2 disabled" style="border-radius: 10px;" disabled title="Invoice limit reached ({{ $invoiceQuota['used'] }} / {{ $invoiceQuota['limit'] }})">
                <i class="bi bi-lock-fill"></i>
                <span>Limit Reached ({{ $invoiceQuota['used'] }}/{{ $invoiceQuota['limit'] }})</span>
            </button>
        @else
            <a href="{{ route('tenant.invoices.bulk-upload') }}" class="btn btn-ig-outline d-flex align-items-center gap-2" style="border-radius: 10px; font-size: 13.5px;">
                <i class="bi bi-file-earmark-spreadsheet text-success"></i>
                <span>Bulk Upload (Excel)</span>
            </a>
            <a href="{{ route('tenant.invoices.create') }}" class="btn btn-ig-primary d-flex align-items-center gap-2 px-3 py-2 shadow-sm" style="border-radius: 10px;">
                <i class="bi bi-file-earmark-plus"></i>
                <span>Create invoice</span>
            </a>
        @endif
    </div>
</div>

<!-- Invoice Quota Card -->
@if (isset($invoiceQuota))
    @if (!empty($invoiceQuota['reached']))
        <div class="alert alert-danger d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 rounded-4 border-danger-subtle shadow-sm py-3 px-4">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-octagon-fill fs-4"></i>
                <div>
                    <div class="fw-bold">Invoice Limit Reached ({{ $invoiceQuota['used'] }} / {{ $invoiceQuota['limit'] }})</div>
                    <div class="small">You have used all {{ $invoiceQuota['limit'] }} monthly invoices for <strong>{{ $invoiceQuota['period_label'] }}</strong>. Creating invoice #{{ $invoiceQuota['limit'] + 1 }} is blocked until your quota auto-renews on <strong>{{ $invoiceQuota['renews_on'] }}</strong>.</div>
                </div>
            </div>
            <span class="badge bg-danger px-3 py-2 rounded-pill">Renews {{ $invoiceQuota['renews_on'] }}</span>
        </div>
    @elseif (empty($invoiceQuota['unlimited']))
        @php
            $barColor = $invoiceQuota['percent'] >= 90 ? '#DC2626' : ($invoiceQuota['percent'] >= 75 ? '#D97706' : '#1E50FF');
        @endphp
        <div class="inv-quota-card">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="badge rounded-pill px-2 py-1" style="background: #EFF6FF; color: #1E50FF; border: 1px solid #BFDBFE; font-weight: 700; font-size: 11.5px;">
                        <i class="bi bi-speedometer2 me-1"></i> Monthly Quota
                    </span>
                    <span class="fw-bold text-dark small">
                        {{ number_format($invoiceQuota['used']) }} / {{ number_format($invoiceQuota['limit']) }} Invoices Used
                    </span>
                    <span class="text-muted small">({{ $invoiceQuota['percent'] }}%)</span>
                </div>
                <div class="small text-muted">
                    <strong class="text-primary">{{ number_format($invoiceQuota['remaining']) }}</strong> remaining &middot; Cycle: <strong class="text-dark">{{ $invoiceQuota['period_label'] }}</strong> &middot; Auto-renews <strong class="text-dark">{{ $invoiceQuota['renews_on'] }}</strong> ({{ $invoiceQuota['days_left'] }}d left)
                </div>
            </div>
            <div class="progress" style="height: 8px; background-color: #E2E8F0; border-radius: 999px;">
                <div class="progress-bar" role="progressbar" style="width: {{ max(2, $invoiceQuota['percent']) }}%; background-color: {{ $barColor }}; border-radius: 999px; transition: width 0.3s ease;"></div>
            </div>
        </div>
    @endif
@endif

<!-- 8 Executive KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="inv-kpi-card">
            <div>
                <div class="inv-kpi-label">Total Invoices</div>
                <div class="inv-kpi-val">{{ number_format($summary['total']) }}</div>
            </div>
            <div class="inv-kpi-icon" style="background: #EFF6FF; color: #1E50FF;">
                <i class="bi bi-files"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="inv-kpi-card">
            <div>
                <div class="inv-kpi-label" style="color: #0284C7;">FBR Submitted</div>
                <div class="inv-kpi-val" style="color: #0284C7;">{{ number_format($summary['fbr_submitted']) }}</div>
            </div>
            <div class="inv-kpi-icon" style="background: #F0F9FF; color: #0284C7;">
                <i class="bi bi-cloud-arrow-up-fill"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="inv-kpi-card">
            <div>
                <div class="inv-kpi-label text-success">Successful (Valid)</div>
                <div class="inv-kpi-val text-success">{{ number_format($summary['successful']) }}</div>
            </div>
            <div class="inv-kpi-icon" style="background: #ECFDF5; color: #059669;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="inv-kpi-card">
            <div>
                <div class="inv-kpi-label text-danger">Failed Invoices</div>
                <div class="inv-kpi-val text-danger">{{ number_format($summary['failed']) }}</div>
            </div>
            <div class="inv-kpi-icon" style="background: #FEF2F2; color: #DC2626;">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="inv-kpi-card">
            <div>
                <div class="inv-kpi-label" style="color: #B45309;">Pending / Draft</div>
                <div class="inv-kpi-val" style="color: #B45309;">{{ number_format($summary['pending']) }}</div>
            </div>
            <div class="inv-kpi-icon" style="background: #FFFBEB; color: #D97706;">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="inv-kpi-card">
            <div>
                <div class="inv-kpi-label">Cancelled</div>
                <div class="inv-kpi-val text-secondary">{{ number_format($summary['cancelled']) }}</div>
            </div>
            <div class="inv-kpi-icon" style="background: #F1F5F9; color: #64748B;">
                <i class="bi bi-slash-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="inv-kpi-card amount-card">
            <div style="min-width: 0;">
                <div class="inv-kpi-label text-primary">Total Invoice Amount</div>
                <div class="inv-kpi-val" style="font-size: 18px;">PKR {{ number_format($summary['total_amount'], 2) }}</div>
            </div>
            <div class="inv-kpi-icon" style="background: #EFF6FF; color: #1E50FF;">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="inv-kpi-card tax-card">
            <div style="min-width: 0;">
                <div class="inv-kpi-label text-success">Total Sales Tax Collected</div>
                <div class="inv-kpi-val text-success" style="font-size: 18px;">PKR {{ number_format($summary['total_sales_tax'], 2) }}</div>
            </div>
            <div class="inv-kpi-icon" style="background: #ECFDF5; color: #059669;">
                <i class="bi bi-bank2"></i>
            </div>
        </div>
    </div>
</div>

<!-- Daily / Monthly / Yearly Analytics Strip -->
<div class="row g-3 mb-4">
    @php
        $periodIcons = [
            'daily' => 'bi-calendar-event',
            'monthly' => 'bi-calendar-month',
            'yearly' => 'bi-calendar-range',
        ];
    @endphp
    @foreach ($analytics as $key => $period)
        <div class="col-md-4">
            <div class="inv-period-card">
                <div class="inv-period-header">
                    <span class="inv-period-title">
                        <i class="bi {{ $periodIcons[$key] ?? 'bi-calendar3' }}"></i>
                        <span>{{ $period['label'] }}</span>
                    </span>
                    <span class="inv-period-badge">
                        {{ $period['count'] }} inv &middot; <span class="text-success">{{ $period['valid'] }} valid</span> / <span class="text-danger">{{ $period['failed'] }} fail</span>
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center small mb-1">
                    <span class="text-muted">Sales Value:</span>
                    <strong class="text-dark" style="font-size: 13.5px;">PKR {{ number_format($period['sales'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center small">
                    <span class="text-muted">Sales Tax:</span>
                    <strong class="text-success" style="font-size: 13.5px;">PKR {{ number_format($period['tax'], 2) }}</strong>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Unified Invoices Workspace Card (Tabs + Filters + Table) -->
<div class="inv-workspace-card">
    <!-- Quick Status Tabs -->
    <div class="inv-tabs-bar">
        @php $curStatus = request('status', ''); @endphp
        <a href="{{ route('tenant.invoices.index', array_merge(request()->except('status', 'page'))) }}" class="inv-status-pill {{ $curStatus === '' ? 'active' : '' }}">
            All Invoices <span class="inv-pill-count">{{ $summary['total'] }}</span>
        </a>
        <a href="{{ route('tenant.invoices.index', array_merge(request()->except('page'), ['status' => 'accepted'])) }}" class="inv-status-pill {{ $curStatus === 'accepted' ? 'active' : '' }}">
            <i class="bi bi-check-circle" style="font-size: 12px;"></i>
            Successful <span class="inv-pill-count">{{ $summary['successful'] }}</span>
        </a>
        <a href="{{ route('tenant.invoices.index', array_merge(request()->except('page'), ['status' => 'failed'])) }}" class="inv-status-pill {{ $curStatus === 'failed' ? 'active' : '' }}">
            <i class="bi bi-x-circle" style="font-size: 12px;"></i>
            Failed <span class="inv-pill-count">{{ $summary['failed'] }}</span>
        </a>
        <a href="{{ route('tenant.invoices.index', array_merge(request()->except('page'), ['status' => 'pending_group'])) }}" class="inv-status-pill {{ $curStatus === 'pending_group' ? 'active' : '' }}">
            <i class="bi bi-clock" style="font-size: 12px;"></i>
            Pending / Draft <span class="inv-pill-count">{{ $summary['pending'] }}</span>
        </a>
        <a href="{{ route('tenant.invoices.index', array_merge(request()->except('page'), ['status' => 'submitted'])) }}" class="inv-status-pill {{ $curStatus === 'submitted' ? 'active' : '' }}">
            <i class="bi bi-cloud-check" style="font-size: 12px;"></i>
            Submitted
        </a>
        <a href="{{ route('tenant.invoices.index', array_merge(request()->except('page'), ['status' => 'cancelled'])) }}" class="inv-status-pill {{ $curStatus === 'cancelled' ? 'active' : '' }}">
            <i class="bi bi-slash-circle" style="font-size: 12px;"></i>
            Cancelled <span class="inv-pill-count">{{ $summary['cancelled'] }}</span>
        </a>
    </div>

    <!-- Search, Filters & Sorting Bar -->
    <div class="inv-filter-toolbar">
        <form method="GET" action="{{ route('tenant.invoices.index') }}" class="row g-2 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label class="inv-filter-label d-block">Search</label>
                <div class="inv-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Invoice ID, #, FBR #, Buyer, NTN..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-lg-2 col-md-3">
                <label class="inv-filter-label d-block">Status</label>
                <select name="status" class="form-select form-select-sm inv-filter-control">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Valid / Accepted</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed / Invalid</option>
                    <option value="needs_reconciliation" {{ request('status') === 'needs_reconciliation' ? 'selected' : '' }}>Needs Reconciliation</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-6 col-lg-2 col-md-3">
                <label class="inv-filter-label d-block">Invoice Type</label>
                <select name="invoice_type" class="form-select form-select-sm inv-filter-control">
                    <option value="">All Types</option>
                    <option value="standard" {{ request('invoice_type') === 'standard' ? 'selected' : '' }}>Sale Invoice</option>
                    <option value="credit_note" {{ request('invoice_type') === 'credit_note' ? 'selected' : '' }}>Credit Note</option>
                    <option value="debit_note" {{ request('invoice_type') === 'debit_note' ? 'selected' : '' }}>Debit Note</option>
                </select>
            </div>
            <div class="col-6 col-lg-2 col-md-6">
                <label class="inv-filter-label d-block">Environment</label>
                <select name="environment" class="form-select form-select-sm inv-filter-control">
                    <option value="">All Environments</option>
                    <option value="sandbox" {{ request('environment') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                    <option value="production" {{ request('environment') === 'production' ? 'selected' : '' }}>Production</option>
                </select>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <label class="inv-filter-label d-block">Sort By</label>
                <select name="sort" class="form-select form-select-sm inv-filter-control">
                    <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    <option value="highest_amount" {{ request('sort') === 'highest_amount' ? 'selected' : '' }}>Highest Amount</option>
                    <option value="lowest_amount" {{ request('sort') === 'lowest_amount' ? 'selected' : '' }}>Lowest Amount</option>
                </select>
            </div>

            <div class="col-6 col-lg-2 col-md-4">
                <label class="inv-filter-label d-block">From Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm inv-filter-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-6 col-lg-2 col-md-4">
                <label class="inv-filter-label d-block">To Date</label>
                <input type="date" name="date_to" class="form-control form-control-sm inv-filter-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-6 col-lg-2 col-md-4">
                <label class="inv-filter-label d-block">Per Page</label>
                <select name="per_page" class="form-select form-select-sm inv-filter-control">
                    @foreach ([15, 25, 50, 100] as $pp)
                        <option value="{{ $pp }}" {{ (int) request('per_page', 15) === $pp ? 'selected' : '' }}>{{ $pp }} rows</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-6 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-sm btn-ig-primary px-4 py-1" style="border-radius: 9px; height: 32px;">
                    <i class="bi bi-funnel me-1"></i> Apply Filters
                </button>
                <a href="{{ route('tenant.invoices.index') }}" class="btn btn-sm btn-ig-outline px-3 py-1 d-inline-flex align-items-center" style="border-radius: 9px; height: 32px;">Reset</a>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="table-responsive">
        <table class="table inv-table mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Invoice ID</th>
                    <th>Invoice # &amp; FBR Ref</th>
                    <th>Date</th>
                    <th>Buyer / NTN</th>
                    <th>Type &amp; Env</th>
                    <th class="text-end">Total Amount</th>
                    <th class="text-end">Sales Tax</th>
                    <th>FBR Status</th>
                    <th>Invoice Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $inv)
                    <tr>
                        <td class="ps-4">
                            <span class="inv-id-pill">INV-{{ str_pad((string) $inv->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('tenant.invoices.show', $inv->uuid) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $inv->invoice_number }}
                            </a>
                            @if ($inv->fbr_invoice_number)
                                <div class="text-primary font-monospace mt-1" style="font-size: 11px;">
                                    <i class="bi bi-patch-check-fill me-1" style="font-size: 10px;"></i>{{ $inv->fbr_invoice_number }}
                                </div>
                            @endif
                        </td>
                        <td class="text-muted text-nowrap small">
                            <i class="bi bi-calendar3 me-1 text-secondary opacity-75"></i>{{ $inv->invoice_date->format('d M Y') }}
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $inv->buyer_name ?: ($inv->customer->name ?? 'N/A') }}</div>
                            <div class="text-muted font-monospace" style="font-size: 11px;">{{ $inv->buyer_ntn_cnic ?: ($inv->customer->ntn_cnic ?? '—') }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border fw-semibold" style="font-size: 11px; border-radius: 6px;">
                                {{ $inv->invoice_type === 'credit_note' ? 'Credit Note' : ($inv->invoice_type === 'debit_note' ? 'Debit Note' : 'Sale Invoice') }}
                            </span>
                            <div class="mt-1">
                                @if ($inv->environment === 'production')
                                    <span class="text-success fw-bold" style="font-size: 10px; letter-spacing: 0.04em;">● PRODUCTION</span>
                                @else
                                    <span class="fw-bold" style="font-size: 10px; letter-spacing: 0.04em; color: #B45309;">● SANDBOX</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-end fw-bold text-dark text-nowrap" style="font-size: 13.5px;">
                            PKR {{ number_format($inv->total_invoice_value, 2) }}
                        </td>
                        <td class="text-end text-success fw-semibold text-nowrap small">
                            PKR {{ number_format($inv->sales_tax_amount, 2) }}
                        </td>
                        <td>
                            @if ($inv->status === 'accepted')
                                <span class="inv-badge inv-badge-success"><i class="bi bi-check-circle-fill" style="font-size: 10px;"></i> Valid</span>
                            @elseif ($inv->status === 'failed')
                                <span class="inv-badge inv-badge-danger"><i class="bi bi-x-circle-fill" style="font-size: 10px;"></i> Invalid</span>
                            @elseif ($inv->status === 'submitted')
                                <span class="inv-badge inv-badge-info"><i class="bi bi-cloud-check-fill" style="font-size: 10px;"></i> Submitted</span>
                            @elseif ($inv->status === 'needs_reconciliation')
                                <span class="inv-badge inv-badge-warning"><i class="bi bi-clock-fill" style="font-size: 10px;"></i> Pending</span>
                            @elseif ($inv->status === 'cancelled')
                                <span class="inv-badge inv-badge-muted">Cancelled</span>
                            @else
                                <span class="inv-badge inv-badge-muted">Not Submitted</span>
                            @endif
                        </td>
                        <td>
                            @if ($inv->status === 'accepted')
                                <span class="inv-badge inv-badge-success">Accepted</span>
                            @elseif ($inv->status === 'failed')
                                <span class="inv-badge inv-badge-danger">Failed</span>
                            @elseif ($inv->status === 'cancelled')
                                <span class="inv-badge inv-badge-muted">Cancelled</span>
                            @elseif ($inv->status === 'needs_reconciliation')
                                <span class="inv-badge inv-badge-warning">Reconcile</span>
                            @else
                                <span class="inv-badge inv-badge-info">{{ ucfirst($inv->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-4 text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a href="{{ route('tenant.invoices.show', $inv->uuid) }}" class="inv-action-btn view" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if (!$inv->isAccepted() && $inv->status !== 'cancelled')
                                    <a href="{{ route('tenant.invoices.edit', $inv->uuid) }}" class="inv-action-btn edit" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                <a href="{{ route('tenant.invoices.print', $inv->uuid) }}" target="_blank" class="inv-action-btn print" title="Print / PDF">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @if (!$inv->isAccepted() && $inv->status !== 'cancelled')
                                    <form method="POST" action="{{ route('tenant.invoices.cancel', $inv->uuid) }}" class="d-inline" onsubmit="return confirm('Cancel invoice {{ $inv->invoice_number }}?');">
                                        @csrf
                                        <button type="submit" class="inv-action-btn cancel" title="Cancel Invoice">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div class="mx-auto mb-3 rounded-4 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); border: 1px solid #BFDBFE;">
                                <i class="bi bi-receipt-cutoff fs-2 text-primary"></i>
                            </div>
                            <h5 class="fw-bold text-dark fs-5 mb-1">No invoices found</h5>
                            <p class="text-muted small mx-auto mb-3" style="max-width: 390px;">
                                Create a new invoice or clear your active filters to see results.
                            </p>
                            @if (empty($invoiceQuota['reached']))
                                <a href="{{ route('tenant.invoices.create') }}" class="btn btn-ig-primary px-4 py-2" style="border-radius: 10px;">
                                    <i class="bi bi-file-earmark-plus me-1"></i> Create Your First Invoice
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer & Pagination -->
    @if ($invoices->total() > 0)
        <div class="d-flex flex-wrap justify-content-between align-items-center px-4 py-3 bg-light border-top small text-muted">
            <div>
                Showing <strong>{{ $invoices->firstItem() }}</strong> to <strong>{{ $invoices->lastItem() }}</strong> of <strong>{{ $invoices->total() }}</strong> {{ $invoices->total() === 1 ? 'invoice' : 'invoices' }}
            </div>
            @if ($invoices->hasPages())
                <div>
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
