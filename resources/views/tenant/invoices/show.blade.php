@extends('layouts.tenant')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h1 class="font-display fw-bold text-dark fs-3 mb-0">{{ $invoice->invoice_number }}</h1>
            @if ($invoice->status === 'accepted')
                <span class="badge bg-success bg-opacity-10 text-success fs-6">Accepted by FBR</span>
            @elseif ($invoice->status === 'failed')
                <span class="badge bg-danger bg-opacity-10 text-danger fs-6">FBR Submission Failed</span>
            @elseif ($invoice->status === 'needs_reconciliation')
                <span class="badge bg-warning bg-opacity-10 text-warning fs-6">Needs Reconciliation</span>
            @else
                <span class="badge bg-secondary bg-opacity-10 text-secondary fs-6">{{ ucfirst($invoice->status) }}</span>
            @endif

            <span class="badge {{ $invoice->environment === 'production' ? 'badge-production' : 'badge-sandbox' }}">
                {{ ucfirst($invoice->environment) }}
            </span>
        </div>
        <div class="text-muted small">
            Date: {{ $invoice->invoice_date->format('d M Y') }} &middot; Created by {{ $invoice->creator->name ?? 'User' }}
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('tenant.invoices.index') }}" class="btn btn-ig-outline">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        @if ($invoice->isAccepted())
            <a href="{{ route('tenant.invoices.print', $invoice->uuid) }}" target="_blank" class="btn btn-ig-primary d-flex align-items-center gap-1">
                <i class="bi bi-printer"></i> Print Official A4
            </a>
        @else
            <form method="POST" action="{{ route('tenant.invoices.submit', $invoice->uuid) }}">
                @csrf
                <button type="submit" class="btn btn-ig-primary d-flex align-items-center gap-1">
                    <i class="bi bi-send"></i> Submit to FBR
                </button>
            </form>
        @endif
        @if ($invoice->status === 'needs_reconciliation')
            <form method="POST" action="{{ route('tenant.invoices.reconcile', $invoice->uuid) }}">
                @csrf
                <button type="submit" class="btn btn-warning d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-repeat"></i> Reconcile
                </button>
            </form>
        @endif
    </div>
</div>

<!-- FBR Verification Status Banner -->
@if ($invoice->isAccepted())
    <div class="alert alert-success d-flex align-items-center gap-3 p-3 rounded-3 mb-4 border-0 shadow-sm" style="background-color: #ECFDF5; border-left: 4px solid #10B981 !important;">
        <i class="bi bi-check-circle-fill fs-2 text-success"></i>
        <div>
            <div class="fw-bold text-success">FBR PRAL DI API Verification Complete</div>
            <div class="small text-dark">
                Official FBR Invoice Number: <strong class="user-select-all">{{ $invoice->fbr_invoice_number }}</strong> &middot; Fiscalized on: {{ $invoice->fbr_submitted_at?->format('d M Y, H:i:s PKT') }}
            </div>
        </div>
    </div>
