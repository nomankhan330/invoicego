<x-filament-panels::page>
    <div class="report-dashboard-wrap" x-data="{
        currentTheme: localStorage.getItem('theme') || 'system',
        setTheme(mode) {
            this.currentTheme = mode;
            localStorage.setItem('theme', mode);
            if (mode === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (mode === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
            $dispatch('theme-changed', mode);
        }
    }">
        <style>
            .report-dashboard-wrap {
                --rp-card-bg: #ffffff;
                --rp-card-border: #e2e8f0;
                --rp-card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.03);
                --rp-text-main: #0f172a;
                --rp-text-muted: #64748b;
                --rp-text-light: #94a3b8;
                --rp-divider: #f1f5f9;
                --rp-th-bg: #f8fafc;
                --rp-th-text: #475569;
                --rp-tr-hover: #f8fafc;
                --rp-badge-approved-bg: #ecfdf5;
                --rp-badge-approved-text: #047857;
                --rp-toggle-bg: #f1f5f9;
                --rp-toggle-btn-active: #ffffff;
                --rp-toggle-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
            }

            :is(.dark) .report-dashboard-wrap,
            .dark .report-dashboard-wrap,
            html.dark .report-dashboard-wrap {
                --rp-card-bg: #182234;
                --rp-card-border: #2c384e;
                --rp-card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.25), 0 2px 4px -2px rgba(0, 0, 0, 0.2);
                --rp-text-main: #f8fafc;
                --rp-text-muted: #94a3b8;
                --rp-text-light: #64748b;
                --rp-divider: #243044;
                --rp-th-bg: #0f172a;
                --rp-th-text: #94a3b8;
                --rp-tr-hover: #1e293b;
                --rp-badge-approved-bg: rgba(16, 185, 129, 0.15);
                --rp-badge-approved-text: #34d399;
                --rp-toggle-bg: #0f172a;
                --rp-toggle-btn-active: #2c384e;
                --rp-toggle-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            }

            .rp-top-bar {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                margin-bottom: 1.25rem;
            }

            .rp-theme-toggle {
                display: inline-flex;
                align-items: center;
                background-color: var(--rp-toggle-bg);
                border: 1px solid var(--rp-card-border);
                border-radius: 9999px;
                padding: 3px;
                gap: 2px;
            }
            .rp-theme-btn {
                border: none;
                background: transparent;
                padding: 6px 14px;
                border-radius: 9999px;
                font-size: 12px;
                font-weight: 600;
                color: var(--rp-text-muted);
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all 0.15s ease;
            }
            .rp-theme-btn:hover {
                color: var(--rp-text-main);
            }
            .rp-theme-btn.active {
                background-color: var(--rp-toggle-btn-active);
                color: var(--rp-text-main);
                box-shadow: var(--rp-toggle-shadow);
            }

            .rp-grid {
                display: grid;
                grid-template-columns: repeat(1, minmax(0, 1fr));
                gap: 1.25rem;
            }
            @media (min-width: 640px) {
                .rp-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            }
            @media (min-width: 1024px) {
                .rp-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            }

            .rp-card {
                background-color: var(--rp-card-bg);
                border: 1px solid var(--rp-card-border);
                border-radius: 14px;
                padding: 24px 26px;
                box-shadow: var(--rp-card-shadow);
                transition: transform 0.15s ease, box-shadow 0.15s ease;
                box-sizing: border-box;
                position: relative;
            }
            .rp-card:hover {
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
                transform: translateY(-1px);
            }

            .rp-card-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 14px;
            }
            .rp-card-title {
                font-size: 11.5px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                color: var(--rp-text-muted);
            }
            .rp-icon-pill {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .rp-icon-indigo {
                background-color: #eff6ff;
                color: #1e50ff;
            }
            .dark .rp-icon-indigo {
                background-color: rgba(30, 80, 255, 0.2);
                color: #60a5fa;
            }
            .rp-icon-emerald {
                background-color: #ecfdf5;
                color: #059669;
            }
            .dark .rp-icon-emerald {
                background-color: rgba(16, 185, 129, 0.2);
                color: #34d399;
            }
            .rp-icon-purple {
                background-color: #dbeafe;
                color: #1d4ed8;
            }
            .dark .rp-icon-purple {
                background-color: rgba(37, 99, 235, 0.2);
                color: #93c5fd;
            }
            .rp-icon-blue {
                background-color: #eff6ff;
                color: #2563eb;
            }
            .dark .rp-icon-blue {
                background-color: rgba(59, 130, 246, 0.2);
                color: #60a5fa;
            }

            .rp-card-value {
                font-size: 28px;
                font-weight: 800;
                line-height: 1.2;
                color: var(--rp-text-main);
                margin: 0 0 14px 0;
                letter-spacing: -0.02em;
                display: flex;
                align-items: baseline;
                gap: 6px;
            }
            .rp-card-sublabel {
                font-size: 14px;
                font-weight: 500;
                color: var(--rp-text-muted);
            }

            .rp-card-footer {
                border-top: 1px solid var(--rp-divider);
                padding-top: 12px;
                margin-top: 4px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 12px;
                color: var(--rp-text-muted);
            }
            .rp-card-footer strong {
                color: var(--rp-text-main);
                font-weight: 700;
            }

            .rp-table-box {
                background-color: var(--rp-card-bg);
                border: 1px solid var(--rp-card-border);
                border-radius: 14px;
                box-shadow: var(--rp-card-shadow);
                overflow: hidden;
                margin-top: 1.5rem;
            }
            .rp-table-header {
                padding: 20px 26px;
                border-bottom: 1px solid var(--rp-card-border);
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 12px;
            }
            .rp-table-heading {
                font-size: 15px;
                font-weight: 700;
                color: var(--rp-text-main);
                margin: 0 0 3px 0;
            }
            .rp-table-desc {
                font-size: 12px;
                color: var(--rp-text-muted);
                margin: 0;
            }

            .rp-table-scroll {
                overflow-x: auto;
                width: 100%;
            }
            .rp-table {
                width: 100%;
                border-collapse: collapse;
                text-align: left;
                font-size: 13px;
            }
            .rp-table th {
                background-color: var(--rp-th-bg);
                color: var(--rp-th-text);
                padding: 13px 20px;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                border-bottom: 1px solid var(--rp-card-border);
                white-space: nowrap;
            }
            .rp-table td {
                padding: 14px 20px;
                border-bottom: 1px solid var(--rp-divider);
                color: var(--rp-text-main);
                vertical-align: middle;
            }
            .rp-table tbody tr:hover {
                background-color: var(--rp-tr-hover);
            }
            .rp-table tbody tr:last-child td {
                border-bottom: none;
            }

            .rp-badge {
                display: inline-flex;
                align-items: center;
                padding: 3px 9px;
                border-radius: 9999px;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.03em;
            }
            .rp-badge-approved {
                background-color: var(--rp-badge-approved-bg);
                color: var(--rp-badge-approved-text);
            }
            .rp-badge-pending {
                background-color: #fef3c7;
                color: #b45309;
            }
            .dark .rp-badge-pending {
                background-color: rgba(245, 158, 11, 0.2);
                color: #fbbf24;
            }
        </style>

        <!-- Mode Toggle Bar -->
        <div class="rp-top-bar">
            <div class="rp-theme-toggle">
                <button
                    type="button"
                    class="rp-theme-btn"
                    :class="{ 'active': currentTheme === 'light' }"
                    @click="setTheme('light')"
                    title="Switch to Light Mode"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <span>Light</span>
                </button>
                <button
                    type="button"
                    class="rp-theme-btn"
                    :class="{ 'active': currentTheme === 'dark' }"
                    @click="setTheme('dark')"
                    title="Switch to Dark Mode"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                    <span>Dark</span>
                </button>
                <button
                    type="button"
                    class="rp-theme-btn"
                    :class="{ 'active': currentTheme === 'system' }"
                    @click="setTheme('system')"
                    title="Match System Preference"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                    <span>System</span>
                </button>
            </div>
        </div>

        <!-- 4 Key Metrics Cards -->
        <div class="rp-grid">
            <!-- Card 1: User Registrations -->
            <div class="rp-card">
                <div class="rp-card-header">
                    <span class="rp-card-title">User Registrations</span>
                    <div class="rp-icon-pill rp-icon-indigo">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                </div>
                <div class="rp-card-value">
                    {{ $monthRegistrations }}
                </div>
                <div class="rp-card-footer">
                    <span>Today: <strong>{{ $todayRegistrations }}</strong></span>
                    <span>Last 7 Days: <strong>{{ $weekRegistrations }}</strong></span>
                </div>
            </div>

            <!-- Card 2: Subscription Status -->
            <div class="rp-card">
                <div class="rp-card-header">
                    <span class="rp-card-title">Subscription Status</span>
                    <div class="rp-icon-pill rp-icon-emerald">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                    </div>
                </div>
                <div class="rp-card-value">
                    {{ $activeSubscriptions }} <span class="rp-card-sublabel">Active</span>
                </div>
                <div class="rp-card-footer">
                    <span>Expired: <strong style="color: #ef4444;">{{ $expiredSubscriptions }}</strong></span>
                    <span>Renewal Rate: <strong>95%</strong></span>
                </div>
            </div>

            <!-- Card 3: Monthly Sales & Revenue -->
            <div class="rp-card">
                <div class="rp-card-header">
                    <span class="rp-card-title">Revenue (This Month)</span>
                    <div class="rp-icon-pill rp-icon-purple">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                </div>
                <div class="rp-card-value">
                    Rs {{ number_format($monthlyRevenue, 2) }}
                </div>
                <div class="rp-card-footer">
                    <span>All-Time: <strong>Rs {{ number_format($totalRevenue, 2) }}</strong></span>
                </div>
            </div>

            <!-- Card 4: FBR Fiscalization Card -->
            <div class="rp-card">
                <div class="rp-card-header">
                    <span class="rp-card-title">FBR Acceptance Rate</span>
                    <div class="rp-icon-pill rp-icon-blue">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                </div>
                <div class="rp-card-value">
                    {{ $successRate }}%
                </div>
                <div class="rp-card-footer">
                    <span style="color: #10b981; font-weight: 700;">{{ $acceptedInvoices }} Accepted</span>
                    <span style="color: #ef4444; font-weight: 700;">{{ $failedInvoices }} Failed</span>
                </div>
            </div>
        </div>

        <!-- User / Business Invoicing Reports Table -->
        <div class="rp-table-box">
            <div class="rp-table-header">
                <div>
                    <h3 class="rp-table-heading">User-Wise Invoice & Fiscalization Breakdown</h3>
                    <p class="rp-table-desc">Summary of invoice volume, FBR acceptance metrics, and cumulative sales value per registered business.</p>
                </div>
                <div>
                    <span style="font-size: 12px; font-weight: 600; color: var(--rp-text-muted);">
                        Total Businesses: <strong>{{ count($businessReports) }}</strong>
                    </span>
                </div>
            </div>

            <div class="rp-table-scroll">
                <table class="rp-table">
                    <thead>
                        <tr>
                            <th>Business / Taxpayer</th>
                            <th>NTN / CNIC</th>
                            <th>Province</th>
                            <th style="text-align: center;">Total Invoices</th>
                            <th style="text-align: center;">FBR Accepted</th>
                            <th style="text-align: center;">FBR Failed</th>
                            <th style="text-align: right;">Total Sales (PKR)</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($businessReports as $b)
                            <tr>
                                <td style="font-weight: 700; color: var(--rp-text-main);">{{ $b->name }}</td>
                                <td style="font-family: monospace; font-size: 12px; color: var(--rp-text-muted);">{{ $b->ntn_cnic }}</td>
                                <td>{{ $b->province }}</td>
                                <td style="text-align: center; font-weight: 700;">{{ $b->total_invoices ?? 0 }}</td>
                                <td style="text-align: center; color: #10b981; font-weight: 700;">{{ $b->accepted_invoices ?? 0 }}</td>
                                <td style="text-align: center; color: #ef4444; font-weight: 700;">{{ $b->failed_invoices ?? 0 }}</td>
                                <td style="text-align: right; font-weight: 700;">Rs {{ number_format((float)($b->total_sales ?? 0), 2) }}</td>
                                <td style="text-align: center;">
                                    <span class="rp-badge {{ $b->status === 'approved' ? 'rp-badge-approved' : 'rp-badge-pending' }}">
                                        {{ ucfirst($b->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 36px; color: var(--rp-text-muted);">
                                    No business records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
