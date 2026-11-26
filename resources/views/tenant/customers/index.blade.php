@extends('layouts.tenant')

@section('title', 'Customers')

@push('styles')
<style>
    .cust-hero-header {
        background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFF 100%);
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    }
    .cust-hero-icon {
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
    .cust-kpi-card {
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
    .cust-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        border-color: #CBD5E1;
    }
    .cust-kpi-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748B;
        margin-bottom: 4px;
    }
    .cust-kpi-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 22px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.15;
    }
    .cust-kpi-sub {
        font-size: 11.5px;
        color: #64748B;
        margin-top: 3px;
    }
    .cust-kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        flex-shrink: 0;
    }
    .cust-directory-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.035);
        overflow: hidden;
    }
    .cust-toolbar {
        padding: 16px 20px;
        background: #FAFBFD;
        border-bottom: 1px solid #E2E8F0;
    }
    .cust-search-wrap {
        position: relative;
    }
    .cust-search-wrap .bi-search {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 14px;
        pointer-events: none;
    }
    .cust-search-wrap input {
        padding-left: 36px;
        border-radius: 10px;
        border-color: #CBD5E1;
        font-size: 13.5px;
    }
    .cust-search-wrap input:focus,
    .cust-filter-select:focus {
        border-color: #1E50FF;
        box-shadow: 0 0 0 3px rgba(30, 80, 255, 0.12);
    }
    .cust-filter-select {
        border-radius: 10px;
        border-color: #CBD5E1;
        font-size: 13px;
    }
    .cust-table thead th {
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
    .cust-table tbody tr {
        transition: background-color 0.12s ease;
    }
    .cust-table tbody tr:hover {
        background-color: #F8FAFF;
    }
    .cust-table tbody td {
        padding: 14px;
        vertical-align: middle;
        border-bottom: 1px solid #F1F5F9;
        font-size: 13.5px;
    }
    .cust-avatar {
        width: 40px;
        height: 40px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        font-size: 14px;
        flex-shrink: 0;
    }
    .cust-avatar.registered {
        background: #ECFDF5;
        color: #059669;
        border: 1px solid #A7F3D0;
    }
    .cust-avatar.unregistered {
        background: #FFFBEB;
        color: #D97706;
        border: 1px solid #FDE68A;
    }
    .cust-ntn-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 9px;
        border-radius: 7px;
        background: #F1F5F9;
        color: #0F172A;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #E2E8F0;
    }
    .cust-province-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 8px;
        background: #F8FAFC;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #E2E8F0;
    }
    .cust-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 11px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
        white-space: nowrap;
    }
    .cust-status-pill.registered {
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
    }
    .cust-status-pill.unregistered {
        background: #FFFBEB;
        color: #B45309;
        border: 1px solid #FDE68A;
    }
    .cust-action-btn {
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
    .cust-action-btn.view:hover {
        background: #EFF6FF;
        border-color: #BFDBFE;
        color: #1E50FF;
    }
    .cust-action-btn.edit:hover {
        background: #F1F5F9;
        border-color: #CBD5E1;
        color: #0F172A;
    }
    .cust-action-btn.delete:hover {
        background: #FEF2F2;
        border-color: #FECACA;
        color: #DC2626;
    }
    .cust-modal-section-label {
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
        font-size: 13.5px;
        font-weight: 700;
        color: #0F172A;
    }
</style>
@endpush

@section('content')
<!-- Executive Page Header -->
<div class="cust-hero-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="cust-hero-icon">
            <i class="bi bi-people-fill"></i>
        </div>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="font-display fw-bold text-dark fs-3 mb-0">Customers</h1>
                <span class="badge rounded-pill" style="background: #EFF6FF; color: #1E50FF; border: 1px solid #DBEAFE; font-size: 11.5px; font-weight: 700;">
                    {{ number_format($stats['total'] ?? $customers->total()) }} {{ ($stats['total'] ?? 0) === 1 ? 'Buyer' : 'Buyers' }}
                </span>
            </div>
            <div class="text-muted small mt-1">
                {{ $registeredCount ?? 0 }} registered · {{ $unregisteredCount ?? 0 }} unregistered buyers
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('tenant.invoices.create') }}" class="btn btn-ig-outline d-flex align-items-center gap-2" style="border-radius: 10px; font-size: 13.5px;">
            <i class="bi bi-file-earmark-plus text-primary"></i>
            <span>New Invoice</span>
        </a>
        <button type="button" class="btn btn-ig-primary d-flex align-items-center gap-2 px-3 py-2 shadow-sm" style="border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="bi bi-plus-lg"></i>
            <span>Add customer</span>
        </button>
    </div>
