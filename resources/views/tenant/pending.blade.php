<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Pending Approval — InvoiceGO</title>
    <!-- Fonts -->
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
                        <i class="bi bi-hourglass-split fs-1 text-warning"></i>
                    </div>

                    <h2 class="font-display fw-bold text-dark fs-4 mb-2">Registration Pending Approval</h2>
                    <p class="text-secondary small mb-4">
                        Thank you for registering <strong>{{ $business->name ?? 'your business' }}</strong> (NTN/CNIC: {{ $business->ntn_cnic ?? '' }}). Your account has been received and is currently undergoing verification by the InvoiceGO administration team.
                    </p>

                    <div class="bg-light rounded-3 p-3 text-start small mb-4 border">
                        <div class="row g-2">
                            <div class="col-4 text-muted">Business:</div>
                            <div class="col-8 fw-semibold text-dark">{{ $business->name ?? '' }}</div>
                            <div class="col-4 text-muted">NTN / CNIC:</div>
                            <div class="col-8 text-dark">{{ $business->ntn_cnic ?? '' }}</div>
                            <div class="col-4 text-muted">Sector:</div>
                            <div class="col-8 text-dark">{{ $business->sector ?? '' }}</div>
                            <div class="col-4 text-muted">Status:</div>
                            <div class="col-8"><span class="badge bg-warning text-dark px-2 py-1">Pending Super Admin Review</span></div>
                        </div>
                    </div>

                    <p class="text-muted small mb-4" style="font-size: 12.5px;">
                        To protect the integrity of official FBR fiscalization credentials, new tenant businesses require administrative validation before the business portal and FBR integration can be unlocked.
                    </p>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('tenant.pending') }}" class="btn btn-ig-primary px-4">
                            <i class="bi bi-arrow-clockwise me-1"></i> Check Status
                        </a>
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
