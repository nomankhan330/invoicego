@extends('layouts.tenant')

@section('title', 'Customers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">Customers</h1>
        <div class="text-muted small">
            {{ $registeredCount ?? 0 }} registered · {{ $unregisteredCount ?? 0 }} unregistered buyers
        </div>
    </div>
    <button type="button" class="btn btn-ig-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
        <i class="bi bi-plus-lg"></i>
        <span>Add customer</span>
    </button>
</div>

<!-- Search & Content Card (Matching Screenshot 4) -->
<div class="ig-content-card">
    <form method="GET" action="{{ route('tenant.customers.index') }}" class="mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Search name, NTN/CNIC, phone, email" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">All types</option>
                    <option value="registered" {{ request('type') === 'registered' ? 'selected' : '' }}>Registered</option>
                    <option value="unregistered" {{ request('type') === 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-ig-outline px-4">Search</button>
            </div>
            @if (request('search') || request('type'))
                <div class="col-auto">
                    <a href="{{ route('tenant.customers.index') }}" class="btn btn-link text-secondary text-decoration-none">Clear</a>
                </div>
            @endif
        </div>
    </form>

    @if ($customers->isEmpty())
        <!-- Empty State (Matching Screenshot 4) -->
        <div class="text-center py-5 my-4">
            <div class="mx-auto mb-3 rounded-4 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #F5F3FF;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <h5 class="fw-bold text-dark fs-6 mb-1">No customers yet</h5>
            <p class="text-muted small mx-auto" style="max-width: 380px;">
                Add buyers once and reuse them on invoices.
            </p>
        </div>
    @else
        <!-- Customers Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.05em;">
                    <tr>
                        <th class="py-3 px-3">Name</th>
                        <th class="py-3">NTN / CNIC</th>
                        <th class="py-3">Business</th>
                        <th class="py-3">Province</th>
                        <th class="py-3">Phone</th>
                        <th class="py-3">Type</th>
                        <th class="py-3 text-end px-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $c)
                        <tr style="font-size: 13.5px;">
                            <td class="py-3 px-3 fw-bold text-dark">{{ $c->name }}</td>
                            <td class="py-3 text-secondary font-monospace">{{ $c->ntn_cnic ?? '&mdash;' }}</td>
                            <td class="py-3 text-dark">{{ $c->category ?? $c->name }}</td>
                            <td class="py-3 text-muted small">{{ $c->province ?? 'N/A' }}</td>
                            <td class="py-3 text-muted small">{{ $c->phone ?? ($c->email ?? '&mdash;') }}</td>
                            <td class="py-3">
                                @if ($c->isRegistered())
                                    <span class="badge bg-success bg-opacity-10 text-success">Registered</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning">Unregistered</span>
                                @endif
                            </td>
                            <td class="py-3 text-end px-3">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary view-customer-btn"
                                        data-name="{{ $c->name }}"
                                        data-reg="{{ $c->registration_type }}"
                                        data-ntn="{{ $c->ntn_cnic }}"
                                        data-strn="{{ $c->strn }}"
                                        data-province="{{ $c->province }}"
                                        data-cat="{{ $c->category }}"
                                        data-addr="{{ $c->address }}"
                                        data-phone="{{ $c->phone }}"
                                        data-email="{{ $c->email }}"
                                        title="View Customer Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary edit-customer-btn"
                                        data-id="{{ $c->id }}"
                                        data-name="{{ $c->name }}"
                                        data-reg="{{ $c->registration_type }}"
                                        data-ntn="{{ $c->ntn_cnic }}"
                                        data-strn="{{ $c->strn }}"
                                        data-province="{{ $c->province }}"
                                        data-cat="{{ $c->category }}"
                                        data-addr="{{ $c->address }}"
                                        data-phone="{{ $c->phone }}"
                                        data-email="{{ $c->email }}"
                                        title="Edit Customer">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('tenant.customers.destroy', $c->id) }}" class="d-inline" onsubmit="return confirm('Delete customer {{ $c->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete Customer">
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

        @if ($customers->hasPages())
            <div class="pt-3 border-top mt-3">
                {{ $customers->links() }}
            </div>
        @endif
    @endif
</div>

<!-- ================= ADD CUSTOMER MODAL (Screenshot 3) ================= -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title font-display fw-bold text-dark fs-5" id="addCustomerModalLabel">Add customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('tenant.customers.store') }}">
                @csrf
                <div class="modal-body px-4 py-3">
                    <!-- Row 1: Name & Registration -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="cust_name" class="form-label">Customer / business name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cust_name" name="name" value="{{ old('name') }}" placeholder="" required>
                        </div>
                        <div class="col-md-6">
                            <label for="cust_reg" class="form-label">Registration <span class="text-danger">*</span></label>
                            <select class="form-select" id="cust_reg" name="registration_type" required>
                                <option value="unregistered" {{ old('registration_type', 'unregistered') === 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                                <option value="registered" {{ old('registration_type') === 'registered' ? 'selected' : '' }}>Registered</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: NTN/CNIC & STRN -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="cust_ntn" class="form-label">NTN / CNIC</label>
                            <input type="text" class="form-control" id="cust_ntn" name="ntn_cnic" value="{{ old('ntn_cnic') }}" placeholder="">
                            <div class="form-text" style="font-size: 11.5px;">7-digit NTN or 13-digit CNIC; required for registered buyers</div>
                        </div>
                        <div class="col-md-6">
                            <label for="cust_strn" class="form-label">STRN</label>
                            <input type="text" class="form-control" id="cust_strn" name="strn" value="{{ old('strn') }}" placeholder="">
                        </div>
                    </div>

                    <!-- Row 3: Province & Category -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="cust_province" class="form-label">Province <span class="text-danger">*</span></label>
                            <select class="form-select" id="cust_province" name="province" required>
                                @foreach ($provinces as $val => $label)
                                    <option value="{{ $val }}" {{ old('province', 'AZAD JAMMU AND KASHMIR') == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="cust_cat" class="form-label">Category</label>
                            <input type="text" class="form-control" id="cust_cat" name="category" value="{{ old('category') }}" placeholder="e.g. Wholesale">
                        </div>
                    </div>

                    <!-- Row 4: Address -->
                    <div class="mb-3">
                        <label for="cust_addr" class="form-label">Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cust_addr" name="address" value="{{ old('address') }}" placeholder="" required>
                    </div>

                    <!-- Row 5: Phone & Email -->
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label for="cust_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="cust_phone" name="phone" value="{{ old('phone') }}" placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label for="cust_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="cust_email" name="email" value="{{ old('email') }}" placeholder="">
                        </div>
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

<!-- ================= EDIT CUSTOMER MODAL ================= -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title font-display fw-bold text-dark fs-5" id="editCustomerModalLabel">Edit customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="" id="editCustomerForm">
                @csrf
                @method('PUT')
                <div class="modal-body px-4 py-3">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer / business name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_cust_name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Registration <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_cust_reg" name="registration_type" required>
                                <option value="unregistered">Unregistered</option>
                                <option value="registered">Registered</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">NTN / CNIC</label>
                            <input type="text" class="form-control" id="edit_cust_ntn" name="ntn_cnic">
                            <div class="form-text" style="font-size: 11.5px;">7-digit NTN or 13-digit CNIC; required for registered buyers</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">STRN</label>
                            <input type="text" class="form-control" id="edit_cust_strn" name="strn">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Province <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_cust_province" name="province" required>
                                @foreach ($provinces as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" id="edit_cust_cat" name="category" placeholder="e.g. Wholesale">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_cust_addr" name="address" required>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" id="edit_cust_phone" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_cust_email" name="email">
                        </div>
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

<!-- ================= VIEW CUSTOMER MODAL ================= -->
<div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-labelledby="viewCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title font-display fw-bold text-dark fs-5" id="viewCustomerModalLabel">Buyer / Customer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th class="text-muted small" style="width: 35%;">Buyer Name:</th><td class="fw-bold" id="view_cust_name"></td></tr>
                    <tr><th class="text-muted small">Type:</th><td id="view_cust_reg"></td></tr>
                    <tr><th class="text-muted small">NTN / CNIC:</th><td class="font-monospace" id="view_cust_ntn"></td></tr>
                    <tr><th class="text-muted small">STRN:</th><td class="font-monospace" id="view_cust_strn"></td></tr>
                    <tr><th class="text-muted small">Province:</th><td id="view_cust_province"></td></tr>
                    <tr><th class="text-muted small">Category/Business:</th><td id="view_cust_cat"></td></tr>
                    <tr><th class="text-muted small">Address:</th><td id="view_cust_addr"></td></tr>
                    <tr><th class="text-muted small">Phone:</th><td id="view_cust_phone"></td></tr>
                    <tr><th class="text-muted small">Email:</th><td id="view_cust_email"></td></tr>
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
    const viewButtons = document.querySelectorAll('.view-customer-btn');
    const viewModal = new bootstrap.Modal(document.getElementById('viewCustomerModal'));

    viewButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('view_cust_name').textContent = this.getAttribute('data-name') || 'N/A';
            const reg = this.getAttribute('data-reg') || 'unregistered';
            document.getElementById('view_cust_reg').innerHTML = `<span class="badge ${reg === 'registered' ? 'bg-success' : 'bg-warning'} bg-opacity-10 ${reg === 'registered' ? 'text-success' : 'text-warning'}">${reg}</span>`;
            document.getElementById('view_cust_ntn').textContent = this.getAttribute('data-ntn') || '—';
            document.getElementById('view_cust_strn').textContent = this.getAttribute('data-strn') || '—';
            document.getElementById('view_cust_province').textContent = this.getAttribute('data-province') || '—';
            document.getElementById('view_cust_cat').textContent = this.getAttribute('data-cat') || '—';
            document.getElementById('view_cust_addr').textContent = this.getAttribute('data-addr') || '—';
            document.getElementById('view_cust_phone').textContent = this.getAttribute('data-phone') || '—';
            document.getElementById('view_cust_email').textContent = this.getAttribute('data-email') || '—';

            viewModal.show();
        });
    });

    const editButtons = document.querySelectorAll('.edit-customer-btn');
    const editForm = document.getElementById('editCustomerForm');
    const editModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));

    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            editForm.action = `/customers/${id}`;

            document.getElementById('edit_cust_name').value = this.getAttribute('data-name') || '';
            document.getElementById('edit_cust_reg').value = this.getAttribute('data-reg') || 'unregistered';
            document.getElementById('edit_cust_ntn').value = this.getAttribute('data-ntn') || '';
            document.getElementById('edit_cust_strn').value = this.getAttribute('data-strn') || '';
            document.getElementById('edit_cust_province').value = this.getAttribute('data-province') || 'AZAD JAMMU AND KASHMIR';
            document.getElementById('edit_cust_cat').value = this.getAttribute('data-cat') || '';
            document.getElementById('edit_cust_addr').value = this.getAttribute('data-addr') || '';
            document.getElementById('edit_cust_phone').value = this.getAttribute('data-phone') || '';
            document.getElementById('edit_cust_email').value = this.getAttribute('data-email') || '';

            editModal.show();
        });
    });
});
</script>
@endpush
@endsection
