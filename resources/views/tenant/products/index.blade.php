@extends('layouts.tenant')

@section('title', 'Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">Products</h1>
        <div class="text-muted small">Saved products autofill invoice items.</div>
    </div>
    <button type="button" class="btn btn-ig-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="bi bi-plus-lg"></i>
        <span>Add product</span>
    </button>
</div>

<!-- Search & Content Card (Matching Screenshot 2) -->
<div class="ig-content-card">
    <form method="GET" action="{{ route('tenant.products.index') }}" class="mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search SKU, description or HS code" value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-ig-outline px-4">Search</button>
            </div>
            @if (request('search'))
                <div class="col-auto">
                    <a href="{{ route('tenant.products.index') }}" class="btn btn-link text-secondary text-decoration-none">Clear</a>
                </div>
            @endif
        </div>
    </form>

    @if ($products->isEmpty())
        <!-- Empty State (Matching Screenshot 2) -->
        <div class="text-center py-5 my-4">
            <div class="mx-auto mb-3 rounded-4 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #F5F3FF;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                    <path d="m3.3 7 8.7 5 8.7-5"></path>
                    <path d="M12 22V12"></path>
                </svg>
            </div>
            <h5 class="fw-bold text-dark fs-6 mb-1">No products yet</h5>
            <p class="text-muted small mx-auto" style="max-width: 380px;">
                Add products with their HS code, UOM and sale type to speed up invoicing.
            </p>
        </div>
    @else
        <!-- Products Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.05em;">
                    <tr>
                        <th class="py-3 px-3">Product Code (SKU)</th>
                        <th class="py-3">Description</th>
                        <th class="py-3">HS Code</th>
                        <th class="py-3">UOM</th>
                        <th class="py-3">Sale Type</th>
                        <th class="py-3 text-end">Price (PKR)</th>
                        <th class="py-3 text-end">Tax %</th>
                        <th class="py-3 text-end px-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $p)
                        <tr style="font-size: 13.5px;">
                            <td class="py-3 px-3 fw-bold text-dark font-monospace">{{ $p->sku }}</td>
                            <td class="py-3 text-dark fw-medium">{{ $p->description }}</td>
                            <td class="py-3 text-secondary font-monospace">{{ $p->hs_code ?? $p->hsCode?->pct_code ?? 'N/A' }}</td>
                            <td class="py-3 text-secondary">{{ $p->uom }}</td>
                            <td class="py-3"><span class="badge bg-light text-dark border">{{ $p->sale_type }}</span></td>
                            <td class="py-3 text-end fw-semibold">Rs {{ number_format($p->unit_price, 2) }}</td>
                            <td class="py-3 text-end">{{ number_format($p->default_tax_rate, 1) }}%</td>
                            <td class="py-3 text-end px-3">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary view-product-btn"
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
                                        title="View Product">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary edit-product-btn"
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
                                        <button type="submit" class="btn btn-outline-danger" title="Delete Product">
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

        @if ($products->hasPages())
            <div class="pt-3 border-top mt-3">
                {{ $products->links() }}
            </div>
        @endif
    @endif
</div>

<!-- ================= ADD PRODUCT MODAL (Screenshot 1) ================= -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title font-display fw-bold text-dark fs-5" id="addProductModalLabel">Add product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('tenant.products.store') }}">
                @csrf
                <div class="modal-body px-4 py-3">
                    <!-- Row 1: SKU & Price -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="prod_sku" class="form-label">Product code (SKU) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="prod_sku" name="sku" value="{{ old('sku') }}" placeholder="" required>
                        </div>
                        <div class="col-md-6">
                            <label for="prod_price" class="form-label">Item sale price (PKR, excl. tax) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="prod_price" name="unit_price" value="{{ old('unit_price') }}" placeholder="" required>
                        </div>
                    </div>

                    <!-- Row 2: Description -->
                    <div class="mb-3">
                        <label for="prod_desc" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="prod_desc" name="description" value="{{ old('description') }}" placeholder="" required>
                    </div>

                    <!-- Row 3: HS Code & UOM -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="prod_hs" class="form-label">HS code <span class="text-danger">*</span></label>
                            <select class="form-select" id="prod_hs" name="hs_code" required>
                                <option value="">0101.2100</option>
                                @foreach ($hsCodes as $code)
                                    <option value="{{ $code->pct_code }}" {{ old('hs_code') == $code->pct_code ? 'selected' : '' }}>
                                        {{ $code->pct_code }} &mdash; {{ Str::limit($code->description, 35) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="prod_uom" class="form-label">UOM <span class="text-danger">*</span></label>
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

                    <!-- Row 4: Sale Type & Default Tax Rate -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="prod_saletype" class="form-label">Sale type <span class="text-danger">*</span></label>
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
                            <label for="prod_tax" class="form-label">Default tax rate</label>
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

                    <!-- Row 5: SRO / schedule no. & SRO item S. No. -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="prod_sro" class="form-label">SRO / schedule no.</label>
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
                            <label for="prod_serial" class="form-label">SRO item S. No.</label>
                            <input type="text" class="form-control" id="prod_serial" name="sro_item_serial" value="{{ old('sro_item_serial') }}" placeholder="SRO item serial">
                        </div>
                    </div>

                    <!-- Row 6: Retail price / unit (3rd Schedule) -->
                    <div class="mb-2">
                        <label for="prod_retail" class="form-label">Retail price / unit (3rd Schedule)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="prod_retail" name="retail_price" value="{{ old('retail_price') }}">
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4">
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
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title font-display fw-bold text-dark fs-5" id="editProductModalLabel">Edit product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="" id="editProductForm">
                @csrf
                @method('PUT')
                <div class="modal-body px-4 py-3">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Product code (SKU) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_prod_sku" name="sku" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Item sale price (PKR, excl. tax) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="edit_prod_price" name="unit_price" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_prod_desc" name="description" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">HS code <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_prod_hs" name="hs_code" required>
                                @foreach ($hsCodes as $code)
                                    <option value="{{ $code->pct_code }}">
                                        {{ $code->pct_code }} &mdash; {{ Str::limit($code->description, 35) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">UOM <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_prod_uom" name="uom" required>
                                @foreach ($uomList as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Sale type <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_prod_saletype" name="sale_type" required>
                                @foreach ($saleTypes as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default tax rate</label>
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

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">SRO / schedule no.</label>
                            <select class="form-select" id="edit_prod_sro" name="sro_schedule_no">
                                @foreach ($sroList as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SRO item S. No.</label>
                            <input type="text" class="form-control" id="edit_prod_serial" name="sro_item_serial">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Retail price / unit (3rd Schedule)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="edit_prod_retail" name="retail_price">
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4">
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
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title font-display fw-bold text-dark fs-5" id="viewProductModalLabel">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th class="text-muted small" style="width: 35%;">Product Code (SKU):</th><td class="fw-bold font-monospace" id="view_prod_sku"></td></tr>
                    <tr><th class="text-muted small">Description:</th><td id="view_prod_desc"></td></tr>
                    <tr><th class="text-muted small">HS Code:</th><td class="font-monospace" id="view_prod_hs"></td></tr>
                    <tr><th class="text-muted small">UOM:</th><td id="view_prod_uom"></td></tr>
                    <tr><th class="text-muted small">Sale Type:</th><td id="view_prod_saletype"></td></tr>
                    <tr><th class="text-muted small">Unit Price:</th><td class="fw-semibold text-primary" id="view_prod_price"></td></tr>
                    <tr><th class="text-muted small">Tax Rate:</th><td id="view_prod_tax"></td></tr>
                    <tr><th class="text-muted small">Retail Price:</th><td id="view_prod_retail"></td></tr>
                    <tr><th class="text-muted small">SRO / Schedule:</th><td id="view_prod_sro"></td></tr>
                    <tr><th class="text-muted small">Item Serial No:</th><td id="view_prod_serial"></td></tr>
                </table>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
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
