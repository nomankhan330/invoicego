<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvoiceGO — FBR Digital Invoicing for Pakistani Businesses</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/invoicego.css') }}">
    <style>
        .hero-section {
            padding: 80px 0 60px 0;
            background: radial-gradient(circle at 10% 10%, rgba(238, 242, 255, 0.7) 0%, rgba(255, 255, 255, 1) 70%);
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #4F46E5;
            background: #EEF2FF;
            border: 1px solid #E0E7FF;
            border-radius: 20px;
            padding: 6px 14px;
            margin-bottom: 24px;
        }
        .hero-badge .dot {
            width: 7px;
            height: 7px;
            background-color: #10B981;
            border-radius: 50%;
        }
        .hero-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 48px;
            font-weight: 800;
            line-height: 1.18;
            color: #0F172A;
            margin-bottom: 20px;
            letter-spacing: -0.02em;
        }
        .hero-title .gradient-text {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-subtitle {
            font-size: 17px;
            line-height: 1.6;
            color: #64748B;
            margin-bottom: 32px;
            max-width: 580px;
        }
        .onboarding-rail-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        }
        .rail-header {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #64748B;
            text-transform: uppercase;
            margin-bottom: 24px;
        }
        .rail-step {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
        }
        .rail-step:last-child {
            margin-bottom: 0;
        }
        .rail-step-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1.5px solid #CBD5E1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            flex-shrink: 0;
            background: #F8FAFC;
        }
        .rail-step-title {
            font-size: 14px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 3px;
        }
        .rail-step-desc {
            font-size: 12.5px;
            color: #64748B;
            line-height: 1.45;
        }
        .feature-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 26px 24px;
            height: 100%;
            transition: all 0.2s ease;
        }
        .feature-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
            border-color: #CBD5E1;
        }
        .feature-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #EEF2FF;
            color: #5850EC;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 18px;
        }
        .feature-title {
            font-size: 15px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 8px;
        }
        .feature-desc {
            font-size: 13px;
            line-height: 1.5;
            color: #64748B;
        }
        .fbr-explanation-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 28px 32px;
            margin-top: 50px;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg bg-white border-bottom py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold font-display fs-4" href="{{ url('/') }}">
                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #5850EC !important;">
                    <i class="bi bi-file-earmark-arrow-up-fill fs-5"></i>
                </div>
                <span>InvoiceGO</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('login') }}" class="btn btn-link text-decoration-none text-dark fw-medium px-3">Sign in</a>
                <a href="{{ route('register') }}" class="btn btn-ig-primary px-3">Create account</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="hero-badge">
                        <span class="dot"></span>
                        <span>FBR DIGITAL INVOICING · DI API V1.12</span>
                    </div>
                    <h1 class="hero-title">
                        Sales tax invoices for Pakistani businesses, <span class="gradient-text">validated before you submit.</span>
                    </h1>
                    <p class="hero-subtitle">
                        InvoiceGO helps you build FBR Digital Invoicing invoices with the right sale type, rate, UOM and SRO, validate them with FBR using your own token, submit them, and print a clean A4 invoice.
                    </p>
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('register') }}" class="btn btn-ig-primary btn-lg px-4 py-2 fs-6">Create your account</a>
                        <a href="{{ route('login') }}" class="btn btn-ig-outline btn-lg px-4 py-2 fs-6">Sign in</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="onboarding-rail-card">
                        <div class="rail-header">From sign-up to live invoicing</div>
                        <div class="rail-step">
                            <div class="rail-step-num">1</div>
                            <div>
                                <div class="rail-step-title">Create your account</div>
                                <div class="rail-step-desc">Register your business with the NTN/CNIC, business nature and sector exactly as they appear in IRIS.</div>
                            </div>
                        </div>
                        <div class="rail-step">
                            <div class="rail-step-num">2</div>
                            <div>
                                <div class="rail-step-title">Connect IRIS</div>
                                <div class="rail-step-desc">Complete the technical details and IP whitelisting in IRIS, then save your sandbox token in InvoiceGO.</div>
                            </div>
                        </div>
                        <div class="rail-step">
                            <div class="rail-step-num">3</div>
                            <div>
                                <div class="rail-step-title">Pass the sandbox scenarios</div>
                                <div class="rail-step-desc">Run the scenarios that apply to your business. Each is marked passed only after FBR accepts it.</div>
                            </div>
                        </div>
                        <div class="rail-step">
                            <div class="rail-step-num">4</div>
                            <div>
                                <div class="rail-step-title">Go live</div>
                                <div class="rail-step-desc">Save the production token IRIS issues, then create, validate and submit real invoices.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <h3 class="fw-bold fs-4 mb-4 text-dark font-display">What InvoiceGO does</h3>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon-box"><i class="bi bi-flask"></i></div>
                        <div class="feature-title">Sandbox scenarios, built in</div>
                        <div class="feature-desc">Every supplied scenario (SN001–SN028) loads as an editable form and JSON. Run it against your own FBR sandbox token and track which required scenarios have genuinely passed.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon-box"><i class="bi bi-person-check"></i></div>
                        <div class="feature-title">Buyer registration checks</div>
                        <div class="feature-desc">Before anything is sent, the buyer's registration type is checked with FBR. A wrong Registered/Unregistered choice is caught with the buyer's name in the message.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon-box"><i class="bi bi-calendar3"></i></div>
                        <div class="feature-title">Current-month invoicing only</div>
                        <div class="feature-desc">Invoices can only be created and submitted for the current calendar month (Pakistan time), enforced in the browser and on the server.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon-box"><i class="bi bi-shield-check"></i></div>
                        <div class="feature-title">Careful submission</div>
                        <div class="feature-desc">Exact decimal maths, item-level result checks, locked submissions and a reconciliation workflow when the outcome is uncertain — never a blind resend.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon-box"><i class="bi bi-printer"></i></div>
                        <div class="feature-title">A4 invoices with FBR number &amp; QR</div>
                        <div class="feature-desc">Printable invoices carry the FBR invoice number, the Digital Invoicing logo and QR only after FBR accepts them. Sandbox prints are clearly watermarked.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon-box"><i class="bi bi-people"></i></div>
                        <div class="feature-title">Your whole team, with roles</div>
                        <div class="feature-desc">Invite accountants and operators by email. Owners and admins manage FBR credentials; viewers get read-only access.</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon-box"><i class="bi bi-key"></i></div>
                        <div class="feature-title">Secure accounts</div>
                        <div class="feature-desc">Optional two-factor sign-in with recovery codes, encrypted FBR tokens that never reach the browser, and a full audit trail.</div>
                    </div>
                </div>
            </div>

            <!-- Access Explanation Box -->
            <div class="fbr-explanation-card">
                <h5 class="fw-bold fs-6 text-dark mb-2">How access to FBR works</h5>
                <p class="text-secondary small mb-2" style="line-height: 1.6;">
                    FBR Digital Invoicing credentials are issued to each taxpayer through IRIS (Digital Invoicing &rarr; API Integration). You complete technical details and IP whitelisting there, use your sandbox token to pass the scenarios that apply to your business, and IRIS then provides your production token. InvoiceGO stores your tokens encrypted on the server and never shows them in full.
                </p>
                <p class="text-muted small mb-0" style="font-size: 11.5px;">
                    InvoiceGO is independent software. It is not FBR-certified, FBR-approved or an FBR-licensed integrator.
                </p>
            </div>

            <div class="text-center text-muted small mt-5 pt-3">
                &copy; {{ date('Y') }} InvoiceGO. All rights reserved.
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
