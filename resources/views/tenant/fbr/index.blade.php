@extends('layouts.tenant')

@section('title', 'FBR Integration')

@section('content')
<div class="mb-4">
    <h1 class="font-display fw-bold text-dark fs-3 mb-1">FBR / PRAL API Integration</h1>
    <div class="text-muted small">
        Connect your IRIS Digital Invoicing credentials with end-to-end server encryption
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="ig-content-card">
            <h5 class="card-title">Fiscal Integration Credentials</h5>

            <form method="POST" action="{{ route('tenant.fbr-integration.update') }}">
                @csrf

                <!-- Environment Selector -->
                <div class="mb-4">
                    <label class="form-label">Operating Environment <span class="text-danger">*</span></label>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="form-check p-3 border rounded-3 {{ ($business->current_environment ?? 'sandbox') === 'sandbox' ? 'bg-light border-primary' : '' }}">
                                <input class="form-check-input" type="radio" name="current_environment" id="env_sandbox" value="sandbox" {{ ($business->current_environment ?? 'sandbox') === 'sandbox' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark d-block" for="env_sandbox">
                                    Sandbox (Testing)
                                </label>
                                <span class="text-muted small" style="font-size: 11.5px;">For scenario validation and invoice testing without tax liability.</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check p-3 border rounded-3 {{ ($business->current_environment ?? 'sandbox') === 'production' ? 'bg-light border-primary' : '' }}">
                                <input class="form-check-input" type="radio" name="current_environment" id="env_prod" value="production" {{ ($business->current_environment ?? 'sandbox') === 'production' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark d-block" for="env_prod">
                                    Live Production
                                </label>
                                <span class="text-muted small" style="font-size: 11.5px;">Transmits fiscalized invoices to live PRAL government servers.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sandbox Token -->
                <div class="mb-4">
                    <label for="sandbox_token" class="form-label">
                        Sandbox Bearer Token
                        @if (!empty($business->sandbox_token))
                            <span class="badge bg-success bg-opacity-10 text-success ms-1">Configured &amp; Encrypted</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Not Set</span>
                        @endif
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                        <input type="password" class="form-control" id="sandbox_token" name="sandbox_token" placeholder="{{ !empty($business->sandbox_token) ? '•••••••••••••••••••••••• (Leave blank to keep existing)' : 'Paste IRIS Sandbox Token' }}">
                    </div>
                    <div class="form-text">Issued via IRIS (Digital Invoicing &rarr; API Integration). Token is encrypted at rest using AES-256-CBC.</div>
                </div>

                <!-- Production Token -->
                <div class="mb-4">
                    <label for="production_token" class="form-label">
                        Production Bearer Token
                        @if (!empty($business->production_token))
                            <span class="badge bg-success bg-opacity-10 text-success ms-1">Configured &amp; Encrypted</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Not Set</span>
                        @endif
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" class="form-control" id="production_token" name="production_token" placeholder="{{ !empty($business->production_token) ? '•••••••••••••••••••••••• (Leave blank to keep existing)' : 'Paste IRIS Production Token' }}">
                    </div>
                    <div class="form-text">Issued after passing required sandbox scenarios in IRIS. Never exposed to browser or frontend.</div>
                </div>

                <!-- Production Submissions Gate -->
                <div class="mb-4 p-3 bg-light rounded-3 border">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="production_submissions_enabled" value="1" id="prod_enabled" {{ $business->production_submissions_enabled ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-dark" for="prod_enabled">
                            Enable Live Production Submissions
                        </label>
                    </div>
                    <div class="text-muted small ps-4 mt-1" style="font-size: 11.5px;">
                        Safety Lock: When unchecked, real live invoices cannot be submitted to PRAL to prevent accidental fiscalization.
                    </div>
                </div>

                <button type="submit" class="btn btn-ig-primary px-4">
                    Save FBR Configuration
                </button>
            </form>
        </div>
    </div>

    <!-- Guidance Sidebar -->
    <div class="col-lg-5">
        <div class="ig-content-card mb-4">
            <h6 class="fw-bold text-dark fs-6 mb-3"><i class="bi bi-info-circle text-primary me-1"></i> How to Obtain PRAL Credentials</h6>
            <ol class="small text-secondary ps-3 mb-0" style="line-height: 1.65;">
                <li class="mb-2">Log into the official FBR <strong>IRIS portal</strong> using your business credentials.</li>
                <li class="mb-2">Navigate to <strong>Digital Invoicing &rarr; API Integration</strong>.</li>
                <li class="mb-2">Enter technical contact details and perform <strong>IP Whitelisting</strong> for your server static IP.</li>
                <li class="mb-2">Copy the provided <strong>Sandbox Token</strong> into InvoiceGO.</li>
                <li class="mb-2">Run the required test scenarios (SN001–SN028) in the <strong>Sandbox Scenarios</strong> menu.</li>
                <li>Once accepted by PRAL, IRIS will generate your <strong>Production Token</strong> for live fiscalization.</li>
            </ol>
        </div>

        <div class="ig-content-card bg-light border-0">
            <div class="d-flex gap-2">
                <i class="bi bi-shield-shaded text-success fs-4 flex-shrink-0"></i>
                <div class="small">
                    <div class="fw-bold text-dark mb-1">Zero-Exposure Security Guarantee</div>
                    <div class="text-muted" style="font-size: 11.5px;">
                        InvoiceGO securely stores all FBR credentials on the server using authenticated encryption. Tokens are injected into backend HTTP clients only and never transmitted to the browser or JavaScript console.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

