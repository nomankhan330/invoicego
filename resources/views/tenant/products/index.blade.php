@extends('layouts.tenant')

@section('title', 'Products')

@push('styles')
<style>
    .prod-hero-header {
        background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFF 100%);
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    }
    .prod-hero-icon {
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
    .prod-kpi-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 16px 18px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
    }
    .prod-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        border-color: #CBD5E1;
    }
    .prod-kpi-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748B;
        margin-bottom: 4px;
    }
    .prod-kpi-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 22px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.15;
    }
    .prod-kpi-sub {
        font-size: 11.5px;
        color: #64748B;
        margin-top: 3px;
    }
    .prod-kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        flex-shrink: 0;
    }
    .prod-catalog-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.035);
        overflow: hidden;
    }
    .prod-toolbar {
        padding: 16px 20px;
        background: #FAFBFD;
        border-bottom: 1px solid #E2E8F0;
    }
    .prod-search-wrap {
        position: relative;
    }
    .prod-search-wrap .bi-search {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 14px;
        pointer-events: none;
    }
    .prod-search-wrap input {
        padding-left: 36px;
        border-radius: 10px;
        border-color: #CBD5E1;
        font-size: 13.5px;
    }
    .prod-search-wrap input:focus,
    .prod-filter-select:focus {
        border-color: #1E50FF;
        box-shadow: 0 0 0 3px rgba(30, 80, 255, 0.12);
    }
    .prod-filter-select {
        border-radius: 10px;
        border-color: #CBD5E1;
        font-size: 13px;
    }
    .prod-table thead th {
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
    .prod-table tbody tr {
        transition: background-color 0.12s ease;
    }
    .prod-table tbody tr:hover {
        background-color: #F8FAFF;
    }
    .prod-table tbody td {
        padding: 14px;
        vertical-align: middle;
        border-bottom: 1px solid #F1F5F9;
        font-size: 13.5px;
    }
    .prod-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #EFF6FF;
        color: #1E50FF;
        border: 1px solid #DBEAFE;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .prod-sku-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 7px;
        background: #F1F5F9;
        color: #0F172A;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.02em;
        border: 1px solid #E2E8F0;
    }
    .prod-hs-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 9px;
        border-radius: 7px;
        background: #F8FAFC;
        color: #334155;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #E2E8F0;
    }
    .prod-saletype-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        background: #EFF6FF;
        color: #1D4ED8;
        font-size: 11.5px;
        font-weight: 600;
        border: 1px solid #DBEAFE;
        max-width: 210px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .prod-tax-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
    }
    .prod-tax-pill.zero-tax {
        background: #FEF3C7;
        color: #B45309;
        border-color: #FDE68A;
    }
    .prod-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #E2E8F0;
        background: #FFFFFF;
        color: #475569;
        transition: all 0.15s ease;
        padding: 0;
    }
    .prod-action-btn.view:hover {
        background: #EFF6FF;
        border-color: #BFDBFE;
        color: #1E50FF;
    }
    .prod-action-btn.edit:hover {
        background: #F1F5F9;
        border-color: #CBD5E1;
        color: #0F172A;
    }
    .prod-action-btn.delete:hover {
        background: #FEF2F2;
        border-color: #FECACA;
        color: #DC2626;
    }
    .prod-modal-section-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #1E50FF;
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid #E2E8F0;
    }
    .view-stat-chip {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 10px 12px;
    }
    .view-stat-chip-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748B;
        margin-bottom: 2px;
    }
    .view-stat-chip-val {
        font-size: 14px;
        font-weight: 700;
        color: #0F172A;
    }
</style>
@endpush

@section('content')
<!-- Executive Page Header -->
<div class="prod-hero-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="prod-hero-icon">
            <i class="bi bi-box-seam-fill"></i>
        </div>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="font-display fw-bold text-dark fs-3 mb-0">Products</h1>
                <span class="badge rounded-pill" style="background: #EFF6FF; color: #1E50FF; border: 1px solid #DBEAFE; font-size: 11.5px; font-weight: 700;">
                    {{ number_format($stats['total'] ?? $products->total()) }} Catalog {{ ($stats['total'] ?? 0) === 1 ? 'Item' : 'Items' }}
                </span>
            </div>
            <div class="text-muted small mt-1">Saved products autofill invoice items. Configure HS codes, UOM, sale types, and tax rates once for faster FBR invoicing.</div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('tenant.hscodes.index') }}" class="btn btn-ig-outline d-flex align-items-center gap-2" style="border-radius: 10px; font-size: 13.5px;">
            <i class="bi bi-upc-scan text-primary"></i>
            <span>HS / PCT Codes</span>
        </a>
        <button type="button" class="btn btn-ig-primary d-flex align-items-center gap-2 px-3 py-2 shadow-sm" style="border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-plus-lg"></i>
            <span>Add product</span>
        </button>
    </div>
