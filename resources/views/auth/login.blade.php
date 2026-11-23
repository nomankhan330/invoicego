@php
    $registrationEnabled = \App\Models\SystemSetting::get('enable_public_registration', false);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — InvoiceGO</title>
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
            padding: 30px 15px;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        .font-display {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Centered Split Card */
        .login-card-container {
            width: 100%;
            max-width: 900px;
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 20px 50px -10px rgba(15, 23, 42, 0.12), 0 0 1px 1px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            display: flex;
            min-height: 520px;
        }

        /* Left Royal Blue Pane */
        .login-left-pane {
            width: 44%;
            background: linear-gradient(145deg, #1855E2 0%, #1E50FF 55%, #2563EB 100%);
            color: #FFFFFF;
            padding: 44px 38px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .login-logo {
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

        .login-logo-icon {
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

        .login-brand-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #FFFFFF;
            padding: 10px 18px;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.16);
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .login-brand-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.22);
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
            gap: 12px;
        }

        .left-check-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12.5px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.95);
        }

        .left-check-item i {
            font-size: 15px;
            color: #FFFFFF;
            opacity: 0.9;
        }

        /* Right White Form Pane */
        .login-right-pane {
            width: 56%;
            background: #FFFFFF;
            padding: 44px 46px;
            display: flex;
            flex-direction: column;
            justify-content: center;
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
            margin-bottom: 26px;
        }

        .input-group-custom {
            position: relative;
        }

        .input-icon-left {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 16px;
            pointer-events: none;
            z-index: 5;
        }

        .input-icon-right {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 16px;
            cursor: pointer;
            z-index: 5;
            background: none;
            border: none;
            padding: 0;
        }

        .input-icon-right:hover {
            color: #475569;
        }

        .form-control-custom {
            height: 44px;
            border-radius: 9px;
            border: 1px solid #E2E8F0;
            padding-left: 40px;
            padding-right: 14px;
            font-size: 14px;
            color: #0F172A;
            background-color: #FFFFFF;
            transition: all 0.15s ease;
        }

        .form-control-custom.has-right-icon {
            padding-right: 40px;
        }

        .form-control-custom:focus {
            border-color: #1E50FF;
            box-shadow: 0 0 0 3px rgba(30, 80, 255, 0.12);
            outline: none;
        }

        .btn-submit-login {
            background-color: var(--ig-primary);
            color: #FFFFFF;
            font-weight: 600;
            font-size: 14.5px;
            height: 44px;
            border-radius: 9px;
            border: none;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-submit-login:hover {
            background-color: var(--ig-primary-hover);
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(30, 80, 255, 0.3);
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .login-card-container {
                flex-direction: column;
                min-height: auto;
            }
            .login-left-pane {
                width: 100%;
                padding: 30px;
            }
            .login-right-pane {
                width: 100%;
                padding: 30px;
            }
        }
    </style>
</head>
<body>

    <div class="login-card-container">
        <!-- Left Royal Blue Pane -->
        <div class="login-left-pane">
            <div>
                <a href="{{ url('/') }}" class="login-brand-badge mb-3">
                    <img src="{{ asset('images/invoicego-logo.png') }}" alt="InvoiceGO" style="height: 50px; width: auto; object-fit: contain; display: block;">
                </a>

                <div style="margin-top: 36px;">
                    <h1 class="left-h1">Digital Invoicing Simplified for Pakistan</h1>
                    <p class="left-desc">
                        Access your secure FBR digital invoicing dashboard, manage sales, validate tax compliance, and submit invoices instantly.
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-2">
                <ul class="left-checks-list">
                    <li class="left-check-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>FBR Digital Invoicing API Connected</span>
                    </li>
                    <li class="left-check-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Bank-Grade Cloud Encryption</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right White Form Pane -->
        <div class="login-right-pane">
            <h2 class="form-h2">{{ $registrationEnabled ? 'Sign In to Your Workspace' : 'Sign In' }}</h2>
            <p class="form-subtitle">
                {{ $registrationEnabled ? 'Enter your credentials to access InvoiceGO' : 'Enter your login credentials provided by admin' }}
            </p>

            @if (session('status'))
                <div class="alert alert-success py-2 px-3 small mb-3">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 small mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold text-dark mb-1">Business Email</label>
                    <div class="input-group-custom">
                        <i class="bi bi-envelope input-icon-left"></i>
                        <input type="email" class="form-control form-control-custom" id="email" name="email" value="{{ old('email', 'nomanoc1a@gmail.com') }}" placeholder="ahmed.khan@company.pk" required autofocus>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="password" class="form-label small fw-semibold text-dark mb-0">Password</label>
                        <a href="{{ route('password.request') }}" class="text-decoration-none small text-primary fw-medium" style="font-size: 12.5px;">Forgot password?</a>
                    </div>
                    <div class="input-group-custom">
                        <i class="bi bi-lock input-icon-left"></i>
                        <input type="password" class="form-control form-control-custom has-right-icon" id="password" name="password" placeholder="••••••••••••" required>
                        <button type="button" class="input-icon-right" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
                            <i class="bi bi-eye" id="passwordEyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                    <label class="form-check-label small text-muted" for="remember" style="font-size: 13px;">
                        Remember this device
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="d-grid mb-3">
                    <button type="submit" class="btn-submit-login">
                        <span>Sign In to Dashboard</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

                @if ($registrationEnabled)
                    <!-- Footer link -->
                    <div class="text-center pt-2" style="font-size: 13px;">
                        <span class="text-muted">Don't have an account?</span>
                        <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-semibold ms-1">Create Account</a>
                    </div>
                @endif


            </form>
        </div>
    </div>

    <!-- Password visibility toggle script -->
    <script>
        function togglePasswordVisibility() {
            const passInput = document.getElementById('password');
            const eyeIcon = document.getElementById('passwordEyeIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                passInput.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>
