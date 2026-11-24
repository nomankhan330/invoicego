@extends('layouts.tenant')

@section('title', 'Invoices')

@push('styles')
<style>
    .inv-kpi-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 14px 16px;
        height: 100%;
    }
    .inv-kpi-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748B;
        margin-bottom: 6px;
    }
    .inv-kpi-val {
        font-size: 21px;
        font-weight: 700;
        color: #0F172A;
        line-height: 1.15;
    }
    .inv-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
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
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">Invoice Management</h1>
        <div class="text-muted small">Manage, filter, fiscalize, and analyze sales tax invoices for <strong>{{ $business->name }}</strong></div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('tenant.invoices.bulk-upload') }}" class="btn btn-ig-outline d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-spreadsheet"></i>
            <span>Bulk Upload (Excel)</span>
        </a>
        <a href="{{ route('tenant.invoices.create') }}" class="btn btn-ig-primary d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-plus"></i>
            <span>Create invoice</span>
        </a>
    </div>
</div>

<!-- 8 Dynamic Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="inv-kpi-card shadow-sm">
            <div class="inv-kpi-label">Total Invoices</div>
            <div class="inv-kpi-val">{{ number_format($summary['total']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="inv-kpi-card shadow-sm">
            <div class="inv-kpi-label text-primary">FBR Submitted</div>
            <div class="inv-kpi-val text-primary">{{ number_format($summary['fbr_submitted']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="inv-kpi-card shadow-sm">
            <div class="inv-kpi-label text-success">Successful (Valid)</div>
            <div class="inv-kpi-val text-success">{{ number_format($summary['successful']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="inv-kpi-card shadow-sm">
            <div class="inv-kpi-label text-danger">Failed Invoices</div>
            <div class="inv-kpi-val text-danger">{{ number_format($summary['failed']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="inv-kpi-card shadow-sm">
            <div class="inv-kpi-label text-warning" style="color: #B45309 !important;">Pending / Draft</div>
            <div class="inv-kpi-val" style="color: #B45309;">{{ number_format($summary['pending']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="inv-kpi-card shadow-sm">
            <div class="inv-kpi-label">Cancelled</div>
            <div class="inv-kpi-val text-secondary">{{ number_format($summary['cancelled']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="inv-kpi-card shadow-sm">
            <div class="inv-kpi-label text-primary">Total Invoice Amount</div>
            <div class="inv-kpi-val fs-5">PKR {{ number_format($summary['total_amount'], 2) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="inv-kpi-card shadow-sm">
            <div class="inv-kpi-label text-success">Total Sales Tax Collected</div>
            <div class="inv-kpi-val fs-5 text-success">PKR {{ number_format($summary['total_sales_tax'], 2) }}</div>
        </div>
    </div>
</div>

<!-- Daily / Monthly / Yearly Analytics Strip -->
<div class="row g-3 mb-4">
    @foreach ($analytics as $period)
        <div class="col-md-4">
            <div class="inv-kpi-card shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="inv-kpi-label mb-0 text-primary">{{ $period['label'] }}</span>
                    <span class="badge bg-light text-dark border" style="font-size: 11px;">
                        {{ $period['count'] }} inv &middot; <span class="text-success">{{ $period['valid'] }} valid</span> / <span class="text-danger">{{ $period['failed'] }} fail</span>
                    </span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Sales:</span>
                    <strong class="text-dark">PKR {{ number_format($period['sales'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between small mt-1">
                    <span class="text-muted">Sales Tax:</span>
                    <strong class="text-success">PKR {{ number_format($period['tax'], 2) }}</strong>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Quick Status Tabs -->
<div class="d-flex flex-wrap gap-2 mb-3">
    @php $curStatus = request('status', ''); @endphp
    <a href="{{ route('tenant.invoices.index', array_merge(request()->except('status', 'page'))) }}" class="inv-status-pill {{ $curStatus === '' ? 'active' : '' }}">
        All Invoices <span class="badge bg-white bg-opacity-25 text-reset">{{ $summary['total'] }}</span>
    </a>
    <a href="{{ route('tenant.invoices.index', array_merge(request()->except('page'), ['status' => 'accepted'])) }}" class="inv-status-pill {{ $curStatus === 'accepted' ? 'active' : '' }}">
        Successful <span class="badge bg-white bg-opacity-25 text-reset">{{ $summary['successful'] }}</span>
    </a>
    <a href="{{ route('tenant.invoices.index', array_merge(request()->except('page'), ['status' => 'failed'])) }}" class="inv-status-pill {{ $curStatus === 'failed' ? 'active' : '' }}">
        Failed <span class="badge bg-white bg-opacity-25 text-reset">{{ $summary['failed'] }}</span>
    </a>
    <a href="{{ route('tenant.invoices.index', array_merge(request()->except('page'), ['status' => 'pending_group'])) }}" class="inv-status-pill {{ $curStatus === 'pending_group' ? 'active' : '' }}">
        Pending / Draft <span class="badge bg-white bg-opacity-25 text-reset">{{ $summary['pending'] }}</span>
    </a>
    <a href="{{ route('tenant.invoices.index', array_merge(request()->except('page'), ['status' => 'submitted'])) }}" class="inv-status-pill {{ $curStatus === 'submitted' ? 'active' : '' }}">
        Submitted
    </a>
    <a href="{{ route('tenant.invoices.index', array_merge(request()->except('page'), ['status' => 'cancelled'])) }}" class="inv-status-pill {{ $curStatus === 'cancelled' ? 'active' : '' }}">
        Cancelled <span class="badge bg-white bg-opacity-25 text-reset">{{ $summary['cancelled'] }}</span>
    </a>
</div>

<!-- Search, Filters & Sorting Bar -->
<div class="ig-content-card mb-4 p-3">
    <form method="GET" action="{{ route('tenant.invoices.index') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Search</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Invoice ID, #, FBR #, Buyer, NTN..." value="{{ request('search') }}">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small text-muted mb-1">Status</label>
            <select name="status" class="form-select form-select-sm">
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
        <div class="col-6 col-md-2">
            <label class="form-label small text-muted mb-1">Invoice Type</label>
            <select name="invoice_type" class="form-select form-select-sm">
                <option value="">All Types</option>
                <option value="standard" {{ request('invoice_type') === 'standard' ? 'selected' : '' }}>Sale Invoice</option>
                <option value="credit_note" {{ request('invoice_type') === 'credit_note' ? 'selected' : '' }}>Credit Note</option>
                <option value="debit_note" {{ request('invoice_type') === 'debit_note' ? 'selected' : '' }}>Debit Note</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small text-muted mb-1">Environment</label>
            <select name="environment" class="form-select form-select-sm">
                <option value="">All Environments</option>
                <option value="sandbox" {{ request('environment') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                <option value="production" {{ request('environment') === 'production' ? 'selected' : '' }}>Production</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small text-muted mb-1">Sort By</label>
            <select name="sort" class="form-select form-select-sm">
                <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                <option value="highest_amount" {{ request('sort') === 'highest_amount' ? 'selected' : '' }}>Highest Amount</option>
                <option value="lowest_amount" {{ request('sort') === 'lowest_amount' ? 'selected' : '' }}>Lowest Amount</option>
            </select>
        </div>

        <div class="col-6 col-md-2">
            <label class="form-label small text-muted mb-1">From Date</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small text-muted mb-1">To Date</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small text-muted mb-1">Per Page</label>
            <select name="per_page" class="form-select form-select-sm">
                @foreach ([15, 25, 50, 100] as $pp)
                    <option value="{{ $pp }}" {{ (int) request('per_page', 15) === $pp ? 'selected' : '' }}>{{ $pp }} rows</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-6 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-sm btn-ig-primary px-4">
                <i class="bi bi-funnel me-1"></i> Apply Filters
            </button>
            <a href="{{ route('tenant.invoices.index') }}" class="btn btn-sm btn-ig-outline px-3">Reset</a>
        </div>
    </form>
</div>

<!-- Invoices Table -->
<div class="ig-content-card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.05em;">
                    <th class="py-3 ps-4">Invoice ID</th>
                    <th class="py-3">Invoice #</th>
                    <th class="py-3">Date</th>
                    <th class="py-3">Buyer / NTN</th>
                    <th class="py-3">Type</th>
                    <th class="py-3 text-end">Total Amount</th>
                    <th class="py-3 text-end">Sales Tax</th>
                    <th class="py-3">FBR Status</th>
                    <th class="py-3">Invoice Status</th>
                    <th class="py-3 text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $inv)
                    <tr style="font-size: 13px;">
                        <td class="py-3 ps-4 font-monospace text-muted small">
                            INV-{{ str_pad((string) $inv->id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="py-3 fw-semibold">
                            <a href="{{ route('tenant.invoices.show', $inv->uuid) }}" class="text-decoration-none text-dark">
                                {{ $inv->invoice_number }}
                            </a>
                            @if ($inv->fbr_invoice_number)
                                <div class="text-primary font-monospace" style="font-size: 11px;">FBR: {{ $inv->fbr_invoice_number }}</div>
                            @endif
                        </td>
                        <td class="py-3 text-muted text-nowrap">{{ $inv->invoice_date->format('d M Y') }}</td>
                        <td class="py-3">
                            <div class="fw-medium text-dark">{{ $inv->buyer_name ?: ($inv->customer->name ?? 'N/A') }}</div>
                            <div class="text-muted font-monospace" style="font-size: 11px;">{{ $inv->buyer_ntn_cnic ?: ($inv->customer->ntn_cnic ?? '—') }}</div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-dark border" style="font-size: 10.5px;">
                                {{ $inv->invoice_type === 'credit_note' ? 'Credit Note' : ($inv->invoice_type === 'debit_note' ? 'Debit Note' : 'Sale Invoice') }}
                            </span>
                            <div>
                                @if ($inv->environment === 'production')
                                    <span class="text-success fw-semibold" style="font-size: 10px;">PRODUCTION</span>
                                @else
                                    <span class="text-warning fw-semibold" style="font-size: 10px; color: #B45309 !important;">SANDBOX</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 text-end fw-bold text-dark text-nowrap">
                            PKR {{ number_format($inv->total_invoice_value, 2) }}
                        </td>
                        <td class="py-3 text-end text-secondary text-nowrap">
                            PKR {{ number_format($inv->sales_tax_amount, 2) }}
                        </td>
                        <td class="py-3">
                            @if ($inv->status === 'accepted')
                                <span class="badge bg-success bg-opacity-10 text-success">Valid</span>
                            @elseif ($inv->status === 'failed')
                                <span class="badge bg-danger bg-opacity-10 text-danger">Invalid</span>
                            @elseif ($inv->status === 'submitted')
                                <span class="badge bg-info bg-opacity-10 text-info">Submitted</span>
                            @elseif ($inv->status === 'needs_reconciliation')
                                <span class="badge bg-warning bg-opacity-10 text-warning">Pending</span>
                            @elseif ($inv->status === 'cancelled')
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Cancelled</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Not Submitted</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if ($inv->status === 'accepted')
                                <span class="badge bg-success bg-opacity-10 text-success">Accepted</span>
                            @elseif ($inv->status === 'failed')
                                <span class="badge bg-danger bg-opacity-10 text-danger">Failed</span>
                            @elseif ($inv->status === 'cancelled')
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Cancelled</span>
                            @elseif ($inv->status === 'needs_reconciliation')
                                <span class="badge bg-warning bg-opacity-10 text-warning">Reconcile</span>
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ ucfirst($inv->status) }}</span>
                            @endif
                        </td>
                        <td class="py-3 text-end pe-4 text-nowrap">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('tenant.invoices.show', $inv->uuid) }}" class="btn btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if (!$inv->isAccepted() && $inv->status !== 'cancelled')
                                    <a href="{{ route('tenant.invoices.edit', $inv->uuid) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                <a href="{{ route('tenant.invoices.print', $inv->uuid) }}" target="_blank" class="btn btn-outline-dark" title="Print / PDF">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @if (!$inv->isAccepted() && $inv->status !== 'cancelled')
                                    <form method="POST" action="{{ route('tenant.invoices.cancel', $inv->uuid) }}" class="d-inline" onsubmit="return confirm('Cancel invoice {{ $inv->invoice_number }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger" title="Cancel Invoice">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <div class="fw-semibold text-dark mb-1">No invoices found</div>
                            <div class="small">Create a new invoice or clear your active filters to see results.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($invoices->hasPages())
        <div class="p-3 border-top">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
