@extends('layouts.tenant')

@section('title', 'HS / PCT Codes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">HS / PCT Codes Master Catalog</h1>
        <div class="text-muted small">Pakistan Customs &amp; PRAL Harmonized System Codes for Sales Tax Invoicing</div>
    </div>
</div>

<div class="ig-content-card mb-4 p-3">
    <form method="GET" action="{{ route('tenant.hscodes.index') }}" class="row g-2">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by PCT code or description..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-ig-primary w-100">Search</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('tenant.hscodes.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
        </div>
    </form>
</div>

<div class="ig-content-card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted text-uppercase" style="font-size: 11px;">
                <tr>
                    <th class="py-3 px-4">PCT Code (8 Digits)</th>
                    <th class="py-3">Commodity Description</th>
                    <th class="py-3 text-end">Default Sales Tax</th>
                    <th class="py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($hsCodes as $code)
                    <tr style="font-size: 13.5px;">
                        <td class="py-3 px-4 fw-bold text-primary font-monospace">{{ $code->pct_code }}</td>
                        <td class="py-3 text-dark">{{ $code->description }}</td>
                        <td class="py-3 text-end fw-semibold">{{ number_format($code->default_tax_rate, 1) }}%</td>
                        <td class="py-3 text-center">
                            <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            No matching HS codes found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($hsCodes->hasPages())
        <div class="p-3 border-top">
            {{ $hsCodes->links() }}
        </div>
    @endif
</div>
@endsection
