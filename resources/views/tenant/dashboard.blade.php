@extends('layouts.tenant')

@section('title', 'Dashboard')

@section('content')
    <!-- Dashboard Header Row (Screenshot 4) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="font-display fw-bold text-dark fs-3 mb-1">Dashboard</h1>
            <div class="text-muted small">
                Today {{ $metrics['pkt_today_string'] ?? '13 Nov 2026 · 13:00 PKT' }} &middot; invoices can be dated {{ $metrics['pkt_date_range_string'] ?? '01 Nov 2026 – 30 Nov 2026' }}
            </div>
        </div>
        <div>
            <a href="{{ route('tenant.invoices.create') }}" class="btn btn-ig-primary px-3 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-plus"></i>
                <span>Create invoice</span>
            </a>
        </div>
    </div>

    <!-- 8 Metric KPI Tiles (2 Rows x 4 Columns) (Screenshot 4) -->
    <div class="row g-3 mb-4">
        <!-- Row 1 -->
        <div class="col-md-3">
            <div class="ig-kpi-card">
                <div class="kpi-label">TOTAL INVOICES (PRODUCTION)</div>
                <div>
                    <div class="kpi-value">{{ $metrics['total_production_invoices'] ?? 0 }}</div>
                    <div class="kpi-subtext">{{ $metrics['sandbox_invoices_count'] ?? 0 }} sandbox test invoices kept separately</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="ig-kpi-card">
                <div class="kpi-label">THIS MONTH</div>
                <div>
                    <div class="kpi-value">{{ $metrics['this_month_invoices'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="ig-kpi-card">
                <div class="kpi-label">ACCEPTED BY FBR</div>
                <div>
                    <div class="kpi-value text-green">{{ $metrics['accepted_by_fbr'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="ig-kpi-card">
                <div class="kpi-label">FAILED SUBMISSIONS</div>
                <div>
                    <div class="kpi-value">{{ $metrics['failed_submissions'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <!-- Row 2 -->
        <div class="col-md-3">
            <div class="ig-kpi-card">
                <div class="kpi-label">DRAFTS</div>
                <div>
                    <div class="kpi-value">{{ $metrics['drafts'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="ig-kpi-card">
                <div class="kpi-label">PENDING SUBMISSION</div>
                <div>
                    <div class="kpi-value">{{ $metrics['pending_submission'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="ig-kpi-card">
                <div class="kpi-label">NEED RECONCILIATION</div>
                <div>
                    <div class="kpi-value">{{ $metrics['need_reconciliation'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="ig-kpi-card">
                <div class="kpi-label">SCENARIOS PASSED</div>
                <div>
                    <div class="kpi-value">{{ $metrics['scenarios_passed_count'] ?? 0 }}/{{ $metrics['scenarios_total_count'] ?? 3 }}</div>
                    <div class="kpi-subtext">Remaining: {{ implode(', ', $metrics['scenarios_remaining'] ?? ['SN003', 'SN004', 'SN011']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Row: Production Sales Breakdown & FBR Integration Status (Screenshot 4) -->
    <div class="row g-3 mb-4">
        <!-- Production Sales Card (~65% width) -->
        <div class="col-lg-8">
            <div class="ig-content-card h-100">
                <div class="card-title">Production sales (accepted invoices)</div>

                <div class="row gx-5">
                    <!-- Column 1: This Month -->
                    <div class="col-md-6 border-end">
                        <div class="text-uppercase fw-bold text-muted small mb-3" style="font-size: 11.5px; letter-spacing: 0.05em;">This Month</div>
                        <div class="sales-row">
                            <span class="sales-label">Sales value (excl. ST)</span>
                            <span class="sales-val">Rs {{ number_format($metrics['sales_this_month']['sales_value_excl_tax'] ?? 0, 2) }}</span>
                        </div>
                        <div class="sales-row">
                            <span class="sales-label">Sales tax</span>
                            <span class="sales-val">Rs {{ number_format($metrics['sales_this_month']['sales_tax'] ?? 0, 2) }}</span>
                        </div>
                        <div class="sales-row">
                            <span class="sales-label">Further tax</span>
                            <span class="sales-val">Rs {{ number_format($metrics['sales_this_month']['further_tax'] ?? 0, 2) }}</span>
                        </div>
                        <div class="sales-row">
                            <span class="sales-label">ST withheld</span>
                            <span class="sales-val">Rs {{ number_format($metrics['sales_this_month']['st_withheld'] ?? 0, 2) }}</span>
                        </div>
                        <div class="sales-row">
                            <span class="sales-label">Gross invoice value</span>
                            <span class="sales-val text-primary">Rs {{ number_format($metrics['sales_this_month']['gross_value'] ?? 0, 2) }}</span>
                        </div>
                    </div>

                    <!-- Column 2: All Time -->
                    <div class="col-md-6">
                        <div class="text-uppercase fw-bold text-muted small mb-3" style="font-size: 11.5px; letter-spacing: 0.05em;">All Time</div>
                        <div class="sales-row">
                            <span class="sales-label">Sales value (excl. ST)</span>
                            <span class="sales-val">Rs {{ number_format($metrics['sales_all_time']['sales_value_excl_tax'] ?? 0, 2) }}</span>
                        </div>
                        <div class="sales-row">
                            <span class="sales-label">Sales tax</span>
                            <span class="sales-val">Rs {{ number_format($metrics['sales_all_time']['sales_tax'] ?? 0, 2) }}</span>
                        </div>
                        <div class="sales-row">
                            <span class="sales-label">Further tax</span>
                            <span class="sales-val">Rs {{ number_format($metrics['sales_all_time']['further_tax'] ?? 0, 2) }}</span>
                        </div>
                        <div class="sales-row">
                            <span class="sales-label">ST withheld</span>
                            <span class="sales-val">Rs {{ number_format($metrics['sales_all_time']['st_withheld'] ?? 0, 2) }}</span>
                        </div>
                        <div class="sales-row">
                            <span class="sales-label">Gross invoice value</span>
                            <span class="sales-val text-primary">Rs {{ number_format($metrics['sales_all_time']['gross_value'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-2 border-top text-muted small" style="font-size: 12px;">
                    Sandbox test values are excluded. <a href="{{ route('tenant.reports') }}" class="text-decoration-underline text-secondary">Daily, monthly, quarterly and yearly reports</a>
                </div>
            </div>
        </div>

        <!-- FBR Integration Status Card (~35% width) -->
        <div class="col-lg-4">
            <div class="ig-content-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="card-title">FBR integration</div>

                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-secondary small">Sandbox token</span>
                        @if ($metrics['sandbox_token_set'] ?? false)
                            <span class="badge bg-success bg-opacity-10 text-success fw-medium">Configured</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal">Not set</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-secondary small">Production token</span>
                        @if ($metrics['production_token_set'] ?? false)
                            <span class="badge bg-success bg-opacity-10 text-success fw-medium">Configured</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal">Not set</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-2 mb-3">
                        <span class="text-secondary small">Production submission</span>
                        @if ($metrics['production_submission_enabled'] ?? false)
                            <span class="badge bg-success bg-opacity-10 text-success fw-medium">Enabled</span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning fw-medium">Disabled</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3 pt-3 border-top">
                    <a href="{{ route('tenant.fbr-integration') }}" class="btn btn-ig-outline flex-fill text-center small py-2">
                        Manage
                    </a>
                    <a href="{{ route('tenant.sandbox-scenarios') }}" class="btn btn-ig-outline flex-fill text-center small py-2 d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-flask"></i> Scenarios
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Recent Invoices & Recent FBR Activity (Screenshot 4) -->
    <div class="row g-3">
        <!-- Recent Invoices Table -->
        <div class="col-lg-6">
            <div class="ig-content-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="card-title mb-0">Recent invoices</div>
                    <a href="{{ route('tenant.invoices.index') }}" class="text-decoration-none small text-secondary">View all</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-sm mb-0">
                        <thead>
                            <tr class="text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.05em; border-top: 1px solid #F1F5F9;">
                                <th class="py-2">Invoice</th>
                                <th class="py-2">Buyer</th>
                                <th class="py-2">Status</th>
                                <th class="py-2 text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentInvoices ?? [] as $inv)
                                <tr style="font-size: 13px;">
                                    <td class="py-2 fw-medium">
                                        <a href="{{ route('tenant.invoices.show', $inv->uuid) }}" class="text-decoration-none text-dark">
                                            {{ $inv->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="py-2 text-muted">{{ $inv->customer->name ?? 'N/A' }}</td>
                                    <td class="py-2">
                                        @if ($inv->status === 'accepted')
                                            <span class="badge bg-success bg-opacity-10 text-success">Accepted</span>
                                        @elseif ($inv->status === 'failed')
                                            <span class="badge bg-danger bg-opacity-10 text-danger">Failed</span>
                                        @elseif ($inv->status === 'needs_reconciliation')
                                            <span class="badge bg-warning bg-opacity-10 text-warning">Reconcile</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ ucfirst($inv->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-end fw-semibold">Rs {{ number_format($inv->total_invoice_value, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted small">
                                        No invoices yet.
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
            <div class="ig-content-card">
                <div class="card-title mb-3">Recent FBR activity</div>

                <div class="table-responsive">
                    <table class="table align-middle table-sm mb-0">
                        <thead>
                            <tr class="text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.05em; border-top: 1px solid #F1F5F9;">
                                <th class="py-2">When</th>
                                <th class="py-2">Call</th>
                                <th class="py-2 text-end">Outcome</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentFbrActivity ?? [] as $act)
                                <tr style="font-size: 13px;">
                                    <td class="py-2 text-muted">{{ $act->executed_at->diffForHumans() }}</td>
                                    <td class="py-2 fw-medium text-dark">{{ $act->endpoint }}</td>
                                    <td class="py-2 text-end">
                                        @if ($act->outcome === 'success')
                                            <span class="badge bg-success bg-opacity-10 text-success">Success</span>
                                        @elseif ($act->outcome === 'rejected')
                                            <span class="badge bg-danger bg-opacity-10 text-danger">Rejected</span>
                                        @elseif ($act->outcome === 'network_error')
                                            <span class="badge bg-warning bg-opacity-10 text-warning">Timeout</span>
                                        @else
                                            <span class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($act->outcome) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted small">
                                        No FBR calls yet.
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
