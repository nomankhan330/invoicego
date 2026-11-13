<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended — InvoiceGO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/invoicego.css') }}">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center bg-white">
                    <div class="mx-auto mb-4 bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="bi bi-slash-circle fs-1 text-warning"></i>
                    </div>

                    <h2 class="font-display fw-bold text-dark fs-4 mb-2">Business Account Suspended</h2>
                    <p class="text-secondary small mb-4">
                        Access to the tenant portal for <strong>{{ $business->name ?? 'your business' }}</strong> has been temporarily suspended by the Super Administrator.
                    </p>

                    <p class="text-muted small mb-4">
                        Please contact InvoiceGO support or your platform administrator to review compliance and restore access.
                    </p>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary px-4">Sign out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
