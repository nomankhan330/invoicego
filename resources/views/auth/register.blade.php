<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Business Account — InvoiceGO</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --ig-primary: #1E50FF;
            --ig-primary-hover: #1541D9;
            --ig-bg: #F0F4FA;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--ig-bg);
            color: #0F172A;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        .font-display {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .register-card-container {
            width: 100%;
            max-width: 1080px;
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 20px 50px -10px rgba(15, 23, 42, 0.12), 0 0 1px 1px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            display: flex;
        }

        /* Left Royal Blue Pane */
        .register-left-pane {
            width: 38%;
            background: linear-gradient(145deg, #1855E2 0%, #1E50FF 55%, #2563EB 100%);
            color: #FFFFFF;
            padding: 44px 38px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .register-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #FFFFFF;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 20px;
            letter-spacing: -0.02em;
        }

        .register-logo-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #FFFFFF;
            color: var(--ig-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        }

        .left-h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 26px;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 14px;
            letter-spacing: -0.02em;
        }

        .left-desc {
            font-size: 13.5px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.88);
            margin-bottom: 0;
        }

        .left-checks-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .left-check-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.95);
        }

        .left-check-item i {
            font-size: 16px;
            color: #FFFFFF;
        }

        /* Right White Form Pane */
        .register-right-pane {
            width: 62%;
            background: #FFFFFF;
            padding: 44px 48px;
            overflow-y: auto;
            max-height: 90vh;
        }

        .form-h2 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #0F172A;
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }

        .form-subtitle {
            font-size: 13.5px;
            color: #64748B;
            margin-bottom: 24px;
        }

        .section-badge-title {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #1E50FF;
            background: #EEF4FF;
            padding: 4px 12px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 14px;
            margin-top: 10px;
        }

        .form-control-custom, .form-select-custom {
            height: 42px;
            border-radius: 8px;
            border: 1px solid #E2E8F0;
            padding: 8px 12px;
            font-size: 13.5px;
            color: #0F172A;
            background-color: #FFFFFF;
            transition: all 0.15s ease;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            border-color: #1E50FF;
            box-shadow: 0 0 0 3px rgba(30, 80, 255, 0.12);
            outline: none;
        }

        .nature-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .btn-submit-register {
            background-color: var(--ig-primary);
            color: #FFFFFF;
            font-weight: 600;
            font-size: 14.5px;
            height: 46px;
            border-radius: 9px;
            border: none;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-submit-register:hover {
            background-color: var(--ig-primary-hover);
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(30, 80, 255, 0.3);
            transform: translateY(-1px);
        }

        @media (max-width: 900px) {
            .register-card-container {
                flex-direction: column;
            }
            .register-left-pane, .register-right-pane {
                width: 100%;
                padding: 30px;
                max-height: none;
            }
            .nature-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>

    <div class="register-card-container">
        <!-- Left Royal Blue Pane -->
        <div class="register-left-pane">
            <div>
                <a href="{{ url('/') }}" class="d-inline-block mb-3">
                    <img src="{{ asset('images/invoicego-logo-dark.png') }}" alt="InvoiceGO" style="height: 44px; width: auto; object-fit: contain;">
                </a>

                <div style="margin-top: 36px;">
                    <h1 class="left-h1">FBR Digital Invoicing, Done Carefully.</h1>
                    <p class="left-desc">
                        Register your business profile to start generating FBR-compliant digital sales invoices, automate tax calculations, and manage compliance.
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-2">
                <ul class="left-checks-list">
                    <li class="left-check-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Every invoice is pre-validated with FBR rules</span>
                    </li>
                    <li class="left-check-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Current-month rule &amp; ATL buyer checks built in</span>
                    </li>
                    <li class="left-check-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>FBR API credentials AES-256 encrypted</span>
                    </li>
                </ul>
                <div class="text-white-50 mt-4 small" style="font-size: 11px;">
                    InvoiceGO is independent software. Not FBR-certified or FBR-licensed.
                </div>
            </div>
        </div>

        <!-- Right White Form Pane -->
        <div class="register-right-pane">
            <h2 class="form-h2">Create your business account</h2>
            <p class="form-subtitle">Fields marked <span class="text-danger">*</span> are required.</p>

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

                <!-- Section 1: Your Details -->
                <div class="section-badge-title">Your details</div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label small fw-semibold text-dark mb-1">Full name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-custom" id="name" name="name" value="{{ old('name') }}" placeholder="Ahmed Khan" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label small fw-semibold text-dark mb-1">Phone number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-custom" id="phone" name="phone" value="{{ old('phone') }}" placeholder="03001234567" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold text-dark mb-1">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control form-control-custom" id="email" name="email" value="{{ old('email') }}" placeholder="ahmed.khan@company.com" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label small fw-semibold text-dark mb-1">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control form-control-custom" id="password" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label small fw-semibold text-dark mb-1">Confirm password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control form-control-custom" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    <div class="col-12">
                        <div class="text-muted" style="font-size: 11.5px;">At least 10 characters with letters and numbers</div>
                    </div>
                </div>

                <!-- Section 2: Business Profile -->
                <div class="section-badge-title">Business profile</div>

                <div class="mb-3">
                    <label for="business_name" class="form-label small fw-semibold text-dark mb-1">Business name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-custom" id="business_name" name="business_name" value="{{ old('business_name') }}" placeholder="e.g. Al-Madina Trading Co." required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label for="ntn_cnic" class="form-label small fw-semibold text-dark mb-1">NTN / CNIC <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-custom" id="ntn_cnic" name="ntn_cnic" value="{{ old('ntn_cnic') }}" placeholder="1234567" required>
                        <div class="text-muted" style="font-size: 11px;">7-digit NTN or 13-digit CNIC, digits only</div>
                    </div>
                    <div class="col-md-6">
                        <label for="strn" class="form-label small fw-semibold text-dark mb-1">STRN</label>
                        <input type="text" class="form-control form-control-custom" id="strn" name="strn" value="{{ old('strn') }}" placeholder="3277876123456">
                        <div class="text-muted" style="font-size: 11px;">Sales tax registration number, if any</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="business_address" class="form-label small fw-semibold text-dark mb-1">Business address <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-custom" id="business_address" name="business_address" value="{{ old('business_address') }}" placeholder="Shop / Office / Plot address" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label for="province" class="form-label small fw-semibold text-dark mb-1">Province <span class="text-danger">*</span></label>
                        <select class="form-select form-select-custom" id="province" name="province" required>
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
                        <label for="sector" class="form-label small fw-semibold text-dark mb-1">Sector <span class="text-danger">*</span></label>
                        <select class="form-select form-select-custom" id="sector" name="sector" required>
                            <option value="All Other Sectors" {{ old('sector') == 'All Other Sectors' ? 'selected' : '' }}>All Other Sectors</option>
                            <option value="Steel" {{ old('sector') == 'Steel' ? 'selected' : '' }}>Steel</option>
                            <option value="Fast Moving Consumer Goods (FMCG)" {{ old('sector') == 'Fast Moving Consumer Goods (FMCG)' ? 'selected' : '' }}>Fast Moving Consumer Goods (FMCG)</option>
                            <option value="Textile" {{ old('sector') == 'Textile' ? 'selected' : '' }}>Textile</option>
                            <option value="Pharmaceutical" {{ old('sector') == 'Pharmaceutical' ? 'selected' : '' }}>Pharmaceutical</option>
                            <option value="Automotive" {{ old('sector') == 'Automotive' ? 'selected' : '' }}>Automotive</option>
                            <option value="Electronics" {{ old('sector') == 'Electronics' ? 'selected' : '' }}>Electronics</option>
                            <option value="Chemicals" {{ old('sector') == 'Chemicals' ? 'selected' : '' }}>Chemicals</option>
                        </select>
                        <div class="text-muted" style="font-size: 11px;">Select one sector (IRIS standard)</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark d-block mb-2">Business nature <span class="text-danger">*</span> <span class="text-muted fw-normal">(select one or more)</span></label>
                    <div class="nature-grid">
                        @php
                            $natures = ['Manufacturer', 'Importer', 'Distributor', 'Wholesaler', 'Exporter', 'Retailer', 'Service Provider', 'Other'];
                            $oldNatures = old('business_natures', []);
                        @endphp
                        @foreach ($natures as $nature)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="business_natures[]" value="{{ $nature }}" id="nature_{{ Str::slug($nature) }}" {{ in_array($nature, $oldNatures) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="nature_{{ Str::slug($nature) }}" style="font-size: 12.5px;">{{ $nature }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4 bg-light rounded-3 p-3 border">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_retailer_registered" value="1" id="is_retailer_registered" {{ old('is_retailer_registered') ? 'checked' : '' }}>
                        <label class="form-check-label small fw-semibold text-dark" for="is_retailer_registered" style="font-size: 13px;">
                            Registered as a retailer in my sales tax profile
                        </label>
                    </div>
                    <div class="text-muted ps-4" style="font-size: 11px;">Required for Tier-1 POS scenarios SN026–SN028.</div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid mb-3">
                    <button type="submit" class="btn-submit-register">
                        <span>Create account</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

                <!-- Sign In link -->
                <div class="text-center pt-1" style="font-size: 13px;">
                    <span class="text-muted">Already have an account?</span>
                    <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-semibold ms-1">Sign in</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
