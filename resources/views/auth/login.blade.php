<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — InvoiceGO</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/invoicego.css') }}">
</head>
<body>

    <div class="auth-split-wrapper">
        <!-- Left Brand Pane (Purple Gradient) -->
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
        <div class="auth-form-pane">
            <div class="auth-card">
                <h2 class="fw-bold fs-4 text-dark font-display mb-1">Sign in</h2>
                <p class="text-muted small mb-4">Business account sign-in</p>

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
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', 'nomanoc1a@gmail.com') }}" placeholder="ahmed.khan@gmail.com" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••••••" required>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-ig-primary py-2 fs-6">Sign in</button>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2">
                        <a href="{{ route('password.request') }}" class="text-decoration-none small text-muted">Forgot password?</a>
                        <a href="{{ route('register') }}" class="text-decoration-none small text-primary fw-medium" style="color: #5850EC !important;">Create account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