@elseif ($invoice->status === 'failed')
    <div class="alert alert-danger p-3 rounded-3 mb-4 border-0 shadow-sm" style="background-color: #FEF2F2; border-left: 4px solid #EF4444 !important;">
        <div class="fw-bold text-danger mb-1"><i class="bi bi-x-circle-fill me-1"></i> FBR Validation / Submission Errors:</div>
        <ul class="mb-0 small ps-3">
            @foreach ($invoice->fbr_validation_errors ?? ['Submission rejected by FBR gateway'] as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Business & Customer Card -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="ig-content-card h-100">
            <h6 class="card-title text-muted text-uppercase mb-3" style="font-size: 11px; letter-spacing: 0.05em;">Seller / Issuer</h6>
            <h5 class="fw-bold text-dark fs-6 mb-1">{{ $business->name }}</h5>
            <div class="small text-secondary mb-1">NTN / CNIC: <strong>{{ $business->ntn_cnic }}</strong> &middot; STRN: {{ $business->strn ?? 'N/A' }}</div>
            <div class="small text-secondary mb-1">Province: {{ $business->province }} &middot; Sector: {{ $business->sector }}</div>
            <div class="small text-muted">{{ $business->address }}</div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="ig-content-card h-100">
            <h6 class="card-title text-muted text-uppercase mb-3" style="font-size: 11px; letter-spacing: 0.05em;">Buyer / Customer</h6>
            <h5 class="fw-bold text-dark fs-6 mb-1">{{ $invoice->customer->name }}</h5>
            <div class="small text-secondary mb-1">
                {{ $invoice->customer->isRegistered() ? 'Registered NTN: ' : 'Unregistered CNIC: ' }}
                <strong>{{ $invoice->customer->ntn_cnic }}</strong>
                @if ($invoice->customer->isRegistered())
                    <span class="badge bg-success bg-opacity-10 text-success ms-1">Registered</span>
                @else
                    <span class="badge bg-warning bg-opacity-10 text-warning ms-1">Unregistered (+4% Further Tax)</span>
                @endif
            </div>
            <div class="small text-secondary mb-1">Province: {{ $invoice->customer->province ?? 'N/A' }}</div>
            <div class="small text-muted">{{ $invoice->customer->address ?? 'Address on file' }}</div>
        </div>
    </div>
</div>

<!-- Line Items -->
<div class="ig-content-card mb-4 p-0">
    <div class="p-3 border-bottom">
        <h5 class="card-title mb-0">Itemized Breakdown</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle small mb-0">
            <thead class="bg-light text-muted text-uppercase" style="font-size: 11px;">
                <tr>
                    <th class="py-2 px-3">#</th>
                    <th class="py-2">Description</th>
                    <th class="py-2">HS / PCT</th>
                    <th class="py-2 text-end">Qty</th>
                    <th class="py-2">UOM</th>
                    <th class="py-2 text-end">Unit Price</th>
                    <th class="py-2 text-end">Tax Rate</th>
                    <th class="py-2 text-end">Sales Tax</th>
                    <th class="py-2 text-end">Further Tax</th>
                    <th class="py-2 text-end px-3">Total (Rs)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $idx => $item)
                    <tr>
                        <td class="py-2 px-3 text-muted">{{ $idx + 1 }}</td>
                        <td class="py-2 fw-medium">{{ $item->product_description }}</td>
                        <td class="py-2 text-muted"><code>{{ $item->hs_code }}</code></td>
                        <td class="py-2 text-end">{{ number_format($item->quantity, 2) }}</td>
                        <td class="py-2">{{ $item->uom }}</td>
                        <td class="py-2 text-end">Rs {{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-2 text-end">{{ number_format($item->tax_rate, 1) }}%</td>
                        <td class="py-2 text-end">Rs {{ number_format($item->sales_tax_amount, 2) }}</td>
                        <td class="py-2 text-end">Rs {{ number_format($item->further_tax_amount, 2) }}</td>
                        <td class="py-2 text-end px-3 fw-bold">Rs {{ number_format($item->total_value_incl_tax, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totals Summary -->
    <div class="p-3 bg-light d-flex justify-content-end">
        <div style="width: 320px;">
            <div class="sales-row">
                <span class="sales-label">Sales Value (excl. ST):</span>
                <span class="sales-val">Rs {{ number_format($invoice->sales_value_excl_tax, 2) }}</span>
            </div>
            <div class="sales-row">
                <span class="sales-label">Sales Tax Amount:</span>
                <span class="sales-val">Rs {{ number_format($invoice->sales_tax_amount, 2) }}</span>
            </div>
            <div class="sales-row">
                <span class="sales-label">Further Tax (4%):</span>
                <span class="sales-val">Rs {{ number_format($invoice->further_tax_amount, 2) }}</span>
            </div>
            @if ($invoice->st_withheld_amount > 0)
                <div class="sales-row">
                    <span class="sales-label">ST Withheld:</span>
                    <span class="sales-val">Rs {{ number_format($invoice->st_withheld_amount, 2) }}</span>
                </div>
            @endif
            <div class="sales-row border-top pt-2">
                <span class="sales-label fs-6 fw-bold">Gross Invoice Value:</span>
                <span class="sales-val fs-5 fw-bold text-primary">Rs {{ number_format($invoice->total_invoice_value, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
