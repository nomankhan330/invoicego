@extends('layouts.tenant')

@section('title', 'HS / PCT Codes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">HS / PCT Codes</h1>
        <div class="text-muted small">
            Pakistan Customs &amp; PRAL Harmonized System Codes &mdash;
            <strong>{{ number_format($hsCodes->total()) }}</strong> codes available
        </div>
    </div>
    <div>
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2" style="font-size: 12px; border-radius: 8px;">
            <i class="bi bi-info-circle me-1"></i> Read-only master catalog
        </span>
    </div>
</div>

<div class="ig-content-card">
    {{-- Search Bar --}}
    <form method="GET" action="{{ route('tenant.hscodes.index') }}" class="mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-7">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by PCT code or commodity description&hellip;"
                    value="{{ request('search') }}"
                >
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-ig-primary px-4">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </div>
            @if(request('search'))
                <div class="col-auto">
                    <a href="{{ route('tenant.hscodes.index') }}" class="btn btn-ig-outline px-4">
                        Reset
                    </a>
                </div>
            @endif
        </div>
    </form>

    @if ($hsCodes->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-5 my-4">
            <div class="mx-auto mb-3 rounded-4 d-flex align-items-center justify-content-center"
                 style="width: 56px; height: 56px; background-color: #EFF6FF;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#2563EB"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                    <path d="M9 12h6M9 16h4"/>
                </svg>
            </div>
            <h5 class="fw-bold text-dark fs-6 mb-1">No matching HS codes found</h5>
            <p class="text-muted small mx-auto" style="max-width: 360px;">
                @if(request('search'))
                    No codes matched "<strong>{{ request('search') }}</strong>".
                    <a href="{{ route('tenant.hscodes.index') }}" class="text-primary text-decoration-none">Clear search</a> to view all codes.
                @else
                    The HS / PCT codes catalog is empty.
                @endif
            </p>
        </div>
    @else
        {{-- Table --}}
        <div class="table-responsive" style="margin: 0 -24px;">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.05em;">
                    <tr>
                        <th class="py-3 px-4">PCT Code</th>
                        <th class="py-3">Commodity Description</th>
                        <th class="py-3 text-end">Default Sales Tax</th>
                        <th class="py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hsCodes as $code)
                        <tr style="font-size: 13.5px;">
                            <td class="py-3 px-4">
                                <span class="fw-bold text-primary font-monospace" style="letter-spacing: 0.03em;">
                                    {{ $code->pct_code }}
                                </span>
                            </td>
                            <td class="py-3 text-dark"
                                style="max-width: 460px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                title="{{ $code->description }}">
                                {{ $code->description }}
                            </td>
                            <td class="py-3 text-end fw-semibold text-dark">
                                {{ number_format($code->default_tax_rate, 1) }}%
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Active</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($hsCodes->hasPages())
            <div class="pt-3 border-top mt-0 px-0">
                {{ $hsCodes->appends(request()->query())->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