</div>

<!-- 4 KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="prod-kpi-card">
            <div>
                <div class="prod-kpi-label">Total Products</div>
                <div class="prod-kpi-value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="prod-kpi-sub">Active in catalog</div>
            </div>
            <div class="prod-kpi-icon" style="background: #EFF6FF; color: #1E50FF;">
                <i class="bi bi-boxes"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="prod-kpi-card">
            <div>
                <div class="prod-kpi-label">FBR HS Linked</div>
                <div class="prod-kpi-value text-success">{{ number_format($stats['fbr_ready'] ?? 0) }}</div>
                <div class="prod-kpi-sub">Ready for PRAL sync</div>
            </div>
            <div class="prod-kpi-icon" style="background: #ECFDF5; color: #059669;">
                <i class="bi bi-patch-check-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="prod-kpi-card">
            <div>
                <div class="prod-kpi-label">Avg. Unit Price</div>
                <div class="prod-kpi-value" style="font-size: 19px;">PKR {{ number_format($stats['avg_price'] ?? 0, 2) }}</div>
                <div class="prod-kpi-sub">Excl. sales tax</div>
            </div>
            <div class="prod-kpi-icon" style="background: #F0F9FF; color: #0284C7;">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="prod-kpi-card">
            <div>
                <div class="prod-kpi-label">Avg. GST / SRO Items</div>
                <div class="prod-kpi-value">{{ number_format($stats['avg_tax_rate'] ?? 18, 1) }}%</div>
                <div class="prod-kpi-sub">{{ number_format($stats['sro_or_retail'] ?? 0) }} with SRO / 3rd Sch. MRP</div>
            </div>
            <div class="prod-kpi-icon" style="background: #FFFBEB; color: #D97706;">
                <i class="bi bi-percent"></i>
            </div>
        </div>
    </div>
</div>

