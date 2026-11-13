@extends('layouts.tenant')

@section('title', 'Business Settings')

@section('content')
<div class="mb-4">
    <h1 class="font-display fw-bold text-dark fs-3 mb-1">Business Settings</h1>
    <div class="text-muted small">Update business profile, fiscal identity, and team preferences</div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="ig-content-card">
            <h5 class="card-title">Business Profile &amp; IRIS Identity</h5>

            <form method="POST" action="{{ route('tenant.settings.update') }}">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Business Legal Name</label>
                        <input type="text" class="form-control" name="name" value="{{ $business->name }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NTN / CNIC</label>
                        <input type="text" class="form-control bg-light" value="{{ $business->ntn_cnic }}" readonly>
                        <div class="form-text">NTN/CNIC cannot be changed after registration without Super Admin approval.</div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">STRN</label>
                        <input type="text" class="form-control" name="strn" value="{{ $business->strn }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sector</label>
                        <input type="text" class="form-control bg-light" value="{{ $business->sector }}" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Operational Business Address</label>
                    <textarea class="form-control" name="address" rows="3" required>{{ $business->address }}</textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Province</label>
                        <input type="text" class="form-control" name="province" value="{{ $business->province }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Registration Status</label>
                        <div class="pt-2">
                            <span class="badge bg-success bg-opacity-10 text-success fs-6">
                                <i class="bi bi-patch-check-fill me-1"></i> Approved by Super Admin
                            </span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-ig-primary px-4">
                    Update Profile
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="ig-content-card">
            <h6 class="fw-bold text-dark fs-6 mb-3">Tenant Details</h6>
            <div class="small mb-2 text-muted">Tenant Identifier (UUID):</div>
            <code class="d-block p-2 bg-light rounded text-dark small mb-3 user-select-all">{{ $business->uuid }}</code>

            <div class="small mb-2 text-muted">Registered Sector:</div>
            <div class="fw-semibold text-dark mb-3">{{ $business->sector }}</div>

            <div class="small mb-2 text-muted">Business Natures:</div>
            <div class="d-flex flex-wrap gap-1 mb-3">
                @foreach ($business->business_natures ?? [] as $nature)
                    <span class="badge bg-light text-secondary border">{{ $nature }}</span>
                @endforeach
            </div>

            <div class="small mb-2 text-muted">Retailer Profile:</div>
            <div class="text-dark">{{ $business->is_retailer_registered ? 'Registered Retailer (SN026–SN028 Eligible)' : 'Standard Non-Retailer' }}</div>
        </div>
    </div>
</div>
@endsection

