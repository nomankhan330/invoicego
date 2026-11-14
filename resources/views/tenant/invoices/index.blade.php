@extends('layouts.tenant')

@section('title', 'Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">Invoices</h1>
        <div class="text-muted small">Manage, fiscalize, and print standard sales tax invoices</div>
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

<!-- Filters Bar -->
<div class="ig-content-card mb-4 p-3">
    <form method="GET" action="{{ route('tenant.invoices.index') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by Invoice #, FBR #, or Customer" value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Drafts</option>
                <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted by FBR</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed Submissions</option>
                <option value="needs_reconciliation" {{ request('status') === 'needs_reconciliation' ? 'selected' : '' }}>Needs Reconciliation</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="environment" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Environments</option>
                <option value="sandbox" {{ request('environment') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                <option value="production" {{ request('environment') === 'production' ? 'selected' : '' }}>Production</option>
            </select>
        </div>
        <div class="col-md-2 text-end">
            <a href="{{ route('tenant.invoices.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
        </div>
    </form>
</div>

<!-- Invoices Table -->
<div class="ig-content-card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.05em;">
                    <th class="py-3 px-4">Invoice #</th>
                    <th class="py-3">Date</th>
                    <th class="py-3">Buyer / Customer</th>
                    <th class="py-3">Env</th>
                    <th class="py-3">Status</th>
                    <th class="py-3">FBR Invoice #</th>
                    <th class="py-3 text-end">Total Amount</th>
                    <th class="py-3 text-end px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $inv)
                    <tr style="font-size: 13.5px;">
                        <td class="py-3 px-4 fw-semibold">
                            <a href="{{ route('tenant.invoices.show', $inv->uuid) }}" class="text-decoration-none text-dark">
                                {{ $inv->invoice_number }}
                            </a>
                        </td>
                        <td class="py-3 text-muted">{{ $inv->invoice_date->format('d M Y') }}</td>
                        <td class="py-3">
                            <div class="fw-medium text-dark">{{ $inv->customer->name ?? 'N/A' }}</div>
                            <div class="text-muted small" style="font-size: 11px;">{{ $inv->customer->ntn_cnic ?? '' }}</div>
                        </td>
                        <td class="py-3">
                            @if ($inv->environment === 'sandbox')
                                <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size: 10.5px;">Sandbox</span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 10.5px;">Production</span>
                            @endif
                        </td>
                        <td class="py-3">
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
                        <td class="py-3">
                            @if ($inv->fbr_invoice_number)
                                <code class="text-primary small fw-bold">{{ $inv->fbr_invoice_number }}</code>
                            @else
                                <span class="text-muted">&mdash;</span>
                            @endif
                        </td>
                        <td class="py-3 text-end fw-bold">
                            Rs {{ number_format($inv->total_invoice_value, 2) }}
                        </td>
                        <td class="py-3 text-end px-4">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('tenant.invoices.show', $inv->uuid) }}" class="btn btn-outline-secondary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if (!$inv->isAccepted())
                                    <a href="{{ route('tenant.invoices.edit', $inv->uuid) }}" class="btn btn-outline-secondary" title="Edit Invoice">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                @if ($inv->isAccepted())
                                    <a href="{{ route('tenant.invoices.print', $inv->uuid) }}" target="_blank" class="btn btn-outline-primary" title="Print Official A4">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                @else
                                    <form method="POST" action="{{ route('tenant.invoices.submit', $inv->uuid) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Submit to FBR">
                                            <i class="bi bi-send"></i>
                                        </button>
                                    </form>
                                @endif
                                @if ($inv->status === 'draft')
                                    <form method="POST" action="{{ route('tenant.invoices.destroy', $inv->uuid) }}" class="d-inline" onsubmit="return confirm('Delete draft invoice {{ $inv->invoice_number }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete Draft">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                                @if ($inv->status === 'needs_reconciliation')
                                    <form method="POST" action="{{ route('tenant.invoices.reconcile', $inv->uuid) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning" title="Reconcile with FBR">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            No invoices found. Click <strong>Create invoice</strong> to issue your first invoice.
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

