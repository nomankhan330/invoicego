@extends('layouts.tenant')

@section('title', 'Add Customer')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="font-display fw-bold text-dark fs-3 mb-1">Add Customer</h1>
        <div class="text-muted small">Register a customer and verify registration type with FBR</div>
    </div>
    <a href="{{ route('tenant.customers.index') }}" class="btn btn-ig-outline">
        <i class="bi bi-arrow-left me-1"></i> Back to Customers
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="ig-content-card">
            <form method="POST" action="{{ route('tenant.customers.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Customer / Business Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Metro Supermarket" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="ntn_cnic" class="form-label">NTN / CNIC <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ntn_cnic" name="ntn_cnic" value="{{ old('ntn_cnic') }}" placeholder="7-digit NTN or 13-digit CNIC" required>
                    </div>
                    <div class="col-md-6">
                        <label for="registration_type" class="form-label">FBR Registration Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="registration_type" name="registration_type" required>
                            <option value="registered" {{ old('registration_type') === 'registered' ? 'selected' : '' }}>Registered (Sales Tax Payer)</option>
                            <option value="unregistered" {{ old('registration_type', 'unregistered') === 'unregistered' ? 'selected' : '' }}>Unregistered Person (Subject to 4% Further Tax)</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="strn" class="form-label">STRN</label>
                        <input type="text" class="form-control" id="strn" name="strn" value="{{ old('strn') }}" placeholder="Sales Tax Registration Number">
                    </div>
                    <div class="col-md-6">
                        <label for="province" class="form-label">Province</label>
                        <select class="form-select" id="province" name="province">
                            <option value="Punjab">Punjab</option>
                            <option value="Sindh">Sindh</option>
                            <option value="Khyber Pakhtunkhwa">Khyber Pakhtunkhwa</option>
                            <option value="Balochistan">Balochistan</option>
                            <option value="Islamabad Capital Territory">Islamabad Capital Territory</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="2" placeholder="Customer physical address">{{ old('address') }}</textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('tenant.customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-ig-primary px-4">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

