<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — InvoiceGO</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/invoicego.css') }}">
    @stack('styles')
</head>
<body>

    <!-- Left Sidebar -->
    <aside class="tenant-sidebar">
        <div>
            <!-- Brand Logo -->
            <div class="d-flex align-items-center gap-2 mb-4 px-2">
                <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #5850EC !important;">
                    <i class="bi bi-file-earmark-arrow-up-fill fs-5"></i>
                </div>
                <span class="font-display fw-bold text-dark fs-5">InvoiceGO</span>
            </div>

            <!-- Business Switcher Card (Screenshot 4) -->
            @php
                $activeBusiness = app(\App\Services\Contracts\TenantServiceInterface::class)->getActiveBusiness();
                $userBusinesses = Auth::user() ? app(\App\Services\Contracts\TenantServiceInterface::class)->getUserBusinesses(Auth::user()) : collect();
                $userRole = Auth::user() && $activeBusiness ? (Auth::user()->businesses()->where('businesses.id', $activeBusiness->id)->first()?->pivot?->role ?? 'owner') : 'owner';
            @endphp

            <div class="business-switcher-card">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="business-name">{{ $activeBusiness->name ?? 'Default Business' }}</div>
                </div>
                <div class="business-meta mb-2">
                    NTN/CNIC {{ $activeBusiness->ntn_cnic ?? '0000000' }} &middot; {{ $userRole }}
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    @if (($activeBusiness->current_environment ?? 'sandbox') === 'sandbox')
                        <span class="badge-sandbox">Sandbox only</span>
                    @else
                        <span class="badge-production">Production</span>
                    @endif
                </div>

                <div class="mt-2 pt-2 border-top">
                    @if ($userBusinesses->count() > 1)
                        <div class="dropdown">
                            <a class="text-decoration-none text-muted small dropdown-toggle d-block" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-arrow-left-right me-1"></i> Switch business ({{ $userBusinesses->count() }})
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm small py-1">
                                @foreach ($userBusinesses as $biz)
                                    <li>
                                        <form method="POST" action="{{ route('switch.business', $biz->uuid) }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item d-flex justify-content-between align-items-center py-2 {{ $biz->id === $activeBusiness->id ? 'active' : '' }}">
                                                <span>{{ $biz->name }}</span>
                                                <span class="badge bg-light text-dark ms-2">{{ $biz->status }}</span>
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item text-primary" href="{{ route('register') }}">
                                        <i class="bi bi-plus-circle me-1"></i> + Add another business
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('register') }}" class="text-decoration-none small text-muted d-block" style="font-size: 11.5px;">
                            + Add another business
                        </a>
                    @endif
                </div>
            </div>

            <!-- WORKSPACE Section -->
            <div class="nav-section-title">WORKSPACE</div>
            <nav class="d-flex flex-column gap-1">
                <a href="{{ route('tenant.dashboard') }}" class="nav-link-ig {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('tenant.invoices.create') }}" class="nav-link-ig {{ request()->routeIs('tenant.invoices.create') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-plus"></i>
                    <span>Create invoice</span>
                </a>
                <a href="{{ route('tenant.invoices.index') }}" class="nav-link-ig {{ request()->routeIs('tenant.invoices.index') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Invoices</span>
                </a>
                <a href="{{ route('tenant.customers.index') }}" class="nav-link-ig {{ request()->routeIs('tenant.customers.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Customers</span>
                </a>
                <a href="{{ route('tenant.products.index') }}" class="nav-link-ig {{ request()->routeIs('tenant.products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    <span>Products</span>
                </a>
                <a href="{{ route('tenant.hscodes.index') }}" class="nav-link-ig {{ request()->routeIs('tenant.hscodes.*') ? 'active' : '' }}">
                    <i class="bi bi-hash"></i>
                    <span>HS / PCT codes</span>
                </a>
                <a href="{{ route('tenant.reports') }}" class="nav-link-ig {{ request()->routeIs('tenant.reports') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Reports</span>
                </a>
            </nav>

            <!-- FBR Section -->
            <div class="nav-section-title">FBR</div>
            <nav class="d-flex flex-column gap-1">
                <a href="{{ route('tenant.sandbox-scenarios') }}" class="nav-link-ig {{ request()->routeIs('tenant.sandbox-scenarios') ? 'active' : '' }}">
                    <i class="bi bi-flask"></i>
                    <span>Sandbox scenarios</span>
                </a>
                <a href="{{ route('tenant.fbr-integration') }}" class="nav-link-ig {{ request()->routeIs('tenant.fbr-integration') ? 'active' : '' }}">
                    <i class="bi bi-stars"></i>
                    <span>FBR integration</span>
                </a>
            </nav>

            <!-- ACCOUNT Section -->
            <div class="nav-section-title">ACCOUNT</div>
            <nav class="d-flex flex-column gap-1">
                <a href="{{ route('tenant.settings') }}" class="nav-link-ig {{ request()->routeIs('tenant.settings') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </nav>
        </div>

        <!-- Sidebar Footer Note (Screenshot 4) -->
        <div class="border-top pt-3 mt-4 text-muted small" style="font-size: 11px; line-height: 1.45;">
            <div class="d-flex gap-2">
                <i class="bi bi-file-earmark-check fs-6 text-secondary flex-shrink-0"></i>
                <span>Implements PRAL DI API spec v1.12. InvoiceGO is not an FBR certified or FBR-approved product.</span>
            </div>
        </div>
    </aside>

    <!-- Main Content Container -->
    <div class="tenant-main-content">
        <!-- Top Bar (Screenshot 4) -->
        <header class="tenant-topbar">
            <div class="text-muted small">
                Times shown in Pakistan Standard Time (Asia/Karachi)
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- Notifications Bell -->
                <button type="button" class="btn btn-link text-secondary position-relative p-1 text-decoration-none">
                    <i class="bi bi-bell fs-5"></i>
                </button>

                <!-- User Profile & Avatar -->
                <div class="d-flex align-items-center gap-2">
                    <div class="text-end d-none d-sm-block">
                        <div class="fw-semibold text-dark small" style="line-height: 1.2;">{{ Auth::user()->name ?? 'User' }}</div>
                        <div class="text-muted" style="font-size: 11px;">{{ Auth::user()->email ?? '' }}</div>
                    </div>
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; background: #5850EC !important; font-size: 14px;">
                        {{ strtoupper(substr(Auth::user()->name ?? 'N', 0, 1)) }}
                    </div>
                </div>

                <!-- Sign Out -->
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-link text-secondary p-1" title="Sign out">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </button>
                </form>
            </div>
        </header>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show small mb-4 py-2 px-3" role="alert">
                <i class="bi bi-check-circle-fill me-1 text-success"></i> {{ session('success') }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show small mb-4 py-2 px-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> {{ session('error') }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Page Yield -->
        @yield('content')
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
