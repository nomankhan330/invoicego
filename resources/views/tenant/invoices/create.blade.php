@extends('layouts.tenant')

@section('title', 'Create invoice')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    /* TomSelect Single-Select Alignment & Styling */
    .ts-wrapper.single .ts-control {
        display: flex !important;
        align-items: center !important;
        flex-wrap: nowrap !important;
        height: 31px !important;
        min-height: 31px !important;
        max-height: 31px !important;
        padding: 0.25rem 0.5rem !important;
        padding-right: 1.75rem !important;
        font-size: 0.813rem !important;
        border-radius: 0.375rem !important;
        border-color: #CBD5E1 !important;
        cursor: pointer !important;
        overflow: hidden !important;
        line-height: 1.5 !important;
        background-color: #FFFFFF !important;
    }
    .ts-wrapper.single.focus .ts-control,
    .ts-wrapper.single.dropdown-active .ts-control {
        border-color: #5850EC !important;
        box-shadow: 0 0 0 2px rgba(88, 80, 236, 0.15) !important;
    }
    .ts-wrapper.single .ts-control > .item {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 100% !important;
        line-height: 1.5 !important;
        margin: 0 !important;
        padding: 0 !important;
        color: #0F172A !important;
    }
    .ts-wrapper.single .ts-control > input {
        display: none !important;
    }
    .plugin-dropdown_input .dropdown-input {
        border: 1px solid #CBD5E1 !important;
        border-radius: 0.375rem !important;
        margin: 6px 8px !important;
        width: calc(100% - 16px) !important;
        padding: 5px 10px !important;
        font-size: 0.813rem !important;
        background-color: #F8FAFC !important;
    }
    .plugin-dropdown_input .dropdown-input:focus {
        border-color: #5850EC !important;
        background-color: #FFFFFF !important;
        box-shadow: 0 0 0 2px rgba(88, 80, 236, 0.15) !important;
        outline: none !important;
    }
    .ts-dropdown {
        font-size: 0.813rem !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
        border-color: #CBD5E1 !important;
        z-index: 1055 !important;
        margin-top: 2px !important;
    }
    .ts-dropdown .option {
        padding: 6px 12px !important;
        font-size: 0.813rem !important;
    }
    .ts-dropdown .option.active,
    .ts-dropdown .option:hover {
        background-color: #EEF2FF !important;
        color: #4338CA !important;
    }
    .card-section {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 0.75rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
    }
    .section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 0.85rem;
    }
    .info-tile {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 0.5rem;
        padding: 0.65rem 0.85rem;
    }
    .info-tile-label {
        font-size: 0.75rem;
        color: #64748B;
        margin-bottom: 0.2rem;
    }
    .info-tile-value {
        font-size: 0.875rem;
        font-weight: 600;
        color: #0F172A;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .item-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 0.5rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
        position: relative;
    }
    .item-card:hover {
        border-color: #CBD5E1;
    }
    .item-badge-header {
        font-size: 0.85rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .form-label-xs {
        font-size: 0.78rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.25rem;
    }
    .form-helper-xs {
        font-size: 0.72rem;
        color: #64748B;
        margin-top: 0.2rem;
    }
    .action-bar-sticky {
        position: sticky;
        bottom: 0;
        background: #FFFFFF;
        border-top: 1px solid #E2E8F0;
        padding: 0.85rem 1.5rem;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 0.75rem;
        z-index: 1020;
        margin-left: -1.5rem;
        margin-right: -1.5rem;
        margin-bottom: -1.5rem;
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h1 class="fw-bold text-dark fs-3 mb-1 font-display">Create invoice</h1>
            <div class="text-secondary small">Seller details come from your business profile. Fields marked <span class="text-danger">*</span> are required.</div>
        </div>
        <div>
            @if ($business->production_submissions_enabled && $business->current_environment === 'production')
                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-semibold" style="font-size: 13px;">
                    <span class="me-1">●</span> PRODUCTION
                </span>
            @else
                <span class="badge rounded-pill bg-light text-success border px-3 py-2 fw-semibold" style="font-size: 13px; color: #16A34A !important;">
                    <span class="me-1" style="color: #16A34A;">●</span> PRODUCTION
                </span>
            @endif
        </div>
    </div>

    @if (!$business->production_submissions_enabled || $business->current_environment === 'sandbox')
        <div class="alert border-0 p-3 mb-4 rounded-3 d-flex align-items-start gap-2 shadow-sm" style="background-color: #FFFBEB; border: 1px solid #FDE68A !important; color: #92400E;">
            <i class="bi bi-exclamation-triangle-fill fs-5 mt-n1" style="color: #D97706 !important;"></i>
            <div class="small">
                <div class="fw-bold mb-1" style="color: #92400E;">Production is not enabled yet</div>
                <div style="color: #78350F;">
                    You can prepare and save drafts now. To validate and submit them to FBR, pass the required tests in
                    <a href="{{ route('tenant.sandbox-scenarios') }}" class="fw-semibold text-decoration-underline text-reset">Sandbox scenarios</a>,
                    then save the production token and enable production in
                    <a href="{{ route('tenant.fbr-integration') }}" class="fw-semibold text-decoration-underline text-reset">FBR integration</a>.
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger py-2 px-3 small mb-4 rounded-3">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please correct the following errors:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="validationResultAlert" style="display: none;" class="mb-4"></div>

    <form method="POST" action="{{ route('tenant.invoices.store') }}" id="invoiceForm">
        @csrf

        <!-- SECTION 1: INVOICE DETAILS & PROVINCES -->
        <div class="card-section">
            <div class="section-title">Invoice details</div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label-xs">Invoice Type (DROPDOWN) <span class="text-danger">*</span></label>
                    <select name="invoice_type" id="invoice_type" class="form-select form-select-sm" required>
                        <option value="standard" {{ old('invoice_type') === 'standard' ? 'selected' : '' }}>Sale Invoice</option>
                        <option value="credit_note" {{ old('invoice_type') === 'credit_note' ? 'selected' : '' }}>Credit Note</option>
                        <option value="debit_note" {{ old('invoice_type') === 'debit_note' ? 'selected' : '' }}>Debit Note</option>
                        <option value="stwh" {{ old('invoice_type') === 'stwh' ? 'selected' : '' }}>STWH (Sales Tax Withheld)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Invoice No</label>
                    <input type="text" name="invoice_number" id="invoice_number" class="form-control form-control-sm" value="{{ old('invoice_number') }}" placeholder="Auto-generated if empty">
                    <div class="form-helper-xs text-muted">Shown on the invoice; not sent to FBR</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Date with calendar (DD.MM.YY) <span class="text-danger">*</span></label>
                    <input type="date" name="invoice_date" id="invoice_date" class="form-control form-control-sm" value="{{ old('invoice_date', $defaultDate) }}" min="{{ $minDate }}" max="{{ $maxDate }}" required>
                    <div class="form-helper-xs">Current month only ({{ $minDate }} to {{ $maxDate }}, PKT)</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Current time</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="invoice_time" id="invoice_time" class="form-control form-control-sm" value="{{ old('invoice_time', $currentTime) }}">
                        <span class="input-group-text bg-white text-muted"><i class="bi bi-clock"></i></span>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Province of Supplier (Dropdown with Search) <span class="text-danger">*</span></label>
                    <select name="supplier_province" id="supplier_province" class="searchable-select" data-placeholder="Select Supplier Province..." required>
                        @foreach ($provinces as $prov)
                            <option value="{{ $prov }}" {{ old('supplier_province', $business->province ?? 'Punjab') === $prov ? 'selected' : '' }}>{{ $prov }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Destination of Supply (Dropdown with Search) <span class="text-danger">*</span></label>
                    <select name="destination_of_supply" id="destination_of_supply" class="searchable-select" data-placeholder="Select Destination..." required>
                        @foreach ($destinations as $dest)
                            <option value="{{ $dest }}" {{ old('destination_of_supply', 'Punjab') === $dest ? 'selected' : '' }}>{{ $dest }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Payment mode</label>
                    <select name="payment_mode" id="payment_mode" class="form-select form-select-sm">
                        @foreach ($paymentModes as $pm)
                            <option value="{{ $pm }}" {{ old('payment_mode', 'Cash') === $pm ? 'selected' : '' }}>{{ $pm }}</option>
                        @endforeach
                    </select>
                    <div class="form-helper-xs text-muted">How the buyer pays; not sent to FBR</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Reference invoice no.</label>
                    <input type="text" name="reference_invoice_no" id="reference_invoice_no" class="form-control form-control-sm" value="{{ old('reference_invoice_no') }}" placeholder="For credit/debit notes">
                    <div class="form-helper-xs text-muted">Only for debit notes</div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: SELLER DETAILS -->
        <div class="card-section">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="section-title mb-0">Seller details (from business profile)</div>
                <a href="{{ route('tenant.settings') }}" class="text-primary small text-decoration-none fw-semibold">Edit profile</a>
            </div>
            <div class="row g-2">
                <div class="col-md-3 col-6">
                    <div class="info-tile">
                        <div class="info-tile-label">Seller NTN/CNIC</div>
                        <div class="info-tile-value">{{ $business->ntn_cnic }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-tile">
                        <div class="info-tile-label">Business name</div>
                        <div class="info-tile-value">{{ $business->business_name ?? $business->name }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-tile">
                        <div class="info-tile-label">STRN</div>
                        <div class="info-tile-value">{{ $business->strn ?: 'N/A' }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-tile">
                        <div class="info-tile-label">Address</div>
                        <div class="info-tile-value">{{ $business->business_address ?? 'Registered Business Office' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: BUYER DETAILS -->
        <div class="card-section">
            <div class="section-title">Buyer details</div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label-xs">Saved customer</label>
                    <select id="saved_customer_select" name="customer_id" class="searchable-select" data-placeholder="Search customers...">
                        <option value="">New buyer</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}"
                                data-name="{{ $c->name }}"
                                data-ntn="{{ $c->ntn_cnic }}"
                                data-province="{{ $c->province }}"
                                data-address="{{ $c->address }}"
                                data-type="{{ $c->registration_type }}">
                                {{ $c->name }} ({{ $c->ntn_cnic }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-helper-xs">Search and select, or type a new buyer below</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Buyer NTN / CNIC</label>
                    <input type="text" name="buyer_ntn_cnic" id="buyer_ntn_cnic" class="form-control form-control-sm" value="{{ old('buyer_ntn_cnic') }}" placeholder="7-digit NTN or 13-digit CNIC">
                    <div class="form-helper-xs">7-digit NTN or 13-digit CNIC; optional for unregistered buyers</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Buyer name <span class="text-danger">*</span></label>
                    <input type="text" name="buyer_name" id="buyer_name" class="form-control form-control-sm" value="{{ old('buyer_name') }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Buyer province <span class="text-danger">*</span></label>
                    <select name="buyer_province" id="buyer_province" class="searchable-select" data-placeholder="Select Province..." required>
                        @foreach ($provinces as $prov)
                            <option value="{{ $prov }}" {{ old('buyer_province', 'Punjab') === $prov ? 'selected' : '' }}>{{ $prov }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Buyer type <span class="text-danger">*</span></label>
                    <select name="buyer_type" id="buyer_type" class="form-select form-select-sm" required>
                        <option value="registered" {{ old('buyer_type') === 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="unregistered" {{ old('buyer_type', 'unregistered') === 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                    </select>
                    <div class="d-flex align-items-center justify-content-between mt-1">
                        <a href="javascript:void(0)" id="verifyBuyerBtn" class="text-primary small text-decoration-none fw-semibold" style="font-size: 11px;">
                            <i class="bi bi-arrow-repeat me-1"></i> Verify registration with FBR
                        </a>
                        <span id="buyerVerificationBadge" class="small" style="font-size: 11px; display: none;"></span>
                    </div>
                </div>

                <div class="col-md-9">
                    <label class="form-label-xs">Buyer Address <span class="text-danger">*</span></label>
                    <input type="text" name="buyer_address" id="buyer_address" class="form-control form-control-sm" value="{{ old('buyer_address', 'Pakistan') }}" required>
                </div>
            </div>
        </div>

        <!-- SECTION 4: ITEMS ENTRY TABLE (THE CORE) -->
        <div class="card-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="section-title mb-0">Items details</div>
                    <div class="text-muted small">Items Entry Table (The Core) — Configure products, HS codes, tax rates, SRO/Schedule and item serial numbers</div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check form-check-inline m-0">
                        <input class="form-check-input" type="checkbox" id="autoCalcCheckbox" checked>
                        <label class="form-check-label small fw-semibold text-dark" for="autoCalcCheckbox">Automatic calculation</label>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary px-3 fw-semibold shadow-sm" id="addItemBtn" style="background-color: #2563EB; border-color: #2563EB;">
                        <i class="bi bi-plus-lg me-1"></i> Add item
                    </button>
                </div>
            </div>

            <!-- Items Container -->
            <div id="itemsContainer">
                <!-- Dynamic Items will be rendered here -->
            </div>
        </div>

        <!-- TAX SUMMARY FOOTER -->
        <div class="row justify-content-end mb-4">
            <div class="col-md-6 col-lg-5">
                <div class="card border shadow-sm rounded-3 p-3 bg-white">
                    <div class="fw-bold text-dark pb-2 mb-2 border-bottom d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-calculator me-1 text-primary"></i> Totals preview</span>
                        <span class="badge bg-light text-muted border" id="summaryItemCount">1 item</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 small text-secondary">
                        <span>Total Quantity</span>
                        <span class="fw-semibold text-dark" id="summaryTotalQty">0.00</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 small text-secondary">
                        <span>Value excl. ST</span>
                        <span class="fw-semibold text-dark" id="summaryValExcl">Rs 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 small text-secondary">
                        <span>Sales tax</span>
                        <span class="fw-semibold text-dark" id="summarySalesTax">Rs 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 small text-secondary">
                        <span>Further tax</span>
                        <span class="fw-semibold text-danger" id="summaryFurtherTax">Rs 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 small text-secondary">
                        <span>Extra tax + FED</span>
                        <span class="fw-semibold text-dark" id="summaryExtraFed">Rs 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 small text-secondary border-bottom pb-2">
                        <span>ST withheld</span>
                        <span class="fw-semibold text-dark" id="summarySTWithheld">Rs 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <span class="fw-bold text-dark fs-6">Gross invoice value (incl. taxes)</span>
                        <span class="fw-bold text-primary fs-4 font-display" id="summaryGrandTotal">Rs 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-1 small text-secondary">
                        <span>Net receivable (after ST withheld)</span>
                        <span class="fw-semibold text-dark" id="summaryNetReceivable">Rs 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- STICKY ACTION BAR -->
        <div class="action-bar-sticky shadow-sm">
            <a href="{{ route('tenant.invoices.index') }}" class="btn btn-light border px-4 btn-sm fw-medium">Cancel</a>
            <button type="submit" name="action" value="draft" class="btn btn-outline-secondary px-3 btn-sm fw-medium">
                <i class="bi bi-file-earmark me-1"></i> Save as draft
            </button>
            <button type="button" id="validateDraftBtn" class="btn btn-outline-primary px-3 btn-sm fw-medium">
                <i class="bi bi-shield-check me-1"></i> Validate with FBR
            </button>
            <button type="button" class="btn btn-outline-secondary px-3 btn-sm fw-medium" disabled title="Save invoice first to download PDF">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </button>
            <button type="submit" name="submit_to_fbr" value="1" class="btn btn-primary px-4 btn-sm fw-semibold shadow-sm" style="background-color: #2563EB; border-color: #2563EB;">
                <i class="bi bi-send-fill me-1"></i> Submit to FBR
            </button>
        </div>
                <i class="bi bi-send-fill me-1"></i> Submit to FBR (Blue)
            </button>
        </div>
    </form>
</div>

<!-- Raw Lookup Data for Client-Side JavaScript -->
<script>
    window.INVOICE_CONFIG = {
        products: @json($products),
        hsCodes: @json($hsCodes),
        saleTypes: @json($saleTypes),
        uomList: @json($uomList),
        sroList: @json($sroList),
        itemSerials: @json($itemSerials),
        taxRates: @json($taxRates),
        provinces: @json($provinces),
        destinations: @json($destinations),
        invoiceTypes: @json($invoiceTypes),
        buyerTypes: @json($buyerTypes),
        validateUrl: @json(route('tenant.invoices.validate-draft')),
        verifyBuyerUrl: @json(route('tenant.invoices.verify-buyer')),
        csrfToken: '{{ csrf_token() }}'
    };
</script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const config = window.INVOICE_CONFIG;
    let itemCount = 0;
    const tomSelectInstances = {};

    // 1. Initialize TomSelect on Province of Supplier & Destination of Supply
    const supplierProvEl = document.getElementById('supplier_province');
    if (supplierProvEl) {
        new TomSelect(supplierProvEl, {
            plugins: ['dropdown_input'],
            create: false,
            placeholder: 'Select Supplier Province...',
            closeAfterSelect: true
        });
    }

    const destSupplyEl = document.getElementById('destination_of_supply');
    if (destSupplyEl) {
        new TomSelect(destSupplyEl, {
            plugins: ['dropdown_input'],
            create: false,
            placeholder: 'Select Destination of Supply...',
            closeAfterSelect: true
        });
    }

    // 2. Initialize TomSelect on Saved Customer
    const customerSelectEl = document.getElementById('saved_customer_select');
    if (customerSelectEl) {
        new TomSelect(customerSelectEl, {
            plugins: ['dropdown_input'],
            create: false,
            placeholder: 'Search customers...',
            allowEmptyOption: true,
            closeAfterSelect: true,
            onChange: function (val) {
                if (!val) {
                    document.getElementById('buyer_name').value = '';
                    document.getElementById('buyer_ntn_cnic').value = '';
                    document.getElementById('buyer_address').value = '';
                    document.getElementById('buyer_type').value = 'unregistered';
                    recalculateAll();
                    return;
                }
                const opt = customerSelectEl.querySelector(`option[value="${val}"]`);
                if (opt) {
                    document.getElementById('buyer_name').value = opt.getAttribute('data-name') || '';
                    document.getElementById('buyer_ntn_cnic').value = opt.getAttribute('data-ntn') || '';
                    document.getElementById('buyer_address').value = opt.getAttribute('data-address') || '';
                    const prov = opt.getAttribute('data-province');
                    if (prov && destSupplyEl && destSupplyEl.tomselect) {
                        destSupplyEl.tomselect.setValue(prov);
                    }
                    const regType = opt.getAttribute('data-type');
                    document.getElementById('buyer_type').value = (regType === 'registered' ? 'registered' : 'unregistered');
                }
                recalculateAll();
            }
        });
    }

    // Buyer Type Dropdown Change Listener
    const buyerTypeEl = document.getElementById('buyer_type');
    if (buyerTypeEl) {
        buyerTypeEl.addEventListener('change', recalculateAll);
    }

    // 3. Buyer ATL Verification Button
    const verifyBuyerBtn = document.getElementById('verifyBuyerBtn');
    const verifyBadge = document.getElementById('buyerVerificationBadge');
    if (verifyBuyerBtn) {
        verifyBuyerBtn.addEventListener('click', function () {
            const ntn = document.getElementById('buyer_ntn_cnic').value.trim();
            if (!ntn) {
                alert('Please enter Buyer NTN/CNIC first.');
                return;
            }
            verifyBadge.style.display = 'inline-block';
            verifyBadge.className = 'text-muted small';
            verifyBadge.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Checking ATL...';

            fetch(`${config.verifyBuyerUrl}?ntn=${encodeURIComponent(ntn)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    verifyBadge.className = 'text-success fw-semibold small';
                    verifyBadge.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${data.status}`;
                    if (data.registration_type) {
                        document.getElementById('buyer_type').value = data.registration_type;
                        recalculateAll();
                    }
                } else {
                    verifyBadge.className = 'text-danger small';
                    verifyBadge.innerHTML = `<i class="bi bi-x-circle-fill"></i> ${data.message}`;
                }
            })
            .catch(() => {
                verifyBadge.className = 'text-danger small';
                verifyBadge.innerHTML = 'FBR check failed';
            });
        });
    }

    // 4. Dynamic Item Row Generator
    function addItem(initialData = {}) {
        itemCount++;
        const index = itemCount;
        const container = document.getElementById('itemsContainer');

        const card = document.createElement('div');
        card.className = 'item-card';
        card.id = `item_row_${index}`;
        card.setAttribute('data-index', index);

        card.innerHTML = `
            <div class="item-badge-header">
                <span>Item <span class="item-number-text">${index}</span></span>
                <button type="button" class="btn btn-link text-danger p-0 delete-item-btn" title="Delete this item" style="${index === 1 ? 'display:none;' : ''}">
                    <i class="bi bi-trash3-fill fs-5"></i>
                </button>
            </div>

            <!-- Row 1: Saved Product, Sale Type, HS Code, Tax Rate -->
            <div class="row g-2 mb-2">
                <div class="col-md-3">
                    <label class="form-label-xs">Saved product (optional)</label>
                    <select name="items[${index}][product_id]" class="product-select" data-placeholder="Choose saved product...">
                        <option value="">Choose a saved product (optional)</option>
                        ${config.products.map(p => `
                            <option value="${p.id}"
                                data-sku="${p.sku}"
                                data-desc="${p.description || p.name}"
                                data-price="${p.unit_price || 0}"
                                data-hscode="${p.hs_code || ''}"
                                data-uom="${p.uom || 'MT'}"
                                data-saletype="${p.sale_type || 'Goods at Standard Rate (Default)'}"
                                data-taxrate="${p.default_tax_rate ? p.default_tax_rate + '%' : '18.00%'}"
                                data-sro="${p.sro_schedule_no || ''}"
                                data-sroserial="${p.sro_item_serial || ''}"
                                data-retail="${p.retail_price || ''}">
                                ${p.sku} — ${p.name || p.description}
                            </option>
                        `).join('')}
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Sale Type (Dropdown with Search) <span class="text-danger">*</span></label>
                    <select name="items[${index}][sale_type]" class="saletype-select" data-placeholder="Choose Sale Type..." required>
                        ${config.saleTypes.map(st => `<option value="${st}" ${st.includes('Standard') ? 'selected' : ''}>${st}</option>`).join('')}
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">HS Code (Dropdown with Search) <span class="text-danger">*</span></label>
                    <select name="items[${index}][hs_code]" class="hscode-select" data-placeholder="Search HS Code..." required>
                        <option value="">Search HS / PCT Code...</option>
                        ${config.hsCodes.map(h => `<option value="${h.pct_code}">${h.pct_code} — ${h.description.substring(0, 40)}</option>`).join('')}
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Tax Rate (%) (Dropdown with Search) <span class="text-danger">*</span></label>
                    <select name="items[${index}][tax_rate]" class="taxrate-select item-tax-rate" data-placeholder="Select Tax Rate..." required>
                        ${config.taxRates.map(tr => `<option value="${tr}" ${tr === '18.00%' ? 'selected' : ''}>${tr}</option>`).join('')}
                    </select>
                </div>
            </div>

            <!-- Row 2: Product Description, UOM, Quantity, Unit Price -->
            <div class="row g-2 mb-2">
                <div class="col-md-3">
                    <label class="form-label-xs">Product Description <span class="text-danger">*</span></label>
                    <input type="text" name="items[${index}][product_description]" class="form-control form-control-sm item-desc" placeholder="Product details" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">UoM (Dropdown with Search) <span class="text-danger">*</span></label>
                    <select name="items[${index}][uom]" class="uom-select" data-placeholder="Select UoM..." required>
                        ${Object.entries(config.uomList).map(([code, name]) => `<option value="${code}" ${code === 'MT' || code === 'SET' || code === 'KG' ? 'selected' : ''}>${name}</option>`).join('')}
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Quantity <span class="text-danger">*</span></label>
                    <input type="number" step="any" min="0.001" name="items[${index}][quantity]" class="form-control form-control-sm item-qty" value="1" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label-xs">Unit price (excl. tax) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="items[${index}][unit_price]" class="form-control form-control-sm item-unit-price" value="0.00" required>
                </div>
            </div>

            <!-- Row 3: Discount, Value excl tax, Sales Tax, Retail Price, Fixed Value, Extra Tax -->
            <div class="row g-2 mb-2">
                <div class="col-md-2">
                    <label class="form-label-xs">Discount</label>
                    <input type="number" step="0.01" min="0" name="items[${index}][discount_amount]" class="form-control form-control-sm item-discount" value="0.00">
                    <div class="form-helper-xs">Deducted once</div>
                </div>

                <div class="col-md-2">
                    <label class="form-label-xs">VALUE OF SALES (EXCL. TAX)</label>
                    <input type="text" class="form-control form-control-sm bg-light item-val-excl fw-semibold text-dark" value="0.00" readonly>
                    <div class="form-helper-xs text-muted">Auto: Qty &times; Price</div>
                </div>

                <div class="col-md-2">
                    <label class="form-label-xs">Sales Tax</label>
                    <input type="number" step="0.01" min="0" name="items[${index}][sales_tax_amount]" class="form-control form-control-sm item-sales-tax" value="0.00">
                    <div class="form-helper-xs text-muted">Auto: Val &times; Tax Rate %</div>
                </div>

                <div class="col-md-2">
                    <label class="form-label-xs">Fixed / Notified Value</label>
                    <input type="number" step="0.01" min="0" name="items[${index}][fixed_notified_value]" class="form-control form-control-sm item-fixed-val">
                    <div class="form-helper-xs">Fixed tariff value</div>
                </div>

                <div class="col-md-2">
                    <label class="form-label-xs">Retail Price (MRP)</label>
                    <input type="number" step="0.01" min="0" name="items[${index}][retail_price]" class="form-control form-control-sm item-retail-price">
                    <div class="form-helper-xs">3rd Schedule items</div>
                </div>

                <div class="col-md-2">
                    <label class="form-label-xs">Extra Tax</label>
                    <input type="number" step="0.01" min="0" name="items[${index}][extra_tax_amount]" class="form-control form-control-sm item-extra-tax" value="0.00">
                    <div class="form-check small mt-1">
                        <input class="form-check-input item-send-empty" type="checkbox" name="items[${index}][send_extra_tax_as_empty]" value="1" id="empty_extra_${index}">
                        <label class="form-check-label" for="empty_extra_${index}" style="font-size: 10px;">Send as empty (FBR 009)</label>
                    </div>
                </div>
            </div>

            <!-- Row 4: Further tax, SRO / Schedule No, Item S. No, FED, ST Withheld, Total -->
            <div class="row g-2">
                <div class="col-md-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label-xs mb-0">Further Tax (4%)</label>
                        <div class="form-check form-switch m-0" title="Option to cancel further tax">
                            <input class="form-check-input item-cancel-further" type="checkbox" name="items[${index}][cancel_further_tax]" value="1" id="cancel_further_${index}">
                            <label class="form-check-label" for="cancel_further_${index}" style="font-size: 10.5px; color: #DC2626; font-weight: 600;">Cancel</label>
                        </div>
                    </div>
                    <input type="number" step="0.01" min="0" name="items[${index}][further_tax_amount]" class="form-control form-control-sm item-further-tax mt-1" value="0.00">
                    <div class="form-helper-xs text-muted" id="further_note_${index}">Auto 4% for unregistered</div>
                </div>

                <div class="col-md-2">
                    <label class="form-label-xs">SRO No/Schedule No</label>
                    <select name="items[${index}][sro_schedule_no]" class="sro-select" data-placeholder="Select SRO...">
                        <option value="">None / Not Applicable</option>
                        ${config.sroList.map(s => `<option value="${s}">${s}</option>`).join('')}
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label-xs">Item S. No (Dropdown)</label>
                    <select name="items[${index}][sro_item_serial]" class="serial-select item-sro-serial" data-placeholder="Select S. No...">
                        <option value="">None / S. No...</option>
                        ${config.itemSerials.map(s => `<option value="${s}">${s}</option>`).join('')}
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label-xs">FED payable</label>
                    <input type="number" step="0.01" min="0" name="items[${index}][fed_payable_amount]" class="form-control form-control-sm item-fed" value="0.00">
                </div>

                <div class="col-md-2">
                    <label class="form-label-xs">ST withheld at source</label>
                    <input type="number" step="0.01" min="0" name="items[${index}][st_withheld_amount]" class="form-control form-control-sm item-st-withheld" value="0.00">
                </div>

                <div class="col-md-2">
                    <label class="form-label-xs">Total (incl. taxes)</label>
                    <input type="text" class="form-control form-control-sm bg-light item-total-incl fw-bold text-dark" value="0.00" readonly>
                    <div class="form-helper-xs text-muted">Calculated</div>
                </div>
            </div>
        `;

        container.appendChild(card);

        // Initialize TomSelect on searchable dropdowns in this item card
        const prodSelect = card.querySelector('.product-select');
        const saleSelect = card.querySelector('.saletype-select');
        const hsSelect = card.querySelector('.hscode-select');
        const taxSelect = card.querySelector('.taxrate-select');
        const uomSelect = card.querySelector('.uom-select');
        const sroSelect = card.querySelector('.sro-select');
        const serialSelect = card.querySelector('.serial-select');

        const prodTs = new TomSelect(prodSelect, {
            plugins: ['dropdown_input'],
            create: false,
            placeholder: 'Choose saved product...',
            allowEmptyOption: true,
            closeAfterSelect: true,
            onChange: function (pId) {
                if (!pId) return;
                const opt = prodSelect.querySelector(`option[value="${pId}"]`);
                if (opt) {
                    card.querySelector('.item-desc').value = opt.getAttribute('data-desc') || '';
                    card.querySelector('.item-unit-price').value = opt.getAttribute('data-price') || '0.00';
                    const hs = opt.getAttribute('data-hscode');
                    if (hs && hsTs) hsTs.setValue(hs);
                    const uom = opt.getAttribute('data-uom');
                    if (uom && uomTs) uomTs.setValue(uom);
                    const st = opt.getAttribute('data-saletype');
                    if (st && saleTs) saleTs.setValue(st);
                    const sro = opt.getAttribute('data-sro');
                    if (sro && sroTs) sroTs.setValue(sro);
                    const sroSerial = opt.getAttribute('data-sroserial');
                    if (sroSerial && serialTs) serialTs.setValue(sroSerial);
                    const tr = opt.getAttribute('data-taxrate');
                    if (tr && taxTs) taxTs.setValue(tr);
                    const retail = opt.getAttribute('data-retail');
                    if (retail) card.querySelector('.item-retail-price').value = retail;
                }
                recalculateCard(card);
            }
        });

        const saleTs = new TomSelect(saleSelect, {
            plugins: ['dropdown_input'],
            create: false,
            placeholder: 'Choose Sale Type...',
            closeAfterSelect: true
        });

        const hsTs = new TomSelect(hsSelect, {
            plugins: ['dropdown_input'],
            create: false,
            placeholder: 'Search HS Code...',
            maxOptions: 100,
            closeAfterSelect: true
        });

        const taxTs = new TomSelect(taxSelect, {
            plugins: ['dropdown_input'],
            create: false,
            placeholder: 'Select Tax Rate...',
            maxOptions: 200,
            closeAfterSelect: true,
            onChange: () => recalculateCard(card)
        });

        const uomTs = new TomSelect(uomSelect, {
            plugins: ['dropdown_input'],
            create: false,
            placeholder: 'Select UoM...',
            closeAfterSelect: true
        });

        const sroTs = new TomSelect(sroSelect, {
            plugins: ['dropdown_input'],
            create: false,
            placeholder: 'Select SRO...',
            allowEmptyOption: true,
            maxOptions: 200,
            closeAfterSelect: true
        });

        const serialTs = new TomSelect(serialSelect, {
            plugins: ['dropdown_input'],
            create: false,
            placeholder: 'Select S. No...',
            allowEmptyOption: true,
            maxOptions: 500,
            closeAfterSelect: true
        });

        tomSelectInstances[`item_${index}`] = { prodTs, saleTs, hsTs, taxTs, uomTs, sroTs, serialTs };

        // Attach event listeners for calculations
        const inputs = card.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('input', () => recalculateCard(card));
            input.addEventListener('change', () => recalculateCard(card));
        });

        // Cancel Further Tax checkbox listener
        const cancelFurtherCb = card.querySelector('.item-cancel-further');
        if (cancelFurtherCb) {
            cancelFurtherCb.addEventListener('change', () => recalculateCard(card));
        }

        // Delete button listener
        const delBtn = card.querySelector('.delete-item-btn');
        delBtn.addEventListener('click', function () {
            card.remove();
            updateItemNumbers();
            recalculateAll();
        });

        updateItemNumbers();
        recalculateCard(card);
    }

    function updateItemNumbers() {
        const cards = document.querySelectorAll('.item-card');
        cards.forEach((c, idx) => {
            const num = idx + 1;
            const badge = c.querySelector('.item-number-text');
            if (badge) badge.innerText = num;
            const del = c.querySelector('.delete-item-btn');
            if (del) {
                del.style.display = cards.length > 1 ? 'inline-block' : 'none';
            }
        });
    }

    // 5. Real-Time Calculation Engine
    function recalculateCard(card) {
        const autoCalc = document.getElementById('autoCalcCheckbox').checked;
        const buyerTypeVal = document.getElementById('buyer_type').value;
        const isRegistered = (buyerTypeVal === 'registered');

        const qty = parseFloat(card.querySelector('.item-qty').value) || 0;
        const unitPrice = parseFloat(card.querySelector('.item-unit-price').value) || 0;
        const discount = parseFloat(card.querySelector('.item-discount').value) || 0;

        const valExcl = Math.max(0, (qty * unitPrice));
        card.querySelector('.item-val-excl').value = valExcl.toFixed(2);

        let salesTax = parseFloat(card.querySelector('.item-sales-tax').value) || 0;
        let furtherTax = parseFloat(card.querySelector('.item-further-tax').value) || 0;
        const extraTax = parseFloat(card.querySelector('.item-extra-tax').value) || 0;
        const fed = parseFloat(card.querySelector('.item-fed').value) || 0;
        const stWithheld = parseFloat(card.querySelector('.item-st-withheld').value) || 0;

        if (autoCalc) {
            const taxRateStr = card.querySelector('.item-tax-rate').value || '18.00%';
            let taxRateNum = 0;
            const pctMatch = taxRateStr.match(/^([0-9]+(?:\.[0-9]+)?)\s*%/);
            if (pctMatch) {
                taxRateNum = parseFloat(pctMatch[1]);
            } else if (!isNaN(parseFloat(taxRateStr))) {
                taxRateNum = parseFloat(taxRateStr);
            }

            salesTax = (valExcl * taxRateNum) / 100;
            card.querySelector('.item-sales-tax').value = salesTax.toFixed(2);

            // Further Tax 4% auto-applied for unregistered buyers, unless cancelled
            const cancelFurther = card.querySelector('.item-cancel-further').checked;
            const furtherNote = card.querySelector('[id^="further_note_"]');

            if (!isRegistered && !cancelFurther) {
                furtherTax = (valExcl * 4.00) / 100;
                card.querySelector('.item-further-tax').value = furtherTax.toFixed(2);
                if (furtherNote) furtherNote.innerText = '4% applied (unregistered)';
            } else {
                furtherTax = 0.00;
                card.querySelector('.item-further-tax').value = '0.00';
                if (furtherNote) {
                    furtherNote.innerText = cancelFurther ? 'Cancelled (0%)' : '0% (registered buyer)';
                }
            }
        }

        const totalIncl = Math.max(0, (valExcl + salesTax + furtherTax + extraTax + fed - discount));
        card.querySelector('.item-total-incl').value = totalIncl.toFixed(2);

        recalculateTotalsPreview();
    }

    function recalculateAll() {
        document.querySelectorAll('.item-card').forEach(recalculateCard);
    }

    function recalculateTotalsPreview() {
        let totalValExcl = 0;
        let totalSalesTax = 0;
        let totalFurtherTax = 0;
        let totalExtraFed = 0;
        let totalSTWithheld = 0;
        let grandGross = 0;
        let totalQty = 0;
        const cards = document.querySelectorAll('.item-card');

        cards.forEach(card => {
            const qty = parseFloat(card.querySelector('.item-qty').value) || 0;
            const valExcl = parseFloat(card.querySelector('.item-val-excl').value) || 0;
            const salesTax = parseFloat(card.querySelector('.item-sales-tax').value) || 0;
            const furtherTax = parseFloat(card.querySelector('.item-further-tax').value) || 0;
            const extraTax = parseFloat(card.querySelector('.item-extra-tax').value) || 0;
            const fed = parseFloat(card.querySelector('.item-fed').value) || 0;
            const stWithheld = parseFloat(card.querySelector('.item-st-withheld').value) || 0;
            const totalIncl = parseFloat(card.querySelector('.item-total-incl').value) || 0;

            totalQty += qty;
            totalValExcl += valExcl;
            totalSalesTax += salesTax;
            totalFurtherTax += furtherTax;
            totalExtraFed += (extraTax + fed);
            totalSTWithheld += stWithheld;
            grandGross += totalIncl;
        });

        const countText = `${cards.length} ${cards.length === 1 ? 'item' : 'items'}`;
        document.getElementById('summaryItemCount').innerText = countText;
        document.getElementById('summaryTotalQty').innerText = totalQty.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 3 });
        document.getElementById('summaryValExcl').innerText = 'Rs ' + formatMoney(totalValExcl);
        document.getElementById('summarySalesTax').innerText = 'Rs ' + formatMoney(totalSalesTax);
        document.getElementById('summaryFurtherTax').innerText = 'Rs ' + formatMoney(totalFurtherTax);
        document.getElementById('summaryExtraFed').innerText = 'Rs ' + formatMoney(totalExtraFed);
        document.getElementById('summarySTWithheld').innerText = 'Rs ' + formatMoney(totalSTWithheld);
        document.getElementById('summaryGrandTotal').innerText = 'Rs ' + formatMoney(grandGross);
        const netReceivable = Math.max(0, grandGross - totalSTWithheld);
        const netEl = document.getElementById('summaryNetReceivable');
        if (netEl) netEl.innerText = 'Rs ' + formatMoney(netReceivable);
    }

    function formatMoney(num) {
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Auto Calc Checkbox Toggle
    document.getElementById('autoCalcCheckbox').addEventListener('change', recalculateAll);

    // Add Item Button
    document.getElementById('addItemBtn').addEventListener('click', () => addItem());

    // 6. Pre-Submission Validate Button
    const validateDraftBtn = document.getElementById('validateDraftBtn');
    const alertBox = document.getElementById('validationResultAlert');
    if (validateDraftBtn) {
        validateDraftBtn.addEventListener('click', function () {
            const form = document.getElementById('invoiceForm');
            const formData = new FormData(form);

            validateDraftBtn.disabled = true;
            validateDraftBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Validating...';
            alertBox.style.display = 'none';

            fetch(config.validateUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': config.csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(res => {
                validateDraftBtn.disabled = false;
                validateDraftBtn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Validate with FBR';
                alertBox.style.display = 'block';

                if (res.status === 200 && res.body.valid) {
                    alertBox.className = 'alert alert-success py-2 px-3 small rounded-3';
                    alertBox.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> <strong>Validation Passed:</strong> ${res.body.message}`;
                } else {
                    alertBox.className = 'alert alert-danger py-2 px-3 small rounded-3';
                    const errs = res.body.errors || [res.body.message || 'Validation failed.'];
                    const list = Array.isArray(errs) ? errs.map(e => `<li>${e}</li>`).join('') : `<li>${errs}</li>`;
                    alertBox.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Validation Errors:</strong><ul class="mb-0 ps-3 mt-1">${list}</ul>`;
                }
                alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            })
            .catch(() => {
                validateDraftBtn.disabled = false;
                validateDraftBtn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Validate with FBR';
                alertBox.style.display = 'block';
                alertBox.className = 'alert alert-danger py-2 px-3 small rounded-3';
                alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Network error occurred while validating.';
            });
        });
    }

    // Initialize first item card on load
    addItem();
});
</script>
@endpush
