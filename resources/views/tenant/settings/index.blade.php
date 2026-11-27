@extends('layouts.tenant')

@section('title', 'Settings')

@push('styles')
<style>
    .ig-settings-tabs {
        display: flex;
        align-items: center;
        gap: 28px;
        border-bottom: 1px solid #E2E8F0;
        margin-bottom: 24px;
    }

    .ig-settings-tab {
        position: relative;
        background: transparent;
        border: 0;
        padding: 10px 2px 12px;
        font-size: 14px;
        font-weight: 600;
        color: #64748B;
        cursor: pointer;
        transition: color 0.15s ease;
    }

    .ig-settings-tab:hover {
        color: #1E293B;
    }

    .ig-settings-tab.active {
        color: #1E50FF;
    }

    .ig-settings-tab.active::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: -1px;
        height: 2.5px;
        border-radius: 999px;
        background: #1E50FF;
    }

    .ig-settings-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 24px 26px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }

    .ig-settings-card-title {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        font-size: 15.5px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 18px;
    }

    .ig-settings-label {
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
        display: block;
    }

    .ig-settings-label .req {
        color: #EF4444;
    }

    .ig-checkbox-box {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 16px 18px;
        height: 100%;
    }

    .ig-check-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 14px;
    }

    .ig-check-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px 12px;
    }

    @media (max-width: 991.98px) {
        .ig-check-grid-3 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .ig-check-grid-2,
        .ig-check-grid-3 {
            grid-template-columns: 1fr;
        }
    }

    .ig-check-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 13px;
        color: #334155;
        cursor: pointer;
        user-select: none;
        line-height: 1.35;
    }

    .ig-check-item input[type="checkbox"] {
        margin-top: 2px;
        width: 15px;
        height: 15px;
        accent-color: #1E50FF;
        flex-shrink: 0;
        cursor: pointer;
    }

    .ig-check-item:has(input:checked) {
        color: #1E50FF;
        font-weight: 600;
    }

    .ig-btn-royal {
        background: #1E50FF;
        border: 1px solid #1E50FF;
        color: #FFFFFF;
        font-size: 13.5px;
        font-weight: 600;
        padding: 9px 20px;
        border-radius: 9px;
        transition: background 0.15s ease, box-shadow 0.15s ease;
    }

    .ig-btn-royal:hover {
        background: #1541D9;
        border-color: #1541D9;
        color: #FFFFFF;
        box-shadow: 0 4px 12px rgba(30, 80, 255, 0.22);
    }

    .ig-ip-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 12px;
        font-weight: 600;
        background: #EFF6FF;
        color: #1E40AF;
        border: 1px solid #BFDBFE;
        padding: 3px 10px;
        border-radius: 999px;
    }

    .ig-activity-table th {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748B;
        background: #F8FAFC;
        border-bottom: 1px solid #E2E8F0;
        padding: 10px 14px;
    }

    .ig-activity-table td {
        font-size: 13px;
        color: #334155;
        vertical-align: middle;
        padding: 11px 14px;
        border-bottom: 1px solid #F1F5F9;
    }

    .ig-sec-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .ig-sec-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 22px;
        border-bottom: 1px solid #F1F5F9;
    }

    .ig-sec-card-header h2 {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: #0F172A;
        margin: 0;
    }

    .ig-sec-card-body {
        padding: 20px 22px 22px;
        flex-grow: 1;
    }

    .ig-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 600;
        padding: 3px 11px;
        border-radius: 999px;
    }

    .ig-status-pill.is-off {
        background: #F1F5F9;
        color: #475569;
        border: 1px solid #E2E8F0;
    }

    .ig-status-pill.is-on {
        background: #ECFDF5;
        color: #047857;
        border: 1px solid #A7F3D0;
    }

    .ig-qr-wrap {
        display: inline-flex;
        padding: 12px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
    }

    .ig-recovery-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .ig-recovery-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 13px;
        font-weight: 600;
        color: #0F172A;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 7px 10px;
        text-align: center;
        letter-spacing: 0.06em;
    }
</style>
@endpush

@section('content')
<div class="mb-3">
    <h1 class="font-display fw-bold text-dark fs-3 mb-1">Settings</h1>
</div>