<!-- Catalog Table Card -->
<div class="prod-catalog-card">
    <!-- Filter & Search Toolbar -->
    <div class="prod-toolbar">
        <form method="GET" action="{{ route('tenant.products.index') }}">
            <div class="row g-2 align-items-center">
                <div class="col-lg-4 col-md-6">
                    <div class="prod-search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" class="form-control" placeholder="Search SKU, description or HS code" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <select name="sale_type" class="form-select prod-filter-select" onchange="this.form.submit()">
                        <option value="">All Sale Types</option>
                        @foreach ($saleTypes as $val => $label)
                            <option value="{{ $val }}" {{ request('sale_type') === $val ? 'selected' : '' }}>{{ Str::limit($label, 36) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <select name="sort" class="form-select prod-filter-select" onchange="this.form.submit()">
                        <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High &rarr; Low</option>
                        <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low &rarr; High</option>
                        <option value="sku_asc" {{ request('sort') === 'sku_asc' ? 'selected' : '' }}>SKU: A &rarr; Z</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-8 d-flex align-items-center justify-content-lg-end gap-2">
                    <button type="submit" class="btn btn-ig-primary px-3 py-2" style="border-radius: 10px; font-size: 13px;">
                        <i class="bi bi-funnel me-1"></i> Search
                    </button>
                    @if (request()->hasAny(['search', 'sale_type', 'tax_rate']) && (request('search') || request('sale_type') || request()->filled('tax_rate')))
                        <a href="{{ route('tenant.products.index') }}" class="btn btn-ig-outline px-3 py-2" style="border-radius: 10px; font-size: 13px;">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @if ($products->isEmpty())
        <!-- Enhanced Empty State -->
        <div class="text-center py-5 my-4 px-3">
            <div class="mx-auto mb-3 rounded-4 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); border: 1px solid #BFDBFE;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#1E50FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                    <path d="m3.3 7 8.7 5 8.7-5"></path>
                    <path d="M12 22V12"></path>
                </svg>
            </div>
            <h5 class="fw-bold text-dark fs-5 mb-1">No products yet</h5>
            <p class="text-muted small mx-auto mb-3" style="max-width: 400px;">
                Add products with their HS code, UOM and sale type to speed up invoicing.
            </p>
            <button type="button" class="btn btn-ig-primary px-4 py-2" style="border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-lg me-1"></i> Add Your First Product
            </button>
        </div>
    @else
        <!-- Products Table -->
        <div class="table-responsive">
            <table class="table prod-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Product &amp; SKU</th>
                        <th>HS / PCT Code</th>
                        <th>UOM</th>
                        <th>Sale Type</th>
                        <th class="text-end">Price (PKR)</th>
                        <th class="text-center">Tax %</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $p)
                        @php
                            $hsCodeVal = $p->hs_code ?? $p->hsCode?->pct_code ?? 'N/A';
                            $hsDescVal = $p->hsCode?->description;
                            $hasDifferentName = !empty($p->name) && $p->name !== $p->description;
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="prod-avatar">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <div style="min-width: 0;">
                                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                            <span class="prod-sku-pill">{{ $p->sku }}</span>
                                            @if ($hasDifferentName)
                                                <span class="fw-bold text-dark" style="font-size: 13.5px;">{{ $p->name }}</span>
                                            @endif
                                            @if (!empty($p->sro_schedule_no))
                                                <span class="badge rounded-pill" style="background: #FEF3C7; color: #B45309; font-size: 10.5px; font-weight: 700;">SRO</span>
                                            @endif
                                            @if (!empty($p->retail_price) && $p->retail_price > 0)
                                                <span class="badge rounded-pill" style="background: #F0F9FF; color: #0369A1; font-size: 10.5px; font-weight: 700;">3rd Sch.</span>
                                            @endif
                                        </div>
                                        <div class="{{ $hasDifferentName ? 'text-muted small' : 'fw-medium text-dark' }}" style="max-width: 340px;">
                                            {{ $p->description }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="prod-hs-pill" @if ($hsDescVal) title="{{ $hsDescVal }}" @endif>
                                    <i class="bi bi-upc text-primary" style="font-size: 11px;"></i>
                                    {{ $hsCodeVal }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border fw-semibold" style="font-size: 11.5px; padding: 5px 9px; border-radius: 7px;">
                                    {{ $p->uom }}
                                </span>
                            </td>
                            <td>
                                <span class="prod-saletype-pill" title="{{ $p->sale_type }}">{{ $p->sale_type }}</span>
                            </td>
                            <td class="text-end">
                                <div class="fw-bold text-dark" style="font-size: 14px;">Rs {{ number_format($p->unit_price, 2) }}</div>
                                @if (!empty($p->retail_price) && $p->retail_price > 0)
                                    <div class="text-muted" style="font-size: 11px;">MRP: Rs {{ number_format($p->retail_price, 2) }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="prod-tax-pill {{ (float) $p->default_tax_rate == 0.0 ? 'zero-tax' : '' }}">
                                    {{ number_format($p->default_tax_rate, 1) }}%
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" class="prod-action-btn view view-product-btn"
                                        data-sku="{{ $p->sku }}"
                                        data-name="{{ $p->name }}"
                                        data-price="{{ $p->unit_price }}"
                                        data-desc="{{ $p->description }}"
                                        data-hs="{{ $hsCodeVal }}"
                                        data-uom="{{ $p->uom }}"
                                        data-saletype="{{ $p->sale_type }}"
                                        data-tax="{{ $p->default_tax_rate }}"
                                        data-sro="{{ $p->sro_schedule_no }}"
                                        data-serial="{{ $p->sro_item_serial }}"
                                        data-retail="{{ $p->retail_price }}"
                                        title="View Product Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="prod-action-btn edit edit-product-btn"
                                        data-id="{{ $p->id }}"
                                        data-sku="{{ $p->sku }}"
                                        data-price="{{ $p->unit_price }}"
                                        data-desc="{{ $p->description }}"
                                        data-hs="{{ $p->hs_code ?? $p->hsCode?->pct_code }}"
                                        data-uom="{{ $p->uom }}"
                                        data-saletype="{{ $p->sale_type }}"
                                        data-tax="{{ $p->default_tax_rate }}"
                                        data-sro="{{ $p->sro_schedule_no }}"
                                        data-serial="{{ $p->sro_item_serial }}"
                                        data-retail="{{ $p->retail_price }}"
                                        title="Edit Product">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('tenant.products.destroy', $p->id) }}" class="d-inline" onsubmit="return confirm('Delete product {{ $p->sku }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="prod-action-btn delete" title="Delete Product">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Table Footer & Pagination -->
        <div class="d-flex flex-wrap justify-content-between align-items-center px-4 py-3 bg-light border-top small text-muted">
            <div>
                Showing <strong>{{ $products->firstItem() }}</strong> to <strong>{{ $products->lastItem() }}</strong> of <strong>{{ $products->total() }}</strong> {{ $products->total() === 1 ? 'product' : 'products' }}
            </div>
            @if ($products->hasPages())
                <div>
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

<!-- ================= ADD PRODUCT MODAL ================= -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-bottom py-3 px-4" style="background: #F8FAFF;">
                <div class="d-flex align-items-center gap-3">
                    <div class="prod-avatar" style="width: 38px; height: 38px;">
                        <i class="bi bi-plus-circle-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-display fw-bold text-dark fs-5 mb-0" id="addProductModalLabel">Add product</h5>
                        <div class="text-muted" style="font-size: 12px;">Save SKU, FBR HS code, and tax defaults for 1-click invoicing</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('tenant.products.store') }}">
                @csrf
                <div class="modal-body px-4 py-4">
                    <div class="prod-modal-section-label">1. Basic Product Information</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="prod_sku" class="form-label fw-semibold small">Product code (SKU) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="prod_sku" name="sku" value="{{ old('sku') }}" placeholder="e.g. LAP-DELL-5520" required>
                        </div>
                        <div class="col-md-6">
                            <label for="prod_price" class="form-label fw-semibold small">Item sale price (PKR, excl. tax) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="prod_price" name="unit_price" value="{{ old('unit_price') }}" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="prod_desc" class="form-label fw-semibold small">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="prod_desc" name="description" value="{{ old('description') }}" placeholder="Full product name & specification" required>
                    </div>

                    <div class="prod-modal-section-label">2. FBR Tariff &amp; Tax Classification</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="prod_hs" class="form-label fw-semibold small">HS code <span class="text-danger">*</span></label>
                            <select class="form-select" id="prod_hs" name="hs_code" required>
                                <option value="">Select HS / PCT Code (e.g. 0101.2100)</option>
                                @foreach ($hsCodes as $code)
                                    <option value="{{ $code->pct_code }}" {{ old('hs_code') == $code->pct_code ? 'selected' : '' }}>
                                        {{ $code->pct_code }} &mdash; {{ Str::limit($code->description, 35) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="prod_uom" class="form-label fw-semibold small">UOM <span class="text-danger">*</span></label>
                            <select class="form-select" id="prod_uom" name="uom" required>
                                <option value="">Unit of measure</option>
                                @foreach ($uomList as $val => $label)
                                    <option value="{{ $val }}" {{ old('uom', 'NOS') == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="prod_saletype" class="form-label fw-semibold small">Sale type <span class="text-danger">*</span></label>
                            <select class="form-select" id="prod_saletype" name="sale_type" required>
                                <option value="">Choose the sale type</option>
                                @foreach ($saleTypes as $val => $label)
                                    <option value="{{ $val }}" {{ old('sale_type', 'Goods (Standard)') == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="prod_tax" class="form-label fw-semibold small">Default tax rate</label>
                            <select class="form-select" id="prod_tax" name="default_tax_rate">
                                <option value="18.00" selected>18% (Standard GST)</option>
                                <option value="0.00">0% (Zero-rated / Export)</option>
                                <option value="1.00">1% (Reduced Rate)</option>
                                <option value="2.00">2% (Reduced Rate)</option>
                                <option value="5.00">5% (Reduced Rate)</option>
                                <option value="10.00">10% (Reduced Rate)</option>
                                <option value="15.00">15% (Services ICT)</option>
                                <option value="16.00">16% (Services Provincial)</option>
                            </select>
                        </div>
                    </div>

                    <div class="prod-modal-section-label">3. Optional SRO &amp; 3rd Schedule Pricing</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="prod_sro" class="form-label fw-semibold small">SRO / schedule no.</label>
                            <select class="form-select" id="prod_sro" name="sro_schedule_no">
                                <option value="">If an SRO applies</option>
                                @foreach ($sroList as $val => $label)
                                    <option value="{{ $val }}" {{ old('sro_schedule_no') == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="prod_serial" class="form-label fw-semibold small">SRO item S. No.</label>
                            <input type="text" class="form-control" id="prod_serial" name="sro_item_serial" value="{{ old('sro_item_serial') }}" placeholder="SRO item serial">
                        </div>
                    </div>

                    <div>
                        <label for="prod_retail" class="form-label fw-semibold small">Retail price / unit (3rd Schedule)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="prod_retail" name="retail_price" value="{{ old('retail_price') }}" placeholder="Optional printed MRP for 3rd Schedule goods">
                    </div>
                </div>

                <div class="modal-footer border-top bg-light py-3 px-4">
                    <button type="button" class="btn btn-ig-outline px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-ig-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= EDIT PRODUCT MODAL ================= -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-bottom py-3 px-4" style="background: #F8FAFF;">
                <div class="d-flex align-items-center gap-3">
                    <div class="prod-avatar" style="width: 38px; height: 38px;">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-display fw-bold text-dark fs-5 mb-0" id="editProductModalLabel">Edit product</h5>
                        <div class="text-muted" style="font-size: 12px;">Update SKU details, unit pricing, or FBR tax attributes</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="" id="editProductForm">
                @csrf
                @method('PUT')
                <div class="modal-body px-4 py-4">
                    <div class="prod-modal-section-label">1. Basic Product Information</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Product code (SKU) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_prod_sku" name="sku" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Item sale price (PKR, excl. tax) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="edit_prod_price" name="unit_price" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_prod_desc" name="description" required>
                    </div>

                    <div class="prod-modal-section-label">2. FBR Tariff &amp; Tax Classification</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">HS code <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_prod_hs" name="hs_code" required>
                                @foreach ($hsCodes as $code)
                                    <option value="{{ $code->pct_code }}">
                                        {{ $code->pct_code }} &mdash; {{ Str::limit($code->description, 35) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">UOM <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_prod_uom" name="uom" required>
                                @foreach ($uomList as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Sale type <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_prod_saletype" name="sale_type" required>
                                @foreach ($saleTypes as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Default tax rate</label>
                            <select class="form-select" id="edit_prod_tax" name="default_tax_rate">
                                <option value="18.00">18% (Standard GST)</option>
                                <option value="0.00">0% (Zero-rated / Export)</option>
                                <option value="1.00">1% (Reduced Rate)</option>
                                <option value="2.00">2% (Reduced Rate)</option>
                                <option value="5.00">5% (Reduced Rate)</option>
                                <option value="10.00">10% (Reduced Rate)</option>
                                <option value="15.00">15% (Services ICT)</option>
                                <option value="16.00">16% (Services Provincial)</option>
                            </select>
                        </div>
                    </div>

                    <div class="prod-modal-section-label">3. Optional SRO &amp; 3rd Schedule Pricing</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">SRO / schedule no.</label>
                            <select class="form-select" id="edit_prod_sro" name="sro_schedule_no">
                                @foreach ($sroList as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">SRO item S. No.</label>
                            <input type="text" class="form-control" id="edit_prod_serial" name="sro_item_serial">
                        </div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold small">Retail price / unit (3rd Schedule)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="edit_prod_retail" name="retail_price">
                    </div>
                </div>

                <div class="modal-footer border-top bg-light py-3 px-4">
                    <button type="button" class="btn btn-ig-outline px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-ig-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= VIEW PRODUCT MODAL ================= -->
<div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-bottom py-3 px-4" style="background: #F8FAFF;">
                <div class="d-flex align-items-center gap-3">
                    <div class="prod-avatar" style="width: 38px; height: 38px;">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-display fw-bold text-dark fs-5 mb-0" id="viewProductModalLabel">Product Details</h5>
                        <div class="font-monospace small text-primary fw-semibold" id="view_prod_sku"></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="mb-3">
                    <div class="text-muted text-uppercase fw-bold" style="font-size: 10.5px; letter-spacing: 0.05em;">Description</div>
                    <div class="fw-semibold text-dark mt-1" id="view_prod_desc"></div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="view-stat-chip">
                            <div class="view-stat-chip-label">Unit Price (Excl. Tax)</div>
                            <div class="view-stat-chip-val text-primary" id="view_prod_price"></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="view-stat-chip">
                            <div class="view-stat-chip-label">Default Tax Rate</div>
                            <div class="view-stat-chip-val text-success" id="view_prod_tax"></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="view-stat-chip">
                            <div class="view-stat-chip-label">HS / PCT Code</div>
                            <div class="view-stat-chip-val font-monospace" id="view_prod_hs"></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="view-stat-chip">
                            <div class="view-stat-chip-label">Unit of Measure</div>
                            <div class="view-stat-chip-val" id="view_prod_uom"></div>
                        </div>
                    </div>
                </div>

                <table class="table table-sm table-borderless mb-0 small">
                    <tr><th class="text-muted fw-semibold" style="width: 38%;">Sale Type:</th><td class="fw-medium text-dark" id="view_prod_saletype"></td></tr>
                    <tr><th class="text-muted fw-semibold">Retail Price (MRP):</th><td class="fw-medium text-dark" id="view_prod_retail"></td></tr>
                    <tr><th class="text-muted fw-semibold">SRO / Schedule:</th><td class="fw-medium text-dark" id="view_prod_sro"></td></tr>
                    <tr><th class="text-muted fw-semibold">SRO Item Serial:</th><td class="fw-medium text-dark" id="view_prod_serial"></td></tr>
                </table>
            </div>
            <div class="modal-footer border-top bg-light py-3 px-4">
                <button type="button" class="btn btn-ig-outline btn-sm px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const viewButtons = document.querySelectorAll('.view-product-btn');
    const viewModal = new bootstrap.Modal(document.getElementById('viewProductModal'));

    viewButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('view_prod_sku').textContent = this.getAttribute('data-sku') || '';
            document.getElementById('view_prod_desc').textContent = this.getAttribute('data-desc') || '';
            document.getElementById('view_prod_hs').textContent = this.getAttribute('data-hs') || 'N/A';
            document.getElementById('view_prod_uom').textContent = this.getAttribute('data-uom') || 'NOS';
            document.getElementById('view_prod_saletype').textContent = this.getAttribute('data-saletype') || 'Goods';
            document.getElementById('view_prod_price').textContent = 'Rs ' + parseFloat(this.getAttribute('data-price') || 0).toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('view_prod_tax').textContent = parseFloat(this.getAttribute('data-tax') || 18.00).toFixed(2) + '%';
            document.getElementById('view_prod_retail').textContent = this.getAttribute('data-retail') ? 'Rs ' + parseFloat(this.getAttribute('data-retail')).toLocaleString(undefined, {minimumFractionDigits: 2}) : '—';
            document.getElementById('view_prod_sro').textContent = this.getAttribute('data-sro') || '—';
            document.getElementById('view_prod_serial').textContent = this.getAttribute('data-serial') || '—';

            viewModal.show();
        });
    });

    const editButtons = document.querySelectorAll('.edit-product-btn');
    const editForm = document.getElementById('editProductForm');
    const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));

    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            editForm.action = `/products/${id}`;

            document.getElementById('edit_prod_sku').value = this.getAttribute('data-sku') || '';
            document.getElementById('edit_prod_price').value = this.getAttribute('data-price') || '';
            document.getElementById('edit_prod_desc').value = this.getAttribute('data-desc') || '';
            document.getElementById('edit_prod_hs').value = this.getAttribute('data-hs') || '';
            document.getElementById('edit_prod_uom').value = this.getAttribute('data-uom') || 'NOS';
            document.getElementById('edit_prod_saletype').value = this.getAttribute('data-saletype') || 'Goods (Standard)';
            document.getElementById('edit_prod_tax').value = parseFloat(this.getAttribute('data-tax') || 18.00).toFixed(2);
            document.getElementById('edit_prod_sro').value = this.getAttribute('data-sro') || '';
            document.getElementById('edit_prod_serial').value = this.getAttribute('data-serial') || '';
            document.getElementById('edit_prod_retail').value = this.getAttribute('data-retail') || '';

            editModal.show();
        });
    });
});
</script>
@endpush
@endsection
