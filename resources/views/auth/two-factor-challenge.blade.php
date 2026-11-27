<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication — InvoiceGO</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #F0F4FA;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .tfa-card {
            width: 100%;
            max-width: 440px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 18px;
            padding: 36px 32px;
            box-shadow: 0 20px 45px -12px rgba(15, 23, 42, 0.12);
        }
        .tfa-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #EFF6FF;
            color: #1E50FF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }
        .tfa-input {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 22px;
            letter-spacing: 0.22em;
            text-align: center;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1.5px solid #CBD5E1;
        }
        .tfa-input:focus {
            border-color: #1E50FF;
            box-shadow: 0 0 0 3px rgba(30, 80, 255, 0.16);
        }
        .btn-royal {
            background: #1E50FF;
            border: 1px solid #1E50FF;
            color: #FFFFFF;
            font-weight: 600;
            padding: 11px 18px;
            border-radius: 10px;
        }
        .btn-royal:hover {
            background: #1541D9;
            border-color: #1541D9;
            color: #FFFFFF;
        }
    </style>
</head>
<body>
    <div class="tfa-card">
        <div class="text-center mb-4">
            <div class="mb-3">
                <img src="{{ \App\Services\BrandLogoService::lightUrl() }}" alt="InvoiceGO" style="height: 36px; width: auto; object-fit: contain;">
            </div>
            <div class="tfa-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1 class="h4 fw-bold text-dark mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif;">Two-factor verification</h1>
            <p class="small text-muted mb-0">
                Enter the 6-digit code from your authenticator app, or enter one of your saved recovery codes.
            </p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger small py-2 px-3 mb-3">
                <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('two-factor.verify') }}">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label small fw-semibold text-secondary">Authentication or recovery code</label>
                <input
                    id="code"
                    type="text"
                    name="code"
                    class="form-control tfa-input @error('code') is-invalid @enderror"
                    placeholder="000000"
                    autocomplete="one-time-code"
                    autofocus
                    required
                >
            </div>

            <button type="submit" class="btn btn-royal w-100 mb-3">
                Verify &amp; Sign In
            </button>

            <div class="text-center">
                <a href="{{ route('login') }}" class="small text-decoration-none text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Back to Sign In
                </a>
            </div>
        </form>
    </div>
</body>
</html>
