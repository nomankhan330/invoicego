@extends('layouts.tenant')

@section('title', 'Edit Invoice ' . $invoice->invoice_number)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">Edit Invoice: {{ $invoice->invoice_number }}</h1>
        <div class="text-muted small">
            Modify invoice details and items before fiscalization &middot; Current month: {{ date('01 M Y') }} – {{ date('t M Y') }}
        </div>
    </div>
    <a href="{{ route('tenant.invoices.show', $invoice->uuid) }}" class="btn btn-ig-outline">
        <i class="bi bi-arrow-left me-1"></i> Back to Invoice
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger py-2 px-3 small mb-4">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Validation Errors:</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('tenant.invoices.update', $invoice->uuid) }}" id="invoiceForm">
    @csrf
    @method('PUT')

    <!-- Invoice Header Box -->
    <div class="ig-content-card mb-4">
        <div class="row g-3">
            <div class="col-md-5">
                <label for="customer_id" class="form-label">Customer / Buyer <span class="text-danger">*</span></label>
                <select class="form-select" id="customer_id" name="customer_id" required>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" data-registered="{{ $c->isRegistered() ? '1' : '0' }}" data-ntn="{{ $c->ntn_cnic }}" {{ $invoice->customer_id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->isRegistered() ? 'Registered NTN: ' : 'Unregistered CNIC: ' }}{{ $c->ntn_cnic ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                <div id="buyerAlert" class="mt-1 small" style="display: none;"></div>
            </div>

            <div class="col-md-4">
                <label for="invoice_date" class="form-label">Invoice Date (PKT) <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" min="{{ $minDate }}" max="{{ $maxDate }}" required>
                <div class="form-text">Must be current month: {{ date('M Y') }}</div>
            </div>

            <div class="col-md-3">
                <label for="invoice_type" class="form-label">Invoice Type <span class="text-danger">*</span></label>
                <select class="form-select" id="invoice_type" name="invoice_type" required>
                    <option value="standard" {{ $invoice->invoice_type === 'standard' ? 'selected' : '' }}>Standard Invoice</option>
                    <option value="credit_note" {{ $invoice->invoice_type === 'credit_note' ? 'selected' : '' }}>Credit Note (Return/Discount)</option>
                    <option value="debit_note" {{ $invoice->invoice_type === 'debit_note' ? 'selected' : '' }}>Debit Note (Upward Adjustment)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="ig-content-card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Line Items</h5>
            <button type="button" class="btn btn-sm btn-ig-outline" id="addRowBtn">
                <i class="bi bi-plus-circle me-1"></i> Add Item
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle small" id="itemsTable">
                <thead class="bg-light">
                    <tr class="text-secondary text-uppercase" style="font-size: 11px;">
                        <th style="width: 25%;">Product / Description</th>
                        <th style="width: 14%;">HS / PCT Code</th>
                        <th style="width: 8%;">Qty</th>
                        <th style="width: 8%;">UOM</th>
                        <th style="width: 12%;">Unit Price</th>
                        <th style="width: 8%;">Tax %</th>
                        <th style="width: 8%;">Further %</th>
                        <th style="width: 13%;">Total (Rs)</th>
                        <th style="width: 4%;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    @foreach ($invoice->items as $idx => $item)
                        <tr class="item-row">
                            <td>
                                <input type="text" class="form-control form-control-sm mb-1 item-desc" name="items[{{ $idx }}][product_description]" value="{{ $item->product_description }}" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm item-hs" name="items[{{ $idx }}][hs_code]" value="{{ $item->hs_code }}" required>
                            </td>
                            <td>
                                <input type="number" step="0.001" min="0.001" class="form-control form-control-sm item-qty" name="items[{{ $idx }}][quantity]" value="{{ $item->quantity }}" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm item-uom" name="items[{{ $idx }}][uom]" value="{{ $item->uom }}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm item-price" name="items[{{ $idx }}][unit_price]" value="{{ $item->unit_price }}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm item-tax" name="items[{{ $idx }}][tax_rate]" value="{{ $item->tax_rate }}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm item-further" name="items[{{ $idx }}][further_tax_rate]" value="{{ $item->further_tax_rate }}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm item-total bg-light fw-bold text-end" value="{{ number_format($item->total_value_incl_tax, 2) }}" readonly>
                                <input type="hidden" name="items[{{ $idx }}][discount_amount]" value="{{ $item->discount_amount }}">
                                <input type="hidden" name="items[{{ $idx }}][st_withheld_rate]" value="{{ $item->st_withheld_rate }}">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm text-danger remove-row-btn p-0"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Calculations Summary -->
        <div class="row justify-content-end mt-4">
            <div class="col-md-5">
                <div class="bg-light rounded-3 p-3 border">
                    <div class="sales-row">
                        <span class="sales-label">Value (excl. Sales Tax):</span>
                        <span class="sales-val" id="dispExcl">Rs {{ number_format($invoice->sales_value_excl_tax, 2) }}</span>
                    </div>
                    <div class="sales-row">
                        <span class="sales-label">Sales Tax (GST):</span>
                        <span class="sales-val" id="dispTax">Rs {{ number_format($invoice->sales_tax_amount, 2) }}</span>
                    </div>
                    <div class="sales-row">
                        <span class="sales-label">Further Tax (§3(1A)):</span>
                        <span class="sales-val" id="dispFurther">Rs {{ number_format($invoice->further_tax_amount, 2) }}</span>
                    </div>
                    <div class="sales-row">
                        <span class="sales-label">Gross Invoice Value:</span>
                        <span class="sales-val text-primary fs-5" id="dispTotal">Rs {{ number_format($invoice->total_invoice_value, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Action Row -->
    <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('tenant.invoices.show', $invoice->uuid) }}" class="btn btn-outline-secondary">Cancel</a>
        <div class="d-flex gap-2">
            <button type="submit" name="save_draft" value="1" class="btn btn-ig-outline px-4">
                Save Changes
            </button>
            <button type="submit" name="submit_to_fbr" value="1" class="btn btn-ig-primary px-4 d-flex align-items-center gap-2">
                <i class="bi bi-shield-check"></i>
                <span>Save &amp; Submit to FBR</span>
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = {{ $invoice->items->count() }};
    const tableBody = document.getElementById('itemsBody');
    const customerSelect = document.getElementById('customer_id');
    const buyerAlert = document.getElementById('buyerAlert');

    function isCustomerUnregistered() {
        const selected = customerSelect.options[customerSelect.selectedIndex];
        return selected && selected.getAttribute('data-registered') === '0';
    }

    function updateBuyerAlert() {
        const selected = customerSelect.options[customerSelect.selectedIndex];
        if (!selected || !selected.value) {
            buyerAlert.style.display = 'none';
            return;
        }

        const isReg = selected.getAttribute('data-registered') === '1';
        buyerAlert.style.display = 'block';

        if (isReg) {
            buyerAlert.className = 'mt-1 small text-success';
            buyerAlert.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Registered Buyer with FBR (Standard 18% GST).';
        } else {
            buyerAlert.className = 'mt-1 small text-warning text-dark';
            buyerAlert.innerHTML = '<i class="bi bi-info-circle-fill me-1 text-warning"></i> Unregistered Buyer. 4% Further Tax (§3(1A)) automatically applied.';
        }

        document.querySelectorAll('.item-row').forEach(row => {
            const furtherInput = row.querySelector('.item-further');
            if (furtherInput) {
                furtherInput.value = isReg ? '0.00' : '4.00';
            }
        });

        calculateTotals();
    }

    customerSelect.addEventListener('change', updateBuyerAlert);

    function calculateTotals() {
        let totalExcl = 0;
        let totalTax = 0;
        let totalFurther = 0;
        let grandTotal = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const taxRate = parseFloat(row.querySelector('.item-tax').value) || 0;
            const furtherRate = parseFloat(row.querySelector('.item-further').value) || 0;

            const valExcl = Math.round(qty * price * 100) / 100;
            const taxAmt = Math.round(valExcl * (taxRate / 100) * 100) / 100;
            const furtherAmt = Math.round(valExcl * (furtherRate / 100) * 100) / 100;
            const lineTotal = Math.round((valExcl + taxAmt + furtherAmt) * 100) / 100;

            row.querySelector('.item-total').value = lineTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            totalExcl += valExcl;
            totalTax += taxAmt;
            totalFurther += furtherAmt;
            grandTotal += lineTotal;
        });

        document.getElementById('dispExcl').innerText = 'Rs ' + totalExcl.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('dispTax').innerText = 'Rs ' + totalTax.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('dispFurther').innerText = 'Rs ' + totalFurther.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('dispTotal').innerText = 'Rs ' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    tableBody.addEventListener('input', calculateTotals);

    document.getElementById('addRowBtn').addEventListener('click', function () {
        const isUnreg = isCustomerUnregistered();
        const furtherRate = isUnreg ? '4.00' : '0.00';

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm mb-1 item-desc" name="items[${rowIndex}][product_description]" placeholder="Item description" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm item-hs" name="items[${rowIndex}][hs_code]" placeholder="8471.30.00" value="8471.30.00" required>
            </td>
            <td>
                <input type="number" step="0.001" min="0.001" class="form-control form-control-sm item-qty" name="items[${rowIndex}][quantity]" value="1.000" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm item-uom" name="items[${rowIndex}][uom]" value="NOS" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" class="form-control form-control-sm item-price" name="items[${rowIndex}][unit_price]" value="0.00" required>
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm item-tax" name="items[${rowIndex}][tax_rate]" value="18.00" required>
            </td>
            <td>
                <input type="number" step="0.01" class="form-control form-control-sm item-further" name="items[${rowIndex}][further_tax_rate]" value="${furtherRate}" readonly>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm item-total bg-light fw-bold text-end" value="0.00" readonly>
                <input type="hidden" name="items[${rowIndex}][discount_amount]" value="0">
                <input type="hidden" name="items[${rowIndex}][st_withheld_rate]" value="0">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm text-danger remove-row-btn p-0"><i class="bi bi-trash"></i></button>
            </td>
        `;
        tableBody.appendChild(tr);
        rowIndex++;
        calculateTotals();
    });

    tableBody.addEventListener('click', function (e) {
        if (e.target.closest('.remove-row-btn')) {
            if (document.querySelectorAll('.item-row').length > 1) {
                e.target.closest('.item-row').remove();
                calculateTotals();
            } else {
                alert('Invoice must have at least one line item.');
            }
        }
    });

    updateBuyerAlert();
});
</script>
@endpush
@endsection
