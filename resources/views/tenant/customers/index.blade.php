@extends('layouts.tenant')

@section('title', 'Customers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">Customers</h1>
        <div class="text-muted small">Manage buyers and verify their FBR registration status</div>
    </div>
    <a href="{{ route('tenant.customers.create') }}" class="btn btn-ig-primary d-flex align-items-center gap-2">
        <i class="bi bi-person-plus"></i>
        <span>Add Customer</span>
    </a>
</div>

<div class="ig-content-card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted text-uppercase" style="font-size: 11px;">
                <tr>
                    <th class="py-3 px-4">Customer Name</th>
                    <th class="py-3">NTN / CNIC</th>
                    <th class="py-3">Type</th>
                    <th class="py-3">FBR Status</th>
                    <th class="py-3">Province</th>
                    <th class="py-3">Contact</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $c)
                    <tr style="font-size: 13.5px;">
                        <td class="py-3 px-4 fw-semibold text-dark">{{ $c->name }}</td>
                        <td class="py-3"><code>{{ $c->ntn_cnic }}</code></td>
                        <td class="py-3">
                            @if ($c->isRegistered())
                                <span class="badge bg-success bg-opacity-10 text-success">Registered</span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning">Unregistered</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-shield-check me-1"></i> FBR Verified
                            </span>
                        </td>
                        <td class="py-3 text-muted">{{ $c->province ?? 'N/A' }}</td>
                        <td class="py-3 text-muted">{{ $c->phone ?? $c->email ?? '&mdash;' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            No customers yet. Click <strong>Add Customer</strong> to create one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($customers->hasPages())
        <div class="p-3 border-top">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection
