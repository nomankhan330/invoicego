@extends('layouts.tenant')

@section('title', 'Add Product')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">Add Product</h1>
        <div class="text-muted small">Configure items with standard PRAL PCT codes and unit of measurement</div>
    </div>
    <a href="{{ route('tenant.products.index') }}" class="btn btn-ig-outline">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="ig-content-card">
            <form method="POST" action="{{ route('tenant.products.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Laser Printer Model X" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="sku" class="form-label">SKU / Item Code</label>
                        <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku') }}" placeholder="PRN-LX-01">
                    </div>
                    <div class="col-md-6">
                        <label for="hs_code_id" class="form-label">HS / PCT Code <span class="text-danger">*</span></label>
                        <select class="form-select" id="hs_code_id" name="hs_code_id" required>
                            <option value="">-- Select PCT Code --</option>
                            @foreach ($hsCodes as $code)
                                <option value="{{ $code->id }}" {{ old('hs_code_id') == $code->id ? 'selected' : '' }}>
                                    {{ $code->pct_code }} &mdash; {{ Str::limit($code->description, 50) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="uom" class="form-label">Unit of Measure (UOM) <span class="text-danger">*</span></label>
                        <select class="form-select" id="uom" name="uom" required>
                            <option value="NOS" selected>NOS (Numbers / Units)</option>
                            <option value="KGS">KGS (Kilograms)</option>
                            <option value="MTR">MTR (Meters)</option>
                            <option value="LTR">LTR (Liters)</option>
                            <option value="TON">TON (Metric Ton)</option>
                            <option value="PKT">PKT (Packet)</option>
                            <option value="BOX">BOX (Box)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="unit_price" class="form-label">Unit Price (Excl. Tax) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" id="unit_price" name="unit_price" value="{{ old('unit_price', '0.00') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="default_tax_rate" class="form-label">Default Tax Rate % <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="default_tax_rate" name="default_tax_rate" value="{{ old('default_tax_rate', '18.00') }}" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label">Detailed Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2" placeholder="Item technical specification">{{ old('description') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('tenant.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-ig-primary px-4">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