</div>

<!-- 4 KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="cust-kpi-card">
            <div>
                <div class="cust-kpi-label">Total Buyers</div>
                <div class="cust-kpi-value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="cust-kpi-sub">Saved buyer profiles</div>
            </div>
            <div class="cust-kpi-icon" style="background: #EFF6FF; color: #1E50FF;">
                <i class="bi bi-person-lines-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="cust-kpi-card">
            <div>
                <div class="cust-kpi-label">Registered (ATL)</div>
                <div class="cust-kpi-value text-success">{{ number_format($registeredCount ?? 0) }}</div>
                <div class="cust-kpi-sub">NTN active taxpayers</div>
            </div>
            <div class="cust-kpi-icon" style="background: #ECFDF5; color: #059669;">
                <i class="bi bi-patch-check-fill"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="cust-kpi-card">
            <div>
                <div class="cust-kpi-label">Unregistered</div>
                <div class="cust-kpi-value" style="color: #D97706;">{{ number_format($unregisteredCount ?? 0) }}</div>
                <div class="cust-kpi-sub">4% Further Tax applicable</div>
            </div>
            <div class="cust-kpi-icon" style="background: #FFFBEB; color: #D97706;">
                <i class="bi bi-person-exclamation"></i>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="cust-kpi-card">
            <div>
                <div class="cust-kpi-label">STRN / Regions</div>
                <div class="cust-kpi-value">{{ number_format($stats['strn'] ?? 0) }}</div>
                <div class="cust-kpi-sub">Across {{ number_format($stats['provinces'] ?? 0) }} {{ ($stats['provinces'] ?? 0) === 1 ? 'province' : 'provinces' }}</div>
            </div>
            <div class="cust-kpi-icon" style="background: #F0F9FF; color: #0284C7;">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
        </div>
    </div>
</div>

