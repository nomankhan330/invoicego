<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Rejected — InvoiceGO</title>
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
                    <div class="mx-auto mb-4 bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="bi bi-x-circle fs-1 text-danger"></i>
                    </div>

                    <h2 class="font-display fw-bold text-dark fs-4 mb-2">Registration Not Approved</h2>
                    <p class="text-secondary small mb-4">
                        We were unable to approve the business registration for <strong>{{ $business->name ?? 'your business' }}</strong>.
                    </p>

                    @if ($business->rejection_reason)
                        <div class="bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 p-3 text-start small mb-4">
                            <div class="fw-bold text-danger mb-1"><i class="bi bi-info-circle me-1"></i> Reason Provided by Admin:</div>
                            <div class="text-dark">{{ $business->rejection_reason }}</div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('register') }}" class="btn btn-ig-primary px-4">Register Another Business</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary px-4">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

