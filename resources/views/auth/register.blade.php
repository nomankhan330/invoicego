<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create your business account — InvoiceGO</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/invoicego.css') }}">
    <style>
        .auth-card-wide {
            max-width: 680px;
        }
        .form-section-title {
            font-size: 14px;
            font-weight: 700;
            color: #0F172A;
            margin-top: 18px;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid #F1F5F9;
        }
        .nature-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px 14px;
        }
        @media (max-width: 768px) {
            .nature-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>

    <div class="auth-split-wrapper">
        <!-- Left Brand Pane -->
        <div class="auth-brand-pane">
            <div>
                <a href="{{ url('/') }}" class="text-white text-decoration-none d-flex align-items-center gap-2 fw-bold font-display fs-4 mb-5">
                    <div class="bg-white rounded-3 d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px;">
                        <i class="bi bi-file-earmark-arrow-up-fill fs-5" style="color: #5850EC;"></i>
                    </div>
                    <span>InvoiceGO</span>
                </a>

                <h1 class="font-display fw-bold text-white fs-2 mb-4" style="line-height: 1.25; margin-top: 80px;">
                    FBR Digital Invoicing,<br>done carefully.
                </h1>

                <ul class="auth-feature-list">
                    <li class="auth-feature-item">
                        <div class="auth-feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <span>Every invoice is checked with FBR before it is submitted</span>
                    </li>
                    <li class="auth-feature-item">
                        <div class="auth-feature-icon">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <span>Current-month rule and buyer registration checks built in</span>
                    </li>
                    <li class="auth-feature-item">
                        <div class="auth-feature-icon">
                            <i class="bi bi-lock"></i>
                        </div>
                        <span>FBR tokens are encrypted and never reach the browser</span>
                    </li>
                </ul>
            </div>

            <div class="text-white-50 small" style="font-size: 11.5px;">
                InvoiceGO is independent software. It is not FBR-certified or FBR-approved.
            </div>
        </div>

        <!-- Right Form Pane -->
        <div class="auth-form-pane py-5">
            <div class="auth-card auth-card-wide my-auto">
                <h2 class="fw-bold fs-4 text-dark font-display mb-1">Create your business account</h2>
                <p class="text-muted small mb-3">Fields marked <span class="text-danger">*</span> are required.</p>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3 small mb-3">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.post') }}">
                    @csrf

                    <!-- Group 1: Your Details -->
                    <div class="form-section-title">Your details</div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Ahmed Khan" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" placeholder="03001234567" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="ahmed.khan@company.com" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        <div class="col-12 mt-1">
                            <div class="form-text">At least 10 characters with letters and numbers</div>
                        </div>
                    </div>

                    <!-- Group 2: Business Profile -->
                    <div class="form-section-title">Business profile</div>

                    <div class="mb-3">
                        <label for="business_name" class="form-label">Business name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="business_name" name="business_name" value="{{ old('business_name') }}" placeholder="e.g. Al-Madina Trading Co." required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="ntn_cnic" class="form-label">NTN / CNIC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ntn_cnic" name="ntn_cnic" value="{{ old('ntn_cnic') }}" placeholder="1234567" required>
                            <div class="form-text">7-digit NTN or 13-digit CNIC, digits only</div>
                        </div>
                        <div class="col-md-6">
                            <label for="strn" class="form-label">STRN</label>
                            <input type="text" class="form-control" id="strn" name="strn" value="{{ old('strn') }}" placeholder="3277876123456">
                            <div class="form-text">Sales tax registration number, if any</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="business_address" class="form-label">Business address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="business_address" name="business_address" value="{{ old('business_address') }}" placeholder="Shop / Office / Plot address" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="province" class="form-label">Province <span class="text-danger">*</span></label>
                            <select class="form-select" id="province" name="province" required>
                                <option value="Punjab" {{ old('province') == 'Punjab' ? 'selected' : '' }}>Punjab</option>
                                <option value="Sindh" {{ old('province') == 'Sindh' ? 'selected' : '' }}>Sindh</option>
                                <option value="Khyber Pakhtunkhwa" {{ old('province') == 'Khyber Pakhtunkhwa' ? 'selected' : '' }}>Khyber Pakhtunkhwa</option>
                                <option value="Balochistan" {{ old('province') == 'Balochistan' ? 'selected' : '' }}>Balochistan</option>
                                <option value="Islamabad Capital Territory" {{ old('province') == 'Islamabad Capital Territory' ? 'selected' : '' }}>Islamabad Capital Territory</option>
                                <option value="Gilgit-Baltistan" {{ old('province') == 'Gilgit-Baltistan' ? 'selected' : '' }}>Gilgit-Baltistan</option>
                                <option value="Azad Jammu & Kashmir" {{ old('province') == 'Azad Jammu & Kashmir' ? 'selected' : '' }}>Azad Jammu &amp; Kashmir</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="sector" class="form-label">Sector <span class="text-danger">*</span></label>
                            <select class="form-select" id="sector" name="sector" required>
                                <option value="All Other Sectors" {{ old('sector') == 'All Other Sectors' ? 'selected' : '' }}>All Other Sectors</option>
                                <option value="Steel" {{ old('sector') == 'Steel' ? 'selected' : '' }}>Steel</option>
                                <option value="Fast Moving Consumer Goods (FMCG)" {{ old('sector') == 'Fast Moving Consumer Goods (FMCG)' ? 'selected' : '' }}>Fast Moving Consumer Goods (FMCG)</option>
                                <option value="Textile" {{ old('sector') == 'Textile' ? 'selected' : '' }}>Textile</option>
                                <option value="Pharmaceutical" {{ old('sector') == 'Pharmaceutical' ? 'selected' : '' }}>Pharmaceutical</option>
                                <option value="Automotive" {{ old('sector') == 'Automotive' ? 'selected' : '' }}>Automotive</option>
                                <option value="Electronics" {{ old('sector') == 'Electronics' ? 'selected' : '' }}>Electronics</option>
                                <option value="Chemicals" {{ old('sector') == 'Chemicals' ? 'selected' : '' }}>Chemicals</option>
                            </select>
                            <div class="form-text">Select one sector (IRIS: Sector &mdash; select one only)</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Business nature <span class="text-danger">*</span> <span class="text-muted fw-normal">(select one or more)</span></label>
                        <div class="nature-grid">
                            @php
                                $natures = ['Manufacturer', 'Importer', 'Distributor', 'Wholesaler', 'Exporter', 'Retailer', 'Service Provider', 'Other'];
                                $oldNatures = old('business_natures', []);
                            @endphp
                            @foreach ($natures as $nature)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="business_natures[]" value="{{ $nature }}" id="nature_{{ Str::slug($nature) }}" {{ in_array($nature, $oldNatures) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="nature_{{ Str::slug($nature) }}">{{ $nature }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_retailer_registered" value="1" id="is_retailer_registered" {{ old('is_retailer_registered') ? 'checked' : '' }}>
                            <label class="form-check-label small text-dark" for="is_retailer_registered">
                                Registered as a retailer in my sales tax profile
                            </label>
                        </div>
                        <div class="form-text ps-4">Required for scenarios SN026–SN028 (spec §9 note).</div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-ig-primary py-2 fs-6">Create account</button>
                    </div>

                    <div class="text-center mt-3 pt-2">
                        <span class="text-muted small">Already registered? </span>
                        <a href="{{ route('login') }}" class="text-decoration-none small text-primary fw-medium" style="color: #5850EC !important;">Sign in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