<!-- Customers Directory Card -->
<div class="cust-directory-card">
    <!-- Filter & Search Toolbar -->
    <div class="cust-toolbar">
        <form method="GET" action="{{ route('tenant.customers.index') }}">
            <div class="row g-2 align-items-center">
                <div class="col-lg-4 col-md-6">
                    <div class="cust-search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" class="form-control" placeholder="Search name, NTN/CNIC, phone, email" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <select name="type" class="form-select cust-filter-select" onchange="this.form.submit()">
                        <option value="">All types</option>
                        <option value="registered" {{ request('type') === 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="unregistered" {{ request('type') === 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <select name="province" class="form-select cust-filter-select" onchange="this.form.submit()">
                        <option value="">All Provinces</option>
                        @foreach ($provinces as $val => $label)
                            <option value="{{ $val }}" {{ request('province') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <select name="sort" class="form-select cust-filter-select" onchange="this.form.submit()">
                        <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name: A &rarr; Z</option>
                        <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name: Z &rarr; A</option>
                    </select>
                </div>
                <div class="col-lg-2 d-flex align-items-center justify-content-lg-end gap-2">
                    <button type="submit" class="btn btn-ig-primary px-3 py-2" style="border-radius: 10px; font-size: 13px;">
                        <i class="bi bi-funnel me-1"></i> Search
                    </button>
                    @if (request('search') || request('type') || request('province'))
                        <a href="{{ route('tenant.customers.index') }}" class="btn btn-ig-outline px-2 py-2" style="border-radius: 10px; font-size: 13px;" title="Reset Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @if ($customers->isEmpty())
        <!-- Enhanced Empty State -->
        <div class="text-center py-5 my-4 px-3">
            <div class="mx-auto mb-3 rounded-4 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); border: 1px solid #BFDBFE;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#1E50FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <h5 class="fw-bold text-dark fs-5 mb-1">No customers yet</h5>
            <p class="text-muted small mx-auto mb-3" style="max-width: 380px;">
                Add buyers once and reuse them on invoices.
            </p>
            <button type="button" class="btn btn-ig-primary px-4 py-2" style="border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="bi bi-plus-lg me-1"></i> Add Your First Customer
            </button>
        </div>
    @else
        <!-- Customers Table -->
        <div class="table-responsive">
            <table class="table cust-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Customer / Buyer</th>
                        <th>NTN / CNIC &amp; STRN</th>
                        <th>Province</th>
                        <th>Contact Details</th>
                        <th>Tax Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $c)
                        @php
                            $words = preg_split('/\s+/', trim($c->name));
                            $initials = strtoupper(substr($words[0] ?? 'C', 0, 1) . substr($words[1] ?? '', 0, 1));
                            $isReg = $c->isRegistered();
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="cust-avatar {{ $isReg ? 'registered' : 'unregistered' }}">
                                        {{ $initials }}
                                    </div>
                                    <div style="min-width: 0;">
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <span class="fw-bold text-dark" style="font-size: 14px;">{{ $c->name }}</span>
                                            @if (!empty($c->category))
                                                <span class="badge rounded-pill" style="background: #EFF6FF; color: #1E50FF; font-size: 10.5px; font-weight: 700;">{{ $c->category }}</span>
                                            @endif
                                        </div>
                                        @if (!empty($c->address))
                                            <div class="text-muted small text-truncate" style="max-width: 290px;" title="{{ $c->address }}">
                                                <i class="bi bi-building me-1" style="font-size: 11px;"></i>{{ $c->address }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if (!empty($c->ntn_cnic))
                                    <span class="cust-ntn-pill">
                                        <i class="bi bi-card-heading text-primary" style="font-size: 11px;"></i>
                                        {{ $c->ntn_cnic }}
                                    </span>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                                @if (!empty($c->strn))
                                    <div class="text-muted font-monospace mt-1" style="font-size: 11px;">STRN: {{ $c->strn }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="cust-province-pill">
                                    <i class="bi bi-geo-alt text-primary" style="font-size: 11px;"></i>
                                    {{ $c->province ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @if (!empty($c->phone))
                                    <div class="text-dark fw-medium small">
                                        <i class="bi bi-telephone text-muted me-1" style="font-size: 11px;"></i>{{ $c->phone }}
                                    </div>
                                @endif
                                @if (!empty($c->email))
                                    <div class="text-muted" style="font-size: 11.5px;">
                                        <i class="bi bi-envelope me-1" style="font-size: 11px;"></i>{{ $c->email }}
                                    </div>
                                @endif
                                @if (empty($c->phone) && empty($c->email))
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                @if ($isReg)
                                    <span class="cust-status-pill registered">
                                        <i class="bi bi-check-circle-fill" style="font-size: 11px;"></i>
                                        Registered
                                    </span>
                                @else
                                    <span class="cust-status-pill unregistered">
                                        <i class="bi bi-exclamation-circle-fill" style="font-size: 11px;"></i>
                                        Unregistered
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" class="cust-action-btn view view-customer-btn"
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
                                    <button type="button" class="cust-action-btn edit edit-customer-btn"
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
                                        <button type="submit" class="cust-action-btn delete" title="Delete Customer">
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
                Showing <strong>{{ $customers->firstItem() }}</strong> to <strong>{{ $customers->lastItem() }}</strong> of <strong>{{ $customers->total() }}</strong> {{ $customers->total() === 1 ? 'buyer' : 'buyers' }}
            </div>
            @if ($customers->hasPages())
                <div>
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

<!-- ================= ADD CUSTOMER MODAL ================= -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-bottom py-3 px-4" style="background: #F8FAFF;">
                <div class="d-flex align-items-center gap-3">
                    <div class="cust-avatar registered" style="background: #EFF6FF; color: #1E50FF; border-color: #DBEAFE; width: 38px; height: 38px;">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-display fw-bold text-dark fs-5 mb-0" id="addCustomerModalLabel">Add customer</h5>
                        <div class="text-muted" style="font-size: 12px;">Save buyer NTN/CNIC and FBR registration status for instant invoice autofill</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('tenant.customers.store') }}">
                @csrf
                <div class="modal-body px-4 py-4">
                    <div class="cust-modal-section-label">1. Buyer Identity &amp; Tax Registration</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="cust_name" class="form-label fw-semibold small">Customer / business name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cust_name" name="name" value="{{ old('name') }}" placeholder="e.g. Apex Logistics Pvt Ltd" required>
                        </div>
                        <div class="col-md-6">
                            <label for="cust_reg" class="form-label fw-semibold small">Registration <span class="text-danger">*</span></label>
                            <select class="form-select" id="cust_reg" name="registration_type" required>
                                <option value="unregistered" {{ old('registration_type', 'unregistered') === 'unregistered' ? 'selected' : '' }}>Unregistered (4% Further Tax)</option>
                                <option value="registered" {{ old('registration_type') === 'registered' ? 'selected' : '' }}>Registered (FBR ATL Active)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="cust_ntn" class="form-label fw-semibold small">NTN / CNIC</label>
                            <input type="text" class="form-control font-monospace" id="cust_ntn" name="ntn_cnic" value="{{ old('ntn_cnic') }}" placeholder="7-digit NTN or 13-digit CNIC">
                            <div class="form-text" style="font-size: 11.5px;">7-digit NTN or 13-digit CNIC; required for registered buyers</div>
                        </div>
                        <div class="col-md-6">
                            <label for="cust_strn" class="form-label fw-semibold small">STRN</label>
                            <input type="text" class="form-control font-monospace" id="cust_strn" name="strn" value="{{ old('strn') }}" placeholder="Sales Tax Registration Number">
                        </div>
                    </div>

                    <div class="cust-modal-section-label">2. Location &amp; Category</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="cust_province" class="form-label fw-semibold small">Province <span class="text-danger">*</span></label>
                            <select class="form-select" id="cust_province" name="province" required>
                                @foreach ($provinces as $val => $label)
                                    <option value="{{ $val }}" {{ old('province', 'Punjab') == $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="cust_cat" class="form-label fw-semibold small">Category</label>
                            <input type="text" class="form-control" id="cust_cat" name="category" value="{{ old('category') }}" placeholder="e.g. Wholesale, Corporate, Distributor">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="cust_addr" class="form-label fw-semibold small">Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cust_addr" name="address" value="{{ old('address') }}" placeholder="Office / Shop / Warehouse address" required>
                    </div>

                    <div class="cust-modal-section-label">3. Contact Information</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="cust_phone" class="form-label fw-semibold small">Phone</label>
                            <input type="text" class="form-control" id="cust_phone" name="phone" value="{{ old('phone') }}" placeholder="03001234567">
                        </div>
                        <div class="col-md-6">
                            <label for="cust_email" class="form-label fw-semibold small">Email</label>
                            <input type="email" class="form-control" id="cust_email" name="email" value="{{ old('email') }}" placeholder="accounts@company.pk">
                        </div>
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

<!-- ================= EDIT CUSTOMER MODAL ================= -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-bottom py-3 px-4" style="background: #F8FAFF;">
                <div class="d-flex align-items-center gap-3">
                    <div class="cust-avatar registered" style="background: #EFF6FF; color: #1E50FF; border-color: #DBEAFE; width: 38px; height: 38px;">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-display fw-bold text-dark fs-5 mb-0" id="editCustomerModalLabel">Edit customer</h5>
                        <div class="text-muted" style="font-size: 12px;">Update buyer tax details, address, or contact info</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="" id="editCustomerForm">
                @csrf
                @method('PUT')
                <div class="modal-body px-4 py-4">
                    <div class="cust-modal-section-label">1. Buyer Identity &amp; Tax Registration</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Customer / business name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_cust_name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Registration <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_cust_reg" name="registration_type" required>
                                <option value="unregistered">Unregistered</option>
                                <option value="registered">Registered</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">NTN / CNIC</label>
                            <input type="text" class="form-control font-monospace" id="edit_cust_ntn" name="ntn_cnic">
                            <div class="form-text" style="font-size: 11.5px;">7-digit NTN or 13-digit CNIC; required for registered buyers</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">STRN</label>
                            <input type="text" class="form-control font-monospace" id="edit_cust_strn" name="strn">
                        </div>
                    </div>

                    <div class="cust-modal-section-label">2. Location &amp; Category</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Province <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_cust_province" name="province" required>
                                @foreach ($provinces as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Category</label>
                            <input type="text" class="form-control" id="edit_cust_cat" name="category" placeholder="e.g. Wholesale">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_cust_addr" name="address" required>
                    </div>

                    <div class="cust-modal-section-label">3. Contact Information</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Phone</label>
                            <input type="text" class="form-control" id="edit_cust_phone" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" class="form-control" id="edit_cust_email" name="email">
                        </div>
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

<!-- ================= VIEW CUSTOMER MODAL ================= -->
<div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-labelledby="viewCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-bottom py-3 px-4" style="background: #F8FAFF;">
                <div class="d-flex align-items-center gap-3">
                    <div class="cust-avatar registered" style="background: #EFF6FF; color: #1E50FF; border-color: #DBEAFE; width: 40px; height: 40px;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-display fw-bold text-dark fs-5 mb-0" id="view_cust_name">Buyer Details</h5>
                        <div class="mt-1" id="view_cust_reg"></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="view-stat-chip">
                            <div class="view-stat-chip-label">NTN / CNIC</div>
                            <div class="view-stat-chip-val font-monospace text-primary" id="view_cust_ntn"></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="view-stat-chip">
                            <div class="view-stat-chip-label">STRN</div>
                            <div class="view-stat-chip-val font-monospace" id="view_cust_strn"></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="view-stat-chip">
                            <div class="view-stat-chip-label">Province</div>
                            <div class="view-stat-chip-val" id="view_cust_province"></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="view-stat-chip">
                            <div class="view-stat-chip-label">Category</div>
                            <div class="view-stat-chip-val" id="view_cust_cat"></div>
                        </div>
                    </div>
                </div>

                <table class="table table-sm table-borderless mb-0 small">
                    <tr><th class="text-muted fw-semibold" style="width: 30%;">Address:</th><td class="fw-medium text-dark" id="view_cust_addr"></td></tr>
                    <tr><th class="text-muted fw-semibold">Phone:</th><td class="fw-medium text-dark" id="view_cust_phone"></td></tr>
                    <tr><th class="text-muted fw-semibold">Email:</th><td class="fw-medium text-dark" id="view_cust_email"></td></tr>
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
    const viewButtons = document.querySelectorAll('.view-customer-btn');
    const viewModal = new bootstrap.Modal(document.getElementById('viewCustomerModal'));

    viewButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('view_cust_name').textContent = this.getAttribute('data-name') || 'N/A';
            const reg = this.getAttribute('data-reg') || 'unregistered';
            document.getElementById('view_cust_reg').innerHTML = reg === 'registered'
                ? '<span class="cust-status-pill registered"><i class="bi bi-check-circle-fill" style="font-size: 10px;"></i> Registered</span>'
                : '<span class="cust-status-pill unregistered"><i class="bi bi-exclamation-circle-fill" style="font-size: 10px;"></i> Unregistered</span>';
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
            document.getElementById('edit_cust_province').value = this.getAttribute('data-province') || 'Punjab';
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