<!-- 2-Tab Navigation (Business profile | Account & security) -->
<div class="ig-settings-tabs" role="tablist" aria-label="Settings tabs">
    <button
        type="button"
        id="tab-btn-profile"
        class="ig-settings-tab {{ $activeTab === 'profile' ? 'active' : '' }}"
        data-target-tab="profile"
        role="tab"
        aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}"
    >
        Business profile
    </button>
    <button
        type="button"
        id="tab-btn-security"
        class="ig-settings-tab {{ $activeTab === 'security' ? 'active' : '' }}"
        data-target-tab="security"
        role="tab"
        aria-selected="{{ $activeTab === 'security' ? 'true' : 'false' }}"
    >
        Account &amp; security
    </button>
</div>

@if ($errors->any())
    <div class="alert alert-danger small mb-4 py-2 px-3">
        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i> Please fix the following issues:</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- TAB 1: BUSINESS PROFILE -->
<div id="tab-pane-profile" class="{{ $activeTab === 'profile' ? '' : 'd-none' }}">
    <div class="ig-settings-card mb-4">
        <h2 class="ig-settings-card-title">Business details</h2>

        <form method="POST" action="{{ route('tenant.settings.update') }}">
            @csrf
            <input type="hidden" name="profile_form" value="1">

            <!-- Row 1: Business name, NTN/CNIC, STRN -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="business_name" class="ig-settings-label">Business name <span class="req">*</span></label>
                    <input
                        id="business_name"
                        type="text"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $business->name) }}"
                        required
                    >
                </div>
                <div class="col-md-4">
                    <label for="business_ntn" class="ig-settings-label">NTN / CNIC <span class="req">*</span></label>
                    <input
                        id="business_ntn"
                        type="text"
                        class="form-control bg-light text-secondary"
                        value="{{ $business->ntn_cnic }}"
                        readonly
                    >
                    <div class="form-text">Used as sellerNTNCNIC on every invoice</div>
                </div>
                <div class="col-md-4">
                    <label for="business_strn" class="ig-settings-label">STRN</label>
                    <input
                        id="business_strn"
                        type="text"
                        name="strn"
                        class="form-control @error('strn') is-invalid @enderror"
                        value="{{ old('strn', $business->strn) }}"
                        placeholder="e.g. 3277876123456"
                    >
                </div>
            </div>

            <!-- Row 2: Business address & Province -->
            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label for="business_address" class="ig-settings-label">Business address <span class="req">*</span></label>
                    <input
                        id="business_address"
                        type="text"
                        name="address"
                        class="form-control @error('address') is-invalid @enderror"
                        value="{{ old('address', $business->address) }}"
                        required
                    >
                </div>
                <div class="col-md-4">
                    <label for="business_province" class="ig-settings-label">Province <span class="req">*</span></label>
                    @php
                        $currentProvince = old('province', $business->province);
                    @endphp
                    <select id="business_province" name="province" class="form-select @error('province') is-invalid @enderror" required>
                        @foreach ($provinces as $province)
                            <option value="{{ $province }}" {{ $currentProvince === $province ? 'selected' : '' }}>{{ $province }}</option>
                        @endforeach
                        @if ($currentProvince && !in_array($currentProvince, $provinces, true))
                            <option value="{{ $currentProvince }}" selected>{{ $currentProvince }}</option>
                        @endif
                    </select>
                </div>
            </div>

            <!-- Row 3: City, Phone, Email -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="business_city" class="ig-settings-label">City</label>
                    <input
                        id="business_city"
                        type="text"
                        name="city"
                        class="form-control @error('city') is-invalid @enderror"
                        value="{{ old('city', $business->city) }}"
                        placeholder="e.g. Lahore, Karachi, Islamabad"
                    >
                </div>
                <div class="col-md-4">
                    <label for="owner_contact_phone" class="ig-settings-label">Phone</label>
                    <input
                        id="owner_contact_phone"
                        type="text"
                        name="phone"
                        class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone', $user->phone) }}"
                        placeholder="03XX-XXXXXXX"
                    >
                </div>
                <div class="col-md-4">
                    <label for="owner_contact_email" class="ig-settings-label">Email</label>
                    <input
                        id="owner_contact_email"
                        type="email"
                        class="form-control bg-light text-secondary"
                        value="{{ $user->email }}"
                        readonly
                    >
                </div>
            </div>

            <!-- Row 4: Business Nature (multi-checkbox) + Sector (16 official multi-checkboxes) -->
            @php
                $checkedNatures = old('business_natures', $selectedNatures);
                if (!is_array($checkedNatures)) {
                    $checkedNatures = [];
                }
                $checkedSectors = old('sector', $selectedSectors);
                if (!is_array($checkedSectors)) {
                    $checkedSectors = array_values(array_filter(array_map('trim', explode(',', (string) $checkedSectors))));
                }
            @endphp

            <div class="row g-3 mb-4">
                <div class="col-lg-5">
                    <div class="ig-checkbox-box">
                        <div class="ig-settings-label mb-3">
                            Business nature <span class="req">*</span>
                            <span class="text-muted fw-normal">(multiple allowed)</span>
                        </div>
                        <div class="ig-check-grid-2">
                            @foreach ($businessNatures as $nature)
                                <label class="ig-check-item">
                                    <input
                                        type="checkbox"
                                        name="business_natures[]"
                                        value="{{ $nature }}"
                                        {{ in_array($nature, $checkedNatures, true) ? 'checked' : '' }}
                                    >
                                    <span>{{ $nature }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="ig-checkbox-box">
                        <div class="ig-settings-label mb-3">
                            Sector <span class="req">*</span>
                            <span class="text-muted fw-normal">(multiple allowed — FBR IRIS)</span>
                        </div>
                        <div class="ig-check-grid-3">
                            @foreach ($sectors as $sector)
                                <label class="ig-check-item">
                                    <input
                                        type="checkbox"
                                        name="sector[]"
                                        value="{{ $sector }}"
                                        {{ in_array($sector, $checkedSectors, true) ? 'checked' : '' }}
                                    >
                                    <span>{{ $sector }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top">
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Keep your Business Nature &amp; Sector aligned with your FBR IRIS Digital Invoicing profile.
                </div>
                <button type="submit" class="btn ig-btn-royal">
                    <i class="bi bi-check2-circle me-1"></i> Save profile
                </button>
            </div>
        </form>
    </div>

    <!-- Bottom 2 Info Cards -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="ig-settings-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="ig-settings-card-title mb-0">Business Status &amp; Monthly Quota</h3>
                    <span class="badge {{ $business->isApproved() ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning' }} px-2 py-1">
                        <i class="bi bi-patch-check-fill me-1"></i> {{ ucfirst($business->status) }}
                    </span>
                </div>

                <div class="mb-3">
                    <div class="small text-muted mb-1">Tenant Identifier (UUID)</div>
                    <code class="d-block px-3 py-2 bg-light border rounded text-dark small user-select-all">{{ $business->uuid }}</code>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="small text-muted mb-1">Active Environment</div>
                        @if (($business->current_environment ?? 'sandbox') === 'production')
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1">Production Live</span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1">Sandbox Only</span>
                        @endif
                    </div>
                    <div class="col-6">
                        <div class="small text-muted mb-1">Monthly Invoice Quota</div>
                        <div class="fw-bold text-dark">
                            @if ($invoiceQuota['unlimited'])
                                {{ $invoiceQuota['used'] }} / Unlimited
                            @else
                                {{ $invoiceQuota['used'] }} / {{ $invoiceQuota['limit'] }}
                                <span class="text-muted fw-normal small">({{ $invoiceQuota['remaining'] }} left)</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if (!$invoiceQuota['unlimited'])
                    <div class="progress mb-2" style="height: 7px; border-radius: 999px; background: #EFF6FF;">
                        <div
                            class="progress-bar {{ $invoiceQuota['reached'] ? 'bg-danger' : '' }}"
                            style="width: {{ min(100, $invoiceQuota['percent']) }}%; {{ !$invoiceQuota['reached'] ? 'background-color: #1E50FF;' : '' }}"
                        ></div>
                    </div>
                    <div class="small text-muted">
                        Cycle: <strong>{{ $invoiceQuota['cycle_start'] }}</strong> &rarr; <strong>{{ $invoiceQuota['renews_on'] }}</strong> (auto-renews monthly)
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="ig-settings-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <h3 class="ig-settings-card-title">FBR Integration &amp; Sandbox Readiness</h3>
                    <p class="small text-muted mb-3">
                        Manage your PRAL Sandbox &amp; Production bearer tokens and verify official FBR Digital Invoicing scenarios required for Go-Live approval.
                    </p>

                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <div class="px-3 py-2 rounded-3 bg-light border flex-grow-1">
                            <div class="small text-muted">Sandbox Connection</div>
                            <div class="fw-semibold text-dark mt-1">
                                @if (in_array($business->sandbox_connection_status, ['connected', 'verified'], true))
                                    <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Connected</span>
                                @else
                                    <span class="text-secondary"><i class="bi bi-clock me-1"></i> Not Verified Yet</span>
                                @endif
                            </div>
                        </div>
                        <div class="px-3 py-2 rounded-3 bg-light border flex-grow-1">
                            <div class="small text-muted">Sandbox Scenarios Passed</div>
                            <div class="fw-semibold text-dark mt-1">
                                <span class="text-primary">{{ $passedScenarios }}</span> / {{ $totalScenarios }} Scenarios
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('tenant.fbr-integration') }}" class="btn btn-sm btn-outline-primary px-3 py-2" style="border-radius: 8px;">
                        <i class="bi bi-stars me-1"></i> Open FBR Integration
                    </a>
                    <a href="{{ route('tenant.sandbox-scenarios') }}" class="btn btn-sm btn-light border px-3 py-2 text-dark" style="border-radius: 8px;">
                        <i class="bi bi-flask me-1"></i> Sandbox Scenarios
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TAB 2: ACCOUNT & SECURITY -->
<div id="tab-pane-security" class="{{ $activeTab === 'security' ? '' : 'd-none' }}">
    <!-- Top 2-Column Row: Two-factor authentication & Email notifications (Matches Screenshot) -->
    <div class="row g-4 mb-4">
        <!-- Left Card: Two-factor authentication -->
        <div class="col-lg-6">
            <div class="ig-sec-card">
                <div class="ig-sec-card-header">
                    <h2>Two-factor authentication</h2>
                    @if ($twoFactorEnabled)
                        <span class="ig-status-pill is-on">
                            <i class="bi bi-check-circle-fill"></i> On
                        </span>
                    @else
                        <span class="ig-status-pill is-off">Off</span>
                    @endif
                </div>

                <div class="ig-sec-card-body">
                    <p class="small text-secondary mb-3" style="line-height: 1.55;">
                        Protect your account with a code from your phone in addition to your password. Recommended for owners and admins, who manage FBR tokens.
                    </p>

                    @if (!$twoFactorEnabled && !$pendingTwoFactorSecret)
                        {{-- State 1: Off — Start Setup --}}
                        <form method="POST" action="{{ route('tenant.settings.two-factor.setup') }}">
                            @csrf
                            <button type="submit" class="btn ig-btn-royal">
                                Set up two-factor authentication
                            </button>
                        </form>
                    @elseif (!$twoFactorEnabled && $pendingTwoFactorSecret)
                        {{-- State 2: Setup Pending — Scan QR + Confirm 6-digit TOTP --}}
                        <div class="p-3 rounded-3 bg-light border mb-3">
                            <div class="fw-semibold small text-dark mb-2">
                                1. Scan this QR code in Google Authenticator, Authy, or Microsoft Authenticator
                            </div>

                            <div class="d-flex flex-column flex-sm-row align-items-center gap-3 mb-3">
                                <div class="ig-qr-wrap flex-shrink-0">
                                    <div id="ig-2fa-qrcode" data-uri="{{ $twoFactorQrUri }}"></div>
                                </div>
                                <div class="flex-grow-1 w-100">
                                    <div class="small text-muted mb-1">Or enter this setup key manually:</div>
                                    <div class="input-group input-group-sm mb-2">
                                        <input
                                            type="text"
                                            id="ig-2fa-secret-input"
                                            class="form-control font-monospace bg-white"
                                            value="{{ $twoFactorFormattedSecret }}"
                                            readonly
                                        >
                                        <button type="button" class="btn btn-outline-secondary" id="ig-copy-2fa-secret" title="Copy setup key">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                    <div class="text-muted" style="font-size: 11.5px;">
                                        Account: <strong>{{ $user->email }}</strong> &middot; Time-based (30s)
                                    </div>
                                </div>
                            </div>

                            <div class="fw-semibold small text-dark mb-2">
                                2. Enter the 6-digit code shown in your app to activate
                            </div>
                            <form method="POST" action="{{ route('tenant.settings.two-factor.confirm') }}" class="d-flex flex-wrap gap-2 align-items-start">
                                @csrf
                                <div style="max-width: 190px;">
                                    <input
                                        type="text"
                                        name="totp_code"
                                        inputmode="numeric"
                                        maxlength="6"
                                        placeholder="123456"
                                        class="form-control font-monospace text-center @error('totp_code') is-invalid @enderror"
                                        style="letter-spacing: 0.2em; font-weight: 700;"
                                        required
                                    >
                                </div>
                                <button type="submit" class="btn ig-btn-royal">
                                    Verify &amp; enable
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-light border text-secondary"
                                    style="padding: 9px 14px; border-radius: 9px; font-size: 13.5px;"
                                    onclick="document.getElementById('cancel-2fa-form').submit();"
                                >
                                    Cancel
                                </button>
                            </form>
                            <form id="cancel-2fa-form" method="POST" action="{{ route('tenant.settings.two-factor.cancel') }}" class="d-none">
                                @csrf
                            </form>
                        </div>
                    @else
                        {{-- State 3: Enabled — Active Status, Recovery Codes, Disable --}}
                        <div class="small text-success fw-semibold mb-3">
                            <i class="bi bi-shield-fill-check me-1"></i>
                            Active since {{ $user->two_factor_confirmed_at?->timezone('Asia/Karachi')->format('d M Y, h:i A') }}
                            &middot; {{ is_array($user->two_factor_recovery_codes) ? count($user->two_factor_recovery_codes) : 0 }} recovery codes remaining
                        </div>

                        @if (is_array($freshRecoveryCodes) && count($freshRecoveryCodes) > 0)
                            <div class="p-3 rounded-3 border border-warning bg-warning bg-opacity-10 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-bold small text-dark">
                                        <i class="bi bi-key-fill text-warning me-1"></i> Save your recovery codes
                                    </div>
                                    <button type="button" class="btn btn-sm btn-light border py-0 px-2 small" id="ig-copy-recovery-codes">
                                        <i class="bi bi-clipboard me-1"></i> Copy all
                                    </button>
                                </div>
                                <div class="text-muted mb-2" style="font-size: 11.5px;">
                                    Store these single-use codes safely. You can use them if you lose access to your phone.
                                </div>
                                <div class="ig-recovery-grid" id="ig-recovery-codes-list" data-codes="{{ implode("\n", $freshRecoveryCodes) }}">
                                    @foreach ($freshRecoveryCodes as $rc)
                                        <div class="ig-recovery-code">{{ $rc }}</div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <form method="POST" action="{{ route('tenant.settings.two-factor.recovery-codes') }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary px-3 py-2" style="border-radius: 8px;">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Regenerate recovery codes
                                </button>
                            </form>

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger px-3 py-2"
                                style="border-radius: 8px;"
                                data-bs-toggle="collapse"
                                data-bs-target="#disable-2fa-collapse"
                                aria-expanded="{{ $errors->has('disable_password') ? 'true' : 'false' }}"
                            >
                                <i class="bi bi-shield-x me-1"></i> Turn off 2FA
                            </button>
                        </div>

                        <div class="collapse {{ $errors->has('disable_password') ? 'show' : '' }}" id="disable-2fa-collapse">
                            <form method="POST" action="{{ route('tenant.settings.two-factor.disable') }}" class="p-3 rounded-3 bg-light border">
                                @csrf
                                <label for="disable_password" class="ig-settings-label">Confirm your password to turn off 2FA</label>
                                <div class="d-flex gap-2">
                                    <input
                                        id="disable_password"
                                        type="password"
                                        name="disable_password"
                                        class="form-control form-control-sm @error('disable_password') is-invalid @enderror"
                                        placeholder="Enter current password"
                                        required
                                    >
                                    <button type="submit" class="btn btn-sm btn-danger text-nowrap px-3">Disable 2FA</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Card: Email notifications -->
        <div class="col-lg-6">
            <div class="ig-sec-card">
                <div class="ig-sec-card-header">
                    <h2>Email notifications</h2>
                    <span id="ig-notif-saved-badge" class="ig-status-pill is-on d-none">
                        <i class="bi bi-check2"></i> Saved
                    </span>
                </div>

                <div class="ig-sec-card-body">
                    <form id="ig-email-notifications-form" method="POST" action="{{ route('tenant.settings.notifications') }}">
                        @csrf
                        <div class="d-flex align-items-start gap-2">
                            <input
                                type="checkbox"
                                id="email_notifications_enabled"
                                name="email_notifications_enabled"
                                value="1"
                                class="form-check-input mt-1 flex-shrink-0"
                                style="width: 17px; height: 17px; accent-color: #1E50FF; cursor: pointer;"
                                {{ ($user->email_notifications_enabled ?? true) ? 'checked' : '' }}
                            >
                            <div>
                                <label for="email_notifications_enabled" class="fw-medium text-dark mb-1" style="font-size: 13.5px; cursor: pointer; line-height: 1.45;">
                                    Email me about rejected or uncertain FBR submissions, support replies, and announcements
                                </label>
                                <div class="text-muted" style="font-size: 12px; line-height: 1.5;">
                                    Notifications always appear in the app. Security emails (password reset, sign-in codes) are always sent.
                                </div>
                            </div>
                        </div>

                        <noscript>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-sm ig-btn-royal">Save preference</button>
                            </div>
                        </noscript>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="ig-settings-card mb-4">
        <h2 class="ig-settings-card-title">Owner Account &amp; Password</h2>

        <form method="POST" action="{{ route('tenant.settings.account') }}">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="owner_name" class="ig-settings-label">Owner Full Name <span class="req">*</span></label>
                    <input
                        id="owner_name"
                        type="text"
                        name="owner_name"
                        class="form-control @error('owner_name') is-invalid @enderror"
                        value="{{ old('owner_name', $user->name) }}"
                        required
                    >
                </div>
                <div class="col-md-6">
                    <label class="ig-settings-label">Account Email</label>
                    <input type="email" class="form-control bg-light text-secondary" value="{{ $user->email }}" readonly>
                </div>
                <div class="col-md-6">
                    <label for="owner_phone" class="ig-settings-label">Mobile Number</label>
                    <input
                        id="owner_phone"
                        type="text"
                        name="owner_phone"
                        class="form-control"
                        value="{{ old('owner_phone', $user->phone) }}"
                        placeholder="03XX-XXXXXXX"
                    >
                </div>
                <div class="col-md-6">
                    <label for="owner_cnic" class="ig-settings-label">Owner CNIC</label>
                    <input
                        id="owner_cnic"
                        type="text"
                        name="owner_cnic"
                        class="form-control"
                        value="{{ old('owner_cnic', $user->cnic) }}"
                        placeholder="42101-1234567-1"
                    >
                </div>
            </div>

            <div class="border-top pt-4 mb-4">
                <div class="fw-bold text-dark small text-uppercase mb-1" style="letter-spacing: 0.04em;">Change Password (Optional)</div>
                <div class="text-muted small mb-3">Leave blank if you do not want to change your login password.</div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="current_password" class="ig-settings-label">Current Password</label>
                        <input
                            id="current_password"
                            type="password"
                            name="current_password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            autocomplete="current-password"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="new_password" class="ig-settings-label">New Password</label>
                        <input
                            id="new_password"
                            type="password"
                            name="new_password"
                            class="form-control @error('new_password') is-invalid @enderror"
                            autocomplete="new-password"
                            placeholder="Minimum 8 characters"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="new_password_confirmation" class="ig-settings-label">Confirm New Password</label>
                        <input
                            id="new_password_confirmation"
                            type="password"
                            name="new_password_confirmation"
                            class="form-control"
                            autocomplete="new-password"
                        >
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn ig-btn-royal">
                    <i class="bi bi-shield-check me-1"></i> Save security changes
                </button>
            </div>
        </form>
    </div>

    <!-- Public IP & Activity History Card -->
    <div class="ig-settings-card">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="ig-settings-card-title mb-1">Activity &amp; Public IP History</h2>
                <div class="small text-muted">Recent logins, profile changes, and invoice actions recorded with your Public IP address.</div>
            </div>
            <div class="ig-ip-pill" title="Current detected Public IP">
                <i class="bi bi-globe2"></i>
                <span>Current Public IP: {{ $currentPublicIp }}</span>
            </div>
        </div>

        @if ($recentActivities->isEmpty())
            <div class="text-center py-4 text-muted small">
                No activity logs recorded yet for this workspace.
            </div>
        @else
            <div class="table-responsive">
                <table class="table ig-activity-table mb-0">
                    <thead>
                        <tr>
                            <th>Date &amp; Time (PKT)</th>
                            <th>Event</th>
                            <th>Description</th>
                            <th class="text-end">Public IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentActivities as $log)
                            <tr>
                                <td class="text-nowrap text-muted">
                                    {{ $log->created_at?->timezone('Asia/Karachi')->format('d M Y, h:i A') }}
                                </td>
                                <td class="text-nowrap">
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-2 py-1">
                                        {{ strtoupper(str_replace('_', ' ', $log->action)) }}
                                    </span>
                                </td>
                                <td>{{ $log->description }}</td>
                                <td class="text-end text-nowrap">
                                    <span class="ig-ip-pill">{{ $log->ip_address ?: '127.0.0.1' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if (!$twoFactorEnabled && $pendingTwoFactorSecret)
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
@endif
<script>
    (function () {
        const buttons = document.querySelectorAll('.ig-settings-tab[data-target-tab]');
        const panes = {
            profile: document.getElementById('tab-pane-profile'),
            security: document.getElementById('tab-pane-security')
        };

        function activateTab(tabName) {
            buttons.forEach((btn) => {
                const isActive = btn.dataset.targetTab === tabName;
                btn.classList.toggle('active', isActive);
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            Object.keys(panes).forEach((key) => {
                if (panes[key]) {
                    panes[key].classList.toggle('d-none', key !== tabName);
                }
            });

            const url = new URL(window.location.href);
            if (tabName === 'security') {
                url.searchParams.set('tab', 'security');
            } else {
                url.searchParams.delete('tab');
            }
            window.history.replaceState({}, '', url.toString());
        }

        buttons.forEach((btn) => {
            btn.addEventListener('click', () => activateTab(btn.dataset.targetTab));
        });

        // Render local browser QR Code for 2FA setup (secret never leaves the browser)
        const qrContainer = document.getElementById('ig-2fa-qrcode');
        if (qrContainer && qrContainer.dataset.uri && typeof QRCode !== 'undefined') {
            new QRCode(qrContainer, {
                text: qrContainer.dataset.uri,
                width: 148,
                height: 148,
                colorDark: '#0F172A',
                colorLight: '#FFFFFF',
                correctLevel: QRCode.CorrectLevel.M
            });
        }

        // Copy 2FA manual secret key
        const copySecretBtn = document.getElementById('ig-copy-2fa-secret');
        const secretInput = document.getElementById('ig-2fa-secret-input');
        if (copySecretBtn && secretInput) {
            copySecretBtn.addEventListener('click', () => {
                navigator.clipboard?.writeText(secretInput.value.replace(/\s+/g, ''));
                copySecretBtn.innerHTML = '<i class="bi bi-check2 text-success"></i>';
                setTimeout(() => {
                    copySecretBtn.innerHTML = '<i class="bi bi-clipboard"></i>';
                }, 1500);
            });
        }

        // Copy recovery codes
        const copyRecoveryBtn = document.getElementById('ig-copy-recovery-codes');
        const recoveryBox = document.getElementById('ig-recovery-codes-list');
        if (copyRecoveryBtn && recoveryBox) {
            copyRecoveryBtn.addEventListener('click', () => {
                navigator.clipboard?.writeText(recoveryBox.dataset.codes || '');
                copyRecoveryBtn.innerHTML = '<i class="bi bi-check2 text-success me-1"></i> Copied';
                setTimeout(() => {
                    copyRecoveryBtn.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy all';
                }, 1800);
            });
        }

        // Auto-save Email notifications preference via AJAX
        const notifCheckbox = document.getElementById('email_notifications_enabled');
        const notifForm = document.getElementById('ig-email-notifications-form');
        const notifBadge = document.getElementById('ig-notif-saved-badge');
        let notifTimer = null;

        if (notifCheckbox && notifForm) {
            notifCheckbox.addEventListener('change', async () => {
                const token = notifForm.querySelector('input[name="_token"]')?.value || '';
                notifCheckbox.disabled = true;
                try {
                    const res = await fetch(notifForm.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            email_notifications_enabled: notifCheckbox.checked
                        })
                    });

                    if (res.ok && notifBadge) {
                        notifBadge.classList.remove('d-none');
                        clearTimeout(notifTimer);
                        notifTimer = setTimeout(() => notifBadge.classList.add('d-none'), 2200);
                    } else {
                        notifForm.submit();
                    }
                } catch (err) {
                    notifForm.submit();
                } finally {
                    notifCheckbox.disabled = false;
                }
            });
        }
    })();
</script>
@endpush
