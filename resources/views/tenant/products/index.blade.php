@extends('layouts.tenant')

@section('title', 'Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">Products &amp; Goods</h1>
        <div class="text-muted small">Maintain your item master catalog with official HS / PCT codes</div>
    </div>
    <a href="{{ route('tenant.products.create') }}" class="btn btn-ig-primary d-flex align-items-center gap-2">
        <i class="bi bi-plus-circle"></i>
        <span>Add Product</span>
    </a>
</div>

<div class="ig-content-card p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted text-uppercase" style="font-size: 11px;">
                <tr>
                    <th class="py-3 px-4">Product Name</th>
                    <th class="py-3">SKU</th>
                    <th class="py-3">HS / PCT Code</th>
                    <th class="py-3">UOM</th>
                    <th class="py-3 text-end">Unit Price</th>
                    <th class="py-3 text-end">Default Tax</th>
                    <th class="py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $p)
                    <tr style="font-size: 13.5px;">
                        <td class="py-3 px-4 fw-semibold text-dark">{{ $p->name }}</td>
                        <td class="py-3 text-muted"><code>{{ $p->sku ?? '&mdash;' }}</code></td>
                        <td class="py-3 text-muted"><code>{{ $p->hsCode?->pct_code ?? '8471.30.00' }}</code></td>
                        <td class="py-3">{{ $p->uom }}</td>
                        <td class="py-3 text-end fw-bold">Rs {{ number_format($p->unit_price, 2) }}</td>
                        <td class="py-3 text-end">{{ number_format($p->default_tax_rate, 1) }}%</td>
                        <td class="py-3 text-center">
                            @if ($p->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            No products configured yet. Click <strong>Add Product</strong> to add items.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($products->hasPages())
        <div class="p-3 border-top">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
