@extends('layouts.tenant')

@section('title', 'Dashboard')

@push('styles')
<style>
    .dash-hero-header {
        background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFF 100%);
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    }
    .dash-hero-icon {
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
    .dash-kpi-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 16px 18px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
    }
    .dash-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        border-color: #CBD5E1;
    }
    .dash-kpi-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }
    .dash-kpi-label {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #64748B;
        line-height: 1.35;
    }
    .dash-kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .dash-kpi-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 26px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.1;
    }
    .dash-kpi-subtext {
        font-size: 11.5px;
        color: #64748B;
        margin-top: 6px;
        line-height: 1.35;
    }
    .dash-panel-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.03);
        height: 100%;
    }
    .dash-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }
    .dash-panel-title-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .dash-panel-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }
    .dash-panel-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 15.5px;
        font-weight: 700;
        color: #0F172A;
        margin: 0;
    }
    .dash-sales-box {
        border-radius: 12px;
        padding: 15px 16px;
        height: 100%;
    }
    .dash-sales-box.month-box {
        background: linear-gradient(180deg, #F8FAFF 0%, #FFFFFF 100%);
        border: 1px solid #DBEAFE;
    }
    .dash-sales-box.alltime-box {
        background: linear-gradient(180deg, #F8FAFC 0%, #FFFFFF 100%);
        border: 1px solid #E2E8F0;
    }
    .dash-sales-box-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #E2E8F0;
    }
    .dash-sales-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        font-size: 13px;
    }
    .dash-sales-row.gross-row {
        margin-top: 8px;
        padding-top: 10px;
        border-top: 1px dashed #CBD5E1;
        font-weight: 700;
    }
    .dash-fbr-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 11px 12px;
        border-radius: 10px;
        background: #F8FAFC;
        border: 1px solid #F1F5F9;
        margin-bottom: 10px;
    }
    .dash-fbr-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .dash-fbr-pill.ok      { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
    .dash-fbr-pill.warn    { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }
    .dash-fbr-pill.neutral { background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; }
    .dash-table-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.03);
        overflow: hidden;
        height: 100%;
    }
    .dash-table-headbar {
        padding: 16px 20px;
        border-bottom: 1px solid #E2E8F0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .dash-mini-table thead th {
        background: #F8FAFC;
        color: #64748B;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 10px 16px;
        border-bottom: 1px solid #E2E8F0;
    }
    .dash-mini-table tbody td {
        padding: 12px 16px;
        font-size: 13px;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }
    .dash-mini-table tbody tr:hover {
        background: #F8FAFF;
    }
    .dash-empty-box {
        padding: 36px 20px;
        text-align: center;
    }
    .dash-empty-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #EFF6FF;
        color: #1E50FF;
        border: 1px solid #DBEAFE;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 10px;
    }
</style>
@endpush

@section('content')
    <!-- Executive Dashboard Header -->
    <div class="dash-hero-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="dash-hero-icon">
                <i class="bi bi-grid-1x2-fill"></i>
            </div>
            <div>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <h1 class="font-display fw-bold text-dark fs-3 mb-0">Dashboard</h1>
                    @if (($business->current_environment ?? 'sandbox') === 'production' && $business->production_submissions_enabled)
                        <span class="badge rounded-pill" style="background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; font-size: 11px; font-weight: 700;">
                            <i class="bi bi-circle-fill me-1" style="font-size: 7px;"></i> Live Production
                        </span>
                    @else
                        <span class="badge rounded-pill" style="background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; font-size: 11px; font-weight: 700;">
                            <i class="bi bi-circle-fill me-1" style="font-size: 7px;"></i> Sandbox Environment
                        </span>
                    @endif
                </div>
                <div class="text-muted small mt-1">
                    <i class="bi bi-clock me-1 text-primary"></i>
                    Today {{ $metrics['pkt_today_string'] ?? '13 Nov 2026 · 13:00 PKT' }} &middot; invoices can be dated {{ $metrics['pkt_date_range_string'] ?? '01 Nov 2026 – 30 Nov 2026' }}
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center flex-wrap gap-2">
            @if (isset($invoiceQuota) && empty($invoiceQuota['unlimited']))
                <a href="{{ route('tenant.invoices.index') }}" class="text-decoration-none badge rounded-pill px-3 py-2 fw-semibold {{ $invoiceQuota['reached'] ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' }}" style="font-size: 12.5px;" title="Monthly cycle: {{ $invoiceQuota['period_label'] }} · Auto-renews {{ $invoiceQuota['renews_on'] }}">
                    <i class="bi bi-speedometer2 me-1"></i>
                    {{ $invoiceQuota['used'] }} / {{ $invoiceQuota['limit'] }} Invoices / mo
                </a>
            @endif
            @if (empty($invoiceQuota['reached']))
                <a href="{{ route('tenant.invoices.bulk-upload') }}" class="btn btn-ig-outline px-3 py-2 d-flex align-items-center gap-2" style="border-radius: 10px; font-size: 13.5px;">
                    <i class="bi bi-file-earmark-spreadsheet text-success"></i>
                    <span>Bulk Upload</span>
                </a>
                <a href="{{ route('tenant.invoices.create') }}" class="btn btn-ig-primary px-3 py-2 d-flex align-items-center gap-2 shadow-sm" style="border-radius: 10px;">
                    <i class="bi bi-file-earmark-plus"></i>
                    <span>Create invoice</span>
                </a>
            @else
                <button type="button" class="btn btn-secondary px-3 py-2 d-flex align-items-center gap-2 disabled" style="border-radius: 10px;" disabled>
                    <i class="bi bi-lock-fill"></i>
                    <span>Create invoice</span>
                </button>
            @endif
        </div>
    </div>

    <!-- 8 Executive KPI Tiles (2 Rows x 4 Columns) -->
    @php
        $scenPassed = (int) ($metrics['scenarios_passed_count'] ?? 0);
        $scenTotal  = max(1, (int) ($metrics['scenarios_total_count'] ?? 3));
        $scenPct    = min(100, (int) round(($scenPassed / $scenTotal) * 100));
    @endphp
    <div class="row g-3 mb-4">
        <!-- Row 1 -->
        <div class="col-6 col-xl-3">
            <div class="dash-kpi-card">
                <div class="dash-kpi-top">
                    <div class="dash-kpi-label">TOTAL INVOICES (PRODUCTION)</div>
                    <div class="dash-kpi-icon" style="background: #EFF6FF; color: #1E50FF;">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
                <div>
                    <div class="dash-kpi-value">{{ $metrics['total_production_invoices'] ?? 0 }}</div>
                    <div class="dash-kpi-subtext">{{ $metrics['sandbox_invoices_count'] ?? 0 }} sandbox test invoices kept separately</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="dash-kpi-card">
                <div class="dash-kpi-top">
                    <div class="dash-kpi-label">THIS MONTH</div>
                    <div class="dash-kpi-icon" style="background: #F0F9FF; color: #0284C7;">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                </div>
                <div>
                    <div class="dash-kpi-value" style="color: #0284C7;">{{ $metrics['this_month_invoices'] ?? 0 }}</div>
                    <div class="dash-kpi-subtext">Current billing cycle</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="dash-kpi-card">
                <div class="dash-kpi-top">
                    <div class="dash-kpi-label">ACCEPTED BY FBR</div>
                    <div class="dash-kpi-icon" style="background: #ECFDF5; color: #059669;">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                </div>
                <div>
                    <div class="dash-kpi-value text-success">{{ $metrics['accepted_by_fbr'] ?? 0 }}</div>
                    <div class="dash-kpi-subtext">Verified with IRN &amp; QR</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="dash-kpi-card">
                <div class="dash-kpi-top">
                    <div class="dash-kpi-label">FAILED SUBMISSIONS</div>
                    <div class="dash-kpi-icon" style="background: #FEF2F2; color: #DC2626;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
                <div>
                    <div class="dash-kpi-value {{ ($metrics['failed_submissions'] ?? 0) > 0 ? 'text-danger' : '' }}">{{ $metrics['failed_submissions'] ?? 0 }}</div>
                    <div class="dash-kpi-subtext">Require validation fix</div>
                </div>
            </div>
        </div>

        <!-- Row 2 -->
        <div class="col-6 col-xl-3">
            <div class="dash-kpi-card">
                <div class="dash-kpi-top">
                    <div class="dash-kpi-label">DRAFTS</div>
                    <div class="dash-kpi-icon" style="background: #F1F5F9; color: #475569;">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                </div>
                <div>
                    <div class="dash-kpi-value">{{ $metrics['drafts'] ?? 0 }}</div>
                    <div class="dash-kpi-subtext">Saved locally, not posted</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="dash-kpi-card">
                <div class="dash-kpi-top">
                    <div class="dash-kpi-label">PENDING SUBMISSION</div>
                    <div class="dash-kpi-icon" style="background: #FFFBEB; color: #D97706;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
                <div>
                    <div class="dash-kpi-value" style="color: #B45309;">{{ $metrics['pending_submission'] ?? 0 }}</div>
                    <div class="dash-kpi-subtext">Awaiting FBR transmission</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="dash-kpi-card">
                <div class="dash-kpi-top">
                    <div class="dash-kpi-label">NEED RECONCILIATION</div>
                    <div class="dash-kpi-icon" style="background: #FFF7ED; color: #EA580C;">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                </div>
                <div>
                    <div class="dash-kpi-value" style="color: #C2410C;">{{ $metrics['need_reconciliation'] ?? 0 }}</div>
                    <div class="dash-kpi-subtext">Pending FBR status check</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="dash-kpi-card">
                <div class="dash-kpi-top">
                    <div class="dash-kpi-label">SCENARIOS PASSED</div>
                    <div class="dash-kpi-icon" style="background: #EFF6FF; color: #1E50FF;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
                <div>
                    <div class="dash-kpi-value text-primary">{{ $metrics['scenarios_passed_count'] ?? 0 }}/{{ $metrics['scenarios_total_count'] ?? 3 }}</div>
                    <div class="progress mt-2" style="height: 5px; background: #E2E8F0; border-radius: 999px;">
                        <div class="progress-bar" style="width: {{ max(4, $scenPct) }}%; background: #1E50FF; border-radius: 999px;"></div>
                    </div>
                    <div class="dash-kpi-subtext">Remaining: {{ implode(', ', $metrics['scenarios_remaining'] ?? ['SN003', 'SN004', 'SN011']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Row: Production Sales Breakdown & FBR Integration Status -->
    <div class="row g-3 mb-4">
        <!-- Production Sales Card -->
        <div class="col-lg-8">
            <div class="dash-panel-card d-flex flex-column justify-content-between">
                <div>
                    <div class="dash-panel-header">
                        <div class="dash-panel-title-wrap">
                            <div class="dash-panel-icon" style="background: #EFF6FF; color: #1E50FF;">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <h2 class="dash-panel-title">Production sales (accepted invoices)</h2>
                        </div>
                        <a href="{{ route('tenant.reports') }}" class="btn btn-sm btn-ig-outline px-3" style="border-radius: 8px; font-size: 12px;">
                            View Reports <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="row g-3">
                        <!-- Column 1: This Month -->
                        <div class="col-md-6">
                            <div class="dash-sales-box month-box">
                                <div class="dash-sales-box-head text-primary">
                                    <span><i class="bi bi-calendar-check me-1"></i> This Month</span>
                                    <span class="badge rounded-pill" style="background: #EFF6FF; color: #1E50FF; font-size: 10px;">CURRENT</span>
                                </div>
                                <div class="dash-sales-row">
                                    <span class="text-muted">Sales value (excl. ST)</span>
                                    <span class="fw-semibold text-dark">Rs {{ number_format($metrics['sales_this_month']['sales_value_excl_tax'] ?? 0, 2) }}</span>
                                </div>
                                <div class="dash-sales-row">
                                    <span class="text-muted">Sales tax</span>
                                    <span class="fw-semibold text-dark">Rs {{ number_format($metrics['sales_this_month']['sales_tax'] ?? 0, 2) }}</span>
                                </div>
                                <div class="dash-sales-row">
                                    <span class="text-muted">Further tax</span>
                                    <span class="fw-semibold text-dark">Rs {{ number_format($metrics['sales_this_month']['further_tax'] ?? 0, 2) }}</span>
                                </div>
                                <div class="dash-sales-row">
                                    <span class="text-muted">ST withheld</span>
                                    <span class="fw-semibold text-dark">Rs {{ number_format($metrics['sales_this_month']['st_withheld'] ?? 0, 2) }}</span>
                                </div>
                                <div class="dash-sales-row gross-row">
                                    <span class="text-dark">Gross invoice value</span>
                                    <span class="text-primary fs-6">Rs {{ number_format($metrics['sales_this_month']['gross_value'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Column 2: All Time -->
                        <div class="col-md-6">
                            <div class="dash-sales-box alltime-box">
                                <div class="dash-sales-box-head text-secondary">
                                    <span><i class="bi bi-bar-chart-line me-1"></i> All Time</span>
                                    <span class="badge rounded-pill bg-light text-secondary border" style="font-size: 10px;">CUMULATIVE</span>
                                </div>
                                <div class="dash-sales-row">
                                    <span class="text-muted">Sales value (excl. ST)</span>
                                    <span class="fw-semibold text-dark">Rs {{ number_format($metrics['sales_all_time']['sales_value_excl_tax'] ?? 0, 2) }}</span>
                                </div>
                                <div class="dash-sales-row">
                                    <span class="text-muted">Sales tax</span>
                                    <span class="fw-semibold text-dark">Rs {{ number_format($metrics['sales_all_time']['sales_tax'] ?? 0, 2) }}</span>
                                </div>
                                <div class="dash-sales-row">
                                    <span class="text-muted">Further tax</span>
                                    <span class="fw-semibold text-dark">Rs {{ number_format($metrics['sales_all_time']['further_tax'] ?? 0, 2) }}</span>
                                </div>
                                <div class="dash-sales-row">
                                    <span class="text-muted">ST withheld</span>
                                    <span class="fw-semibold text-dark">Rs {{ number_format($metrics['sales_all_time']['st_withheld'] ?? 0, 2) }}</span>
                                </div>
                                <div class="dash-sales-row gross-row">
                                    <span class="text-dark">Gross invoice value</span>
                                    <span class="text-primary fs-6">Rs {{ number_format($metrics['sales_all_time']['gross_value'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-top text-muted small d-flex flex-wrap justify-content-between align-items-center gap-2" style="font-size: 12px;">
                    <span><i class="bi bi-info-circle me-1 text-primary"></i> Sandbox test values are excluded.</span>
                    <a href="{{ route('tenant.reports') }}" class="text-decoration-none fw-semibold text-primary">Daily, monthly, quarterly and yearly reports &rarr;</a>
                </div>
            </div>
        </div>

        <!-- FBR Integration Status Card -->
        <div class="col-lg-4">
            <div class="dash-panel-card d-flex flex-column justify-content-between">
                <div>
                    <div class="dash-panel-header">
                        <div class="dash-panel-title-wrap">
                            <div class="dash-panel-icon" style="background: #ECFDF5; color: #059669;">
                                <i class="bi bi-broadcast"></i>
                            </div>
                            <h2 class="dash-panel-title">FBR integration</h2>
                        </div>
                        @if ($metrics['production_submission_enabled'] ?? false)
                            <span class="dash-fbr-pill ok">Live</span>
                        @elseif ($metrics['sandbox_token_set'] ?? false)
                            <span class="dash-fbr-pill ok">Sandbox OK</span>
                        @else
                            <span class="dash-fbr-pill warn">Setup Needed</span>
                        @endif
                    </div>

                    <div class="dash-fbr-row">
                        <span class="text-dark fw-medium small">
                            <i class="bi bi-key me-2 text-primary"></i>Sandbox token
                        </span>
                        @if ($metrics['sandbox_token_set'] ?? false)
                            <span class="dash-fbr-pill ok"><i class="bi bi-check-circle-fill" style="font-size: 10px;"></i> Configured</span>
                        @else
                            <span class="dash-fbr-pill neutral">Not set</span>
                        @endif
                    </div>

                    <div class="dash-fbr-row">
                        <span class="text-dark fw-medium small">
                            <i class="bi bi-shield-lock me-2 text-success"></i>Production token
                        </span>
                        @if ($metrics['production_token_set'] ?? false)
                            <span class="dash-fbr-pill ok"><i class="bi bi-check-circle-fill" style="font-size: 10px;"></i> Configured</span>
                        @else
                            <span class="dash-fbr-pill neutral">Not set</span>
                        @endif
                    </div>

                    <div class="dash-fbr-row mb-0">
                        <span class="text-dark fw-medium small">
                            <i class="bi bi-send-check me-2 text-warning"></i>Production submission
                        </span>
                        @if ($metrics['production_submission_enabled'] ?? false)
                            <span class="dash-fbr-pill ok"><i class="bi bi-check-circle-fill" style="font-size: 10px;"></i> Enabled</span>
                        @else
                            <span class="dash-fbr-pill warn">Disabled</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('tenant.fbr-integration') }}" class="btn btn-ig-primary flex-fill text-center small py-2" style="border-radius: 10px;">
                        <i class="bi bi-sliders me-1"></i> Manage
                    </a>
                    <a href="{{ route('tenant.sandbox-scenarios') }}" class="btn btn-ig-outline flex-fill text-center small py-2 d-flex align-items-center justify-content-center gap-1" style="border-radius: 10px;">
                        <i class="bi bi-flask text-primary"></i> Scenarios
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Recent Invoices & Recent FBR Activity -->
    <div class="row g-3">
        <!-- Recent Invoices Table -->
        <div class="col-lg-6">
            <div class="dash-table-card">
                <div class="dash-table-headbar">
                    <div class="dash-panel-title-wrap">
                        <div class="dash-panel-icon" style="background: #EFF6FF; color: #1E50FF;">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <h2 class="dash-panel-title">Recent invoices</h2>
                    </div>
                    <a href="{{ route('tenant.invoices.index') }}" class="text-decoration-none small fw-semibold text-primary">View all &rarr;</a>
                </div>

                <div class="table-responsive">
                    <table class="table dash-mini-table mb-0">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Buyer</th>
                                <th>Status</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentInvoices ?? [] as $inv)
                                <tr>
                                    <td class="fw-bold">
                                        <a href="{{ route('tenant.invoices.show', $inv->uuid) }}" class="text-decoration-none text-dark">
                                            {{ $inv->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="text-muted">{{ $inv->customer->name ?? ($inv->buyer_name ?? 'N/A') }}</td>
                                    <td>
                                        @if ($inv->status === 'accepted')
                                            <span class="dash-fbr-pill ok">Accepted</span>
                                        @elseif ($inv->status === 'failed')
                                            <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle">Failed</span>
                                        @elseif ($inv->status === 'needs_reconciliation')
                                            <span class="dash-fbr-pill warn">Reconcile</span>
                                        @else
                                            <span class="dash-fbr-pill neutral">{{ ucfirst($inv->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark text-nowrap">Rs {{ number_format($inv->total_invoice_value, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="border-0">
                                        <div class="dash-empty-box">
                                            <div class="dash-empty-icon">
                                                <i class="bi bi-receipt"></i>
                                            </div>
                                            <div class="fw-semibold text-dark small mb-1">No invoices yet.</div>
                                            <div class="text-muted" style="font-size: 12px;">Create your first sales invoice to see live activity here.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent FBR Activity Table -->
        <div class="col-lg-6">
            <div class="dash-table-card">
                <div class="dash-table-headbar">
                    <div class="dash-panel-title-wrap">
                        <div class="dash-panel-icon" style="background: #F0F9FF; color: #0284C7;">
                            <i class="bi bi-activity"></i>
                        </div>
                        <h2 class="dash-panel-title">Recent FBR activity</h2>
                    </div>
                    <a href="{{ route('tenant.fbr-integration') }}" class="text-decoration-none small fw-semibold text-primary">FBR Setup &rarr;</a>
                </div>

                <div class="table-responsive">
                    <table class="table dash-mini-table mb-0">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>Call</th>
                                <th class="text-end">Outcome</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentFbrActivity ?? [] as $act)
                                <tr>
                                    <td class="text-muted text-nowrap small">{{ $act->executed_at->diffForHumans() }}</td>
                                    <td class="fw-semibold text-dark font-monospace small">{{ $act->endpoint }}</td>
                                    <td class="text-end">
                                        @if ($act->outcome === 'success')
                                            <span class="dash-fbr-pill ok">Success</span>
                                        @elseif ($act->outcome === 'rejected')
                                            <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                                        @elseif ($act->outcome === 'network_error')
                                            <span class="dash-fbr-pill warn">Timeout</span>
                                        @else
                                            <span class="dash-fbr-pill neutral">{{ ucfirst($act->outcome) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="border-0">
                                        <div class="dash-empty-box">
                                            <div class="dash-empty-icon" style="background: #F0F9FF; color: #0284C7; border-color: #BAE6FD;">
                                                <i class="bi bi-broadcast"></i>
                                            </div>
                                            <div class="fw-semibold text-dark small mb-1">No FBR calls yet.</div>
                                            <div class="text-muted" style="font-size: 12px;">Validate or post an invoice to track real-time PRAL API calls.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
