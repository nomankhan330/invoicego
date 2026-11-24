@extends('layouts.tenant')

@section('title', 'FBR Integration')

@push('styles')
<style>
    .fbr-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 24px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .fbr-card-active-sandbox {
        border: 2px solid #F59E0B;
        box-shadow: 0 4px 16px rgba(245, 158, 11, 0.08) !important;
    }
    .fbr-card-active-production {
        border: 2px solid #16A34A;
        box-shadow: 0 4px 16px rgba(22, 163, 74, 0.1) !important;
    }
    .fbr-card-title {
        font-size: 14.5px;
        font-weight: 700;
        color: #0F172A;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .fbr-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
    }
    .fbr-input {
        font-size: 13px;
        padding: 7px 12px;
        border-radius: 6px;
        border: 1px solid #CBD5E1;
    }
    .fbr-input:focus {
        border-color: #1E50FF;
        box-shadow: 0 0 0 2px rgba(30, 80, 255, 0.12);
    }
    .fbr-token-group .fbr-input {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .fbr-token-group .btn-token-action {
        border: 1px solid #CBD5E1;
        border-left: none;
        background: #F8FAFC;
        color: #475569;
        font-size: 13px;
        padding: 6px 10px;
    }
    .fbr-token-group .btn-token-action:hover {
        background: #EFF6FF;
        color: #1E50FF;
    }
    .fbr-hint {
        font-size: 11px;
        color: #64748B;
        margin-top: 3px;
    }
    .fbr-footer-note {
        font-size: 11px;
        color: #64748B;
        line-height: 1.45;
        border-top: 1px solid #F1F5F9;
        padding-top: 14px;
        margin-top: 18px;
    }
    .env-status-banner {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 14px 20px;
    }
</style>
@endpush

@section('content')
@php
    $isProductionActive = $business->current_environment === 'production' && $business->production_submissions_enabled;
    $sandboxStatus = $business->sandbox_connection_status ?? 'not_tested';
    $productionStatus = $business->production_connection_status ?? 'not_tested';
    $sandboxVerified = in_array($sandboxStatus, ['connected', 'verified'], true);
    $productionVerified = in_array($productionStatus, ['connected', 'verified'], true);
@endphp

<div class="container-fluid px-0">
    <!-- Active Environment Header Banner -->
    <div class="env-status-banner shadow-sm d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="font-display fw-bold text-dark fs-4 mb-0">FBR Integration</h1>
            <div class="text-muted small">Manage your IRIS Digital Invoicing API credentials and active submission environment.</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small fw-medium">Active Environment:</span>
            @if ($isProductionActive)
                <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 fw-bold d-inline-flex align-items-center gap-2" style="font-size: 12px;">
                    <span class="rounded-circle bg-success d-inline-block" style="width: 8px; height: 8px;"></span>
                    PRODUCTION (Live)
                </span>
            @else
                <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 fw-bold d-inline-flex align-items-center gap-2" style="font-size: 12px; color: #B45309 !important;">
                    <span class="rounded-circle bg-warning d-inline-block" style="width: 8px; height: 8px;"></span>
                    SANDBOX (Testing)
                </span>
            @endif
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small rounded-3 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show p-3 small rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 fw-bold mb-1">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ session('error') }}</span>
                @if (session('fbr_http_status') !== null)
                    <span class="badge bg-danger text-white ms-1">HTTP {{ session('fbr_http_status') }}</span>
                @endif
            </div>
            @if (is_array(session('fbr_errors')) && count(session('fbr_errors')) > 0)
                <ul class="mb-2 ps-3 mt-2">
                    @foreach (session('fbr_errors') as $fbrErr)
                        <li class="font-monospace" style="font-size: 12px;">{{ $fbrErr }}</li>
                    @endforeach
                </ul>
            @endif
            @if (!empty(session('fbr_raw_response')))
                <details class="mt-2" open>
                    <summary class="fw-semibold" style="cursor: pointer; font-size: 12px;">Raw FBR Gateway Response</summary>
                    <pre class="bg-dark text-warning p-2 rounded-2 mt-1 mb-0" style="font-size: 11.5px; max-height: 220px; overflow: auto;">{{ json_encode(session('fbr_raw_response'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </details>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show py-2 px-3 small rounded-3 mb-4" role="alert">
            <i class="bi bi-info-circle-fill me-1"></i> {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Top Row: Sandbox & Production Credentials Cards -->
    <div class="row g-4 mb-4">
        <!-- 1. SANDBOX CREDENTIALS CARD -->
        <div class="col-lg-6">
            <div class="fbr-card shadow-sm {{ !$isProductionActive ? 'fbr-card-active-sandbox' : '' }}">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="fbr-card-title">
                            <span class="text-warning" style="font-size: 16px;">&bull;</span>
                            <span class="text-uppercase fw-bold font-monospace" style="letter-spacing: 0.05em; font-size: 12px; color: #D97706;">SANDBOX</span>
                            <span>Sandbox credentials</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if (!$isProductionActive)
                                <span class="badge bg-warning text-dark fw-bold" style="font-size: 10.5px;">
                                    ACTIVE ENV
                                </span>
                            @endif
                            @if (!empty($business->sandbox_token))
                                <span class="badge bg-success bg-opacity-10 text-success fw-semibold" style="font-size: 11px;">
                                    <i class="bi bi-check-circle-fill me-1"></i> Token saved
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold" style="font-size: 11px;">
                                    No token
                                </span>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tenant.fbr-integration.sandbox.save') }}" id="sandboxForm">
                        @csrf
                        <div class="mb-3">
                            <label class="fbr-label">Sandbox URL (post)</label>
                            <input type="text" name="sandbox_url" class="form-control fbr-input"
                                   value="{{ old('sandbox_url', $business->sandbox_url ?? 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata_sb') }}" required>
                            <div class="fbr-hint">Default: https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata_sb</div>
                        </div>

                        <div class="mb-3">
                            <label class="fbr-label">Sandbox validation URL</label>
                            @php
                                $sbValUrl = ($business->sandbox_validation_url && $business->sandbox_validation_url !== 'https://gw.fbr.gov.pk/di_data/v1/di/validatedata_sb')
                                    ? $business->sandbox_validation_url
                                    : 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata_sb';
                            @endphp
                            <input type="text" name="sandbox_validation_url" class="form-control fbr-input"
                                   value="{{ old('sandbox_validation_url', $sbValUrl) }}" required>
                            <div class="fbr-hint">Default: https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata_sb</div>
                        </div>

                        <div class="mb-4">
                            <label class="fbr-label">Sandbox token / API key</label>
                            <div class="input-group fbr-token-group">
                                <input type="password" name="sandbox_token" id="sandbox_token" class="form-control fbr-input font-monospace"
                                       value="{{ old('sandbox_token', $business->sandbox_token) }}"
                                       placeholder="Paste the security token from IRIS" autocomplete="off">
                                <button type="button" class="btn btn-token-action toggle-token-btn" data-target="sandbox_token" title="Show / Hide token">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-token-action copy-token-btn" data-target="sandbox_token" title="Copy token">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <div class="fbr-hint">Sent only as 'Authorization: Bearer ...' from the server. Click the eye icon to view your saved token.</div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold d-inline-flex align-items-center gap-1" style="background: #1E50FF; border-color: #1E50FF;">
                                <i class="bi bi-save"></i> Save
                            </button>
                            <button type="submit" formaction="{{ route('tenant.fbr-integration.sandbox.verify') }}" class="btn btn-link text-decoration-none p-0 text-primary small d-inline-flex align-items-center gap-1 fw-semibold">
                                <i class="bi bi-wifi"></i> Verify connection
                            </button>
                        </div>
                    </form>
                </div>

                <div class="fbr-footer-note">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Connection status:</span>
                        @if ($sandboxVerified)
                            <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Connected &amp; Verified</strong>
                        @elseif ($sandboxStatus === 'failed')
                            <strong class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Connection Failed / Rejected</strong>
                        @elseif ($sandboxStatus === 'configured')
                            <strong class="text-primary">Saved (Not tested yet)</strong>
                        @else
                            <strong class="text-secondary">Not tested yet</strong>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Last checked:</span>
                        <span>{{ $business->sandbox_verified_at ? $business->sandbox_verified_at->timezone('Asia/Karachi')->format('d M Y, H:i T') : '—' }}</span>
                    </div>
                    <div>
                        Verification calls the FBR Provinces reference API with this token. It never creates an invoice. Only https://gw.fbr.gov.pk with the documented paths is accepted.
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. PRODUCTION CREDENTIALS CARD -->
        <div class="col-lg-6">
            <div class="fbr-card shadow-sm {{ $isProductionActive ? 'fbr-card-active-production' : '' }}">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="fbr-card-title">
                            <span class="text-success" style="font-size: 16px;">&bull;</span>
                            <span class="text-uppercase fw-bold font-monospace" style="letter-spacing: 0.05em; font-size: 12px; color: #16A34A;">PRODUCTION</span>
                            <span>Production credentials</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if ($isProductionActive)
                                <span class="badge bg-success text-white fw-bold" style="font-size: 10.5px;">
                                    ACTIVE ENV
                                </span>
                            @endif
                            @if (!empty($business->production_token))
                                <span class="badge bg-success bg-opacity-10 text-success fw-semibold" style="font-size: 11px;">
                                    <i class="bi bi-check-circle-fill me-1"></i> Token saved
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold" style="font-size: 11px;">
                                    No token
                                </span>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tenant.fbr-integration.production.save') }}" id="productionForm">
                        @csrf
                        <div class="mb-3">
                            <label class="fbr-label">Production URL (post)</label>
                            <input type="text" name="production_url" class="form-control fbr-input"
                                   value="{{ old('production_url', $business->production_url ?? 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata') }}" required>
                            <div class="fbr-hint">Default: https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata</div>
                        </div>

                        <div class="mb-3">
                            <label class="fbr-label">Production validation URL</label>
                            <input type="text" name="production_validation_url" class="form-control fbr-input"
                                   value="{{ old('production_validation_url', $business->production_validation_url ?? 'https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata') }}" required>
                            <div class="fbr-hint">Default: https://gw.fbr.gov.pk/di_data/v1/di/validateinvoicedata</div>
                        </div>

                        <div class="mb-4">
                            <label class="fbr-label">Production token / API key</label>
                            <div class="input-group fbr-token-group">
                                <input type="password" name="production_token" id="production_token" class="form-control fbr-input font-monospace"
                                       value="{{ old('production_token', $business->production_token) }}"
                                       placeholder="Paste the security token from IRIS" autocomplete="off">
                                <button type="button" class="btn btn-token-action toggle-token-btn" data-target="production_token" title="Show / Hide token">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-token-action copy-token-btn" data-target="production_token" title="Copy token">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <div class="fbr-hint">Sent only as 'Authorization: Bearer ...' from the server. Click the eye icon to view your saved token.</div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold d-inline-flex align-items-center gap-1" style="background: #1E50FF; border-color: #1E50FF;">
                                <i class="bi bi-save"></i> Save
                            </button>
                            <button type="submit" formaction="{{ route('tenant.fbr-integration.production.verify') }}" class="btn btn-link text-decoration-none p-0 text-primary small d-inline-flex align-items-center gap-1 fw-semibold">
                                <i class="bi bi-wifi"></i> Verify connection
                            </button>
                        </div>
                    </form>
                </div>

                <div class="fbr-footer-note">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Connection status:</span>
                        @if ($productionVerified)
                            <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Connected &amp; Verified</strong>
                        @elseif ($productionStatus === 'failed')
                            <strong class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Connection Failed / Rejected</strong>
                        @elseif ($productionStatus === 'configured')
                            <strong class="text-primary">Saved (Not tested yet)</strong>
                        @else
                            <strong class="text-secondary">Not tested yet</strong>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Last checked:</span>
                        <span>{{ $business->production_verified_at ? $business->production_verified_at->timezone('Asia/Karachi')->format('d M Y, H:i T') : '—' }}</span>
                    </div>
                    <div>
                        Verification calls the FBR Provinces reference API with this token. It never creates an invoice. Only https://gw.fbr.gov.pk with the documented paths is accepted.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Row: Production submission Card -->
    <div class="fbr-card shadow-sm mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h6 class="fw-bold text-dark mb-0">Production submission</h6>
            <span class="badge {{ $isProductionActive ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning' }} fw-semibold" style="font-size: 11px; {{ !$isProductionActive ? 'color: #B45309 !important;' : '' }}">
                Current mode: {{ $isProductionActive ? 'Production (Live)' : 'Sandbox (Testing)' }}
            </span>
        </div>
        <div class="text-secondary small mb-3" style="line-height: 1.55;">
            Production submission is <strong class="{{ $business->production_submissions_enabled ? 'text-success' : 'text-danger' }}">{{ $business->production_submissions_enabled ? 'enabled' : 'disabled' }}</strong>. Enable it after IRIS has issued your production token and the connection test succeeds.<br>
            The sandbox workspace always uses the sandbox URLs and token; production invoices always use the production URLs and token. Replacing the production token disables production until it is tested again.
        </div>
        <form method="POST" action="{{ route('tenant.fbr-integration.production.toggle') }}">
            @csrf
            @if ($business->production_submissions_enabled)
                <button type="submit" class="btn btn-outline-danger btn-sm px-3 fw-semibold">
                    <i class="bi bi-shield-x me-1"></i> Disable production
                </button>
            @else
                <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold" style="background: #1E50FF; border-color: #1E50FF;">
                    <i class="bi bi-shield-check me-1"></i> Enable production
                </button>
            @endif
        </form>
    </div>

    <!-- Bottom Row: Recent FBR calls Card -->
    <div class="fbr-card shadow-sm">
        <h6 class="fw-bold text-dark mb-3">Recent FBR calls</h6>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12.5px;">
                <thead class="table-light">
                    <tr>
                        <th class="text-secondary text-uppercase" style="font-size: 11px;">WHEN</th>
                        <th class="text-secondary text-uppercase" style="font-size: 11px;">ENVIRONMENT</th>
                        <th class="text-secondary text-uppercase" style="font-size: 11px;">OPERATION</th>
                        <th class="text-secondary text-uppercase" style="font-size: 11px;">OUTCOME</th>
                        <th class="text-secondary text-uppercase" style="font-size: 11px;">INVOICE / SCENARIO</th>
                        <th class="text-secondary text-uppercase" style="font-size: 11px;">HTTP / FBR DETAILS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentCalls as $call)
                        <tr>
                            <td class="text-nowrap text-secondary">{{ $call->executed_at ? $call->executed_at->diffForHumans() : $call->created_at->diffForHumans() }}</td>
                            <td>
                                @if ($call->environment === 'production')
                                    <span class="badge bg-success bg-opacity-10 text-success fw-semibold">production</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold">sandbox</span>
                                @endif
                            </td>
                            <td class="font-monospace text-dark">{{ basename($call->endpoint) ?: ($call->scenario_code ?? 'Invoice Operation') }}</td>
                            <td>
                                @if (in_array(strtolower($call->outcome), ['verified', 'accepted', 'success', 'passed']))
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-check me-1"></i> {{ strtoupper($call->outcome) }}
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        <i class="bi bi-x me-1"></i> {{ strtoupper($call->outcome) }}
                                    </span>
                                @endif
                            </td>
                            <td class="font-monospace">
                                @if ($call->invoice)
                                    <a href="{{ route('tenant.invoices.show', $call->invoice->uuid) }}" class="text-primary text-decoration-none fw-semibold">
                                        {{ $call->invoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ $call->scenario_code ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="small" style="max-width: 340px;">
                                @if ($call->http_status)
                                    <span class="badge bg-light text-dark border me-1">HTTP {{ $call->http_status }}</span>
                                @endif
                                @if ($call->error_message)
                                    <span class="text-danger">{{ $call->error_message }}</span>
                                @elseif (is_array($call->response_payload) && !empty($call->response_payload['invoiceNumber']))
                                    <span class="text-success font-monospace">{{ $call->response_payload['invoiceNumber'] }}</span>
                                @else
                                    <span class="text-muted">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small">
                                No recent FBR calls recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-token-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.getAttribute('data-target'));
            const icon = this.querySelector('i');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });

    document.querySelectorAll('.copy-token-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.getAttribute('data-target'));
            const icon = this.querySelector('i');
            if (!input || !input.value) return;
            navigator.clipboard.writeText(input.value).then(() => {
                icon.classList.remove('bi-clipboard');
                icon.classList.add('bi-check2', 'text-success');
                setTimeout(() => {
                    icon.classList.remove('bi-check2', 'text-success');
                    icon.classList.add('bi-clipboard');
                }, 1500);
            });
        });
    });
});
</script>
@endpush
