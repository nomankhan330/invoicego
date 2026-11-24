<x-filament-panels::page>
    @php
        $totalCount = max(0, (int) ($stats['total'] ?? 0));
        $validPct = $totalCount > 0 ? round(($stats['successful'] / $totalCount) * 100, 1) : 0;
        $failedPct = $totalCount > 0 ? round(($stats['failed'] / $totalCount) * 100, 1) : 0;
        $pendingPct = $totalCount > 0 ? max(0, round(100 - $validPct - $failedPct, 1)) : 0;
    @endphp

    <div class="bia-wrap">
        <style>
            .bia-wrap {
                --bia-bg: #ffffff;
                --bia-bg-soft: #f8fafc;
                --bia-border: #e2e8f0;
                --bia-divider: #f1f5f9;
                --bia-text: #0f172a;
                --bia-muted: #64748b;
                --bia-subtle: #475569;
                --bia-th-bg: #f8fafc;
                --bia-th-text: #475569;
                --bia-hover: #f8fafc;
                --bia-input-bg: #ffffff;
                --bia-input-border: #cbd5e1;
                --bia-shadow: 0 1px 3px rgba(15, 23, 42, 0.06), 0 1px 2px rgba(15, 23, 42, 0.04);
                --bia-primary: #1e50ff;
                --bia-primary-soft: #eff6ff;
                --bia-primary-text: #1e50ff;
                --bia-success: #059669;
                --bia-success-soft: #ecfdf5;
                --bia-danger: #e11d48;
                --bia-danger-soft: #fff1f2;
                --bia-warning: #d97706;
                --bia-warning-soft: #fffbeb;
                --bia-track: #e2e8f0;
                display: flex;
                flex-direction: column;
                gap: 22px;
            }

            :is(.dark) .bia-wrap,
            .dark .bia-wrap,
            html.dark .bia-wrap {
                --bia-bg: #111827;
                --bia-bg-soft: #182234;
                --bia-border: #263349;
                --bia-divider: #1f2b3f;
                --bia-text: #f8fafc;
                --bia-muted: #94a3b8;
                --bia-subtle: #cbd5e1;
                --bia-th-bg: #0b1120;
                --bia-th-text: #94a3b8;
                --bia-hover: #1a263b;
                --bia-input-bg: #0f172a;
                --bia-input-border: #334155;
                --bia-shadow: 0 6px 18px rgba(0, 0, 0, 0.35);
                --bia-primary: #3b82f6;
                --bia-primary-soft: rgba(30, 80, 255, 0.16);
                --bia-primary-text: #60a5fa;
                --bia-success: #34d399;
                --bia-success-soft: rgba(16, 185, 129, 0.16);
                --bia-danger: #fb7185;
                --bia-danger-soft: rgba(244, 63, 94, 0.16);
                --bia-warning: #fbbf24;
                --bia-warning-soft: rgba(245, 158, 11, 0.16);
                --bia-track: #1e293b;
            }

            /* Hero Filter Bar */
            .bia-hero {
                background: var(--bia-bg);
                border: 1px solid var(--bia-border);
                border-left: 4px solid #1e50ff;
                border-radius: 14px;
                padding: 20px 24px;
                box-shadow: var(--bia-shadow);
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
            }
            .bia-hero-left {
                display: flex;
                align-items: center;
                gap: 14px;
            }
            .bia-hero-icon {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                background: var(--bia-primary-soft);
                color: var(--bia-primary-text);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .bia-hero-title {
                font-size: 18px;
                font-weight: 800;
                color: var(--bia-text);
                margin: 0 0 3px 0;
                letter-spacing: -0.01em;
            }
            .bia-hero-sub {
                font-size: 13px;
                color: var(--bia-muted);
                margin: 0;
            }
            .bia-hero-controls {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 10px;
            }
            .bia-select {
                background-color: var(--bia-input-bg);
                color: var(--bia-text);
                border: 1px solid var(--bia-input-border);
                border-radius: 9px;
                padding: 9px 14px;
                font-size: 13.5px;
                font-weight: 500;
                min-width: 260px;
                outline: none;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
            }
            .bia-select:focus {
                border-color: #1e50ff;
                box-shadow: 0 0 0 3px rgba(30, 80, 255, 0.2);
            }
            .bia-btn-back {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                background: #1e50ff;
                color: #ffffff !important;
                font-size: 13px;
                font-weight: 600;
                padding: 9px 16px;
                border-radius: 9px;
                text-decoration: none;
                transition: background 0.15s ease, transform 0.15s ease;
            }
            .bia-btn-back:hover {
                background: #1541d9;
                transform: translateY(-1px);
            }
            .bia-btn-reset {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: var(--bia-bg-soft);
                color: var(--bia-subtle);
                border: 1px solid var(--bia-border);
                font-size: 13px;
                font-weight: 600;
                padding: 9px 14px;
                border-radius: 9px;
                cursor: pointer;
                transition: all 0.15s ease;
            }
            .bia-btn-reset:hover {
                border-color: #1e50ff;
                color: var(--bia-primary-text);
            }

            /* KPI Cards Grid (6 Cards: 3x2 on desktop) */
            .bia-kpi-grid {
                display: grid;
                grid-template-columns: repeat(1, minmax(0, 1fr));
                gap: 16px;
            }
            @media (min-width: 640px) {
                .bia-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            }
            @media (min-width: 1024px) {
                .bia-kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            }
            .bia-kpi-card {
                background: var(--bia-bg);
                border: 1px solid var(--bia-border);
                border-radius: 14px;
                padding: 20px 22px;
                box-shadow: var(--bia-shadow);
                transition: transform 0.15s ease, border-color 0.15s ease;
            }
            .bia-kpi-card:hover {
                transform: translateY(-2px);
                border-color: #1e50ff;
            }
            .bia-kpi-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 12px;
            }
            .bia-kpi-label {
                font-size: 11.5px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                color: var(--bia-muted);
            }
            .bia-icon-pill {
                width: 38px;
                height: 38px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .bia-pill-blue { background: var(--bia-primary-soft); color: var(--bia-primary-text); }
            .bia-pill-green { background: var(--bia-success-soft); color: var(--bia-success); }
            .bia-pill-red { background: var(--bia-danger-soft); color: var(--bia-danger); }
            .bia-pill-amber { background: var(--bia-warning-soft); color: var(--bia-warning); }

            .bia-kpi-value {
                font-size: 28px;
                font-weight: 800;
                color: var(--bia-text);
                line-height: 1.15;
                letter-spacing: -0.02em;
            }
            .bia-kpi-value.money {
                font-size: 23px;
            }
            .bia-kpi-value.green { color: var(--bia-success); }
            .bia-kpi-value.red { color: var(--bia-danger); }
            .bia-kpi-value.amber { color: var(--bia-warning); }
            .bia-kpi-foot {
                margin-top: 10px;
                font-size: 12px;
                color: var(--bia-muted);
            }

            /* FBR Success Rate Visual Progress Card */
            .bia-progress-card {
                background: var(--bia-bg);
                border: 1px solid var(--bia-border);
                border-radius: 14px;
                padding: 18px 22px;
                box-shadow: var(--bia-shadow);
            }
            .bia-progress-head {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                margin-bottom: 12px;
            }
            .bia-progress-title {
                font-size: 13.5px;
                font-weight: 700;
                color: var(--bia-text);
            }
            .bia-legend {
                display: flex;
                flex-wrap: wrap;
                gap: 16px;
                font-size: 12px;
                color: var(--bia-subtle);
            }
            .bia-legend-dot {
                display: inline-block;
                width: 9px;
                height: 9px;
                border-radius: 9999px;
                margin-right: 5px;
            }
            .bia-track {
                width: 100%;
                height: 10px;
                background: var(--bia-track);
                border-radius: 9999px;
                overflow: hidden;
                display: flex;
            }

            /* Daily / Monthly / Yearly Grid */
            .bia-period-grid {
                display: grid;
                grid-template-columns: repeat(1, minmax(0, 1fr));
                gap: 16px;
            }
            @media (min-width: 768px) {
                .bia-period-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            }
            .bia-period-card {
                background: var(--bia-bg);
                border: 1px solid var(--bia-border);
                border-radius: 14px;
                padding: 20px 22px;
                box-shadow: var(--bia-shadow);
            }
            .bia-period-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                padding: 4px 10px;
                border-radius: 9999px;
                background: var(--bia-primary-soft);
                color: var(--bia-primary-text);
                margin-bottom: 14px;
            }
            .bia-period-main {
                display: flex;
                align-items: baseline;
                justify-content: space-between;
                gap: 10px;
                margin-bottom: 14px;
            }
            .bia-period-count {
                font-size: 24px;
                font-weight: 800;
                color: var(--bia-text);
            }
            .bia-period-chips {
                display: flex;
                gap: 6px;
                font-size: 11.5px;
                font-weight: 700;
            }
            .bia-chip-ok {
                background: var(--bia-success-soft);
                color: var(--bia-success);
                padding: 3px 8px;
                border-radius: 6px;
            }
            .bia-chip-fail {
                background: var(--bia-danger-soft);
                color: var(--bia-danger);
                padding: 3px 8px;
                border-radius: 6px;
            }
            .bia-period-rows {
                border-top: 1px solid var(--bia-divider);
                padding-top: 12px;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .bia-period-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 13px;
                color: var(--bia-muted);
            }
            .bia-period-row strong {
                color: var(--bia-text);
                font-weight: 700;
            }

            /* Data Table Cards */
            .bia-table-card {
                background: var(--bia-bg);
                border: 1px solid var(--bia-border);
                border-radius: 14px;
                box-shadow: var(--bia-shadow);
                overflow: hidden;
            }
            .bia-table-head {
                padding: 18px 22px;
                border-bottom: 1px solid var(--bia-divider);
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }
            .bia-table-title {
                font-size: 15.5px;
                font-weight: 700;
                color: var(--bia-text);
                margin: 0;
            }
            .bia-count-pill {
                font-size: 11.5px;
                font-weight: 700;
                padding: 4px 10px;
                border-radius: 9999px;
                background: var(--bia-primary-soft);
                color: var(--bia-primary-text);
            }
            .bia-table-scroll {
                overflow-x: auto;
            }
            .bia-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 13.5px;
            }
            .bia-table thead th {
                background: var(--bia-th-bg);
                color: var(--bia-th-text);
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                padding: 12px 18px;
                text-align: left;
                border-bottom: 1px solid var(--bia-border);
                white-space: nowrap;
            }
            .bia-table thead th.num,
            .bia-table tbody td.num {
                text-align: right;
            }
            .bia-table tbody td {
                padding: 14px 18px;
                color: var(--bia-subtle);
                border-bottom: 1px solid var(--bia-divider);
                vertical-align: middle;
            }
            .bia-table tbody tr:last-child td {
                border-bottom: none;
            }
            .bia-table tbody tr:hover td {
                background: var(--bia-hover);
            }
            .bia-link-btn {
                background: none;
                border: none;
                padding: 0;
                color: var(--bia-primary-text);
                font-weight: 700;
                cursor: pointer;
                text-decoration: none;
                font-size: 13.5px;
            }
            .bia-link-btn:hover {
                text-decoration: underline;
            }
            .bia-mono {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                font-size: 12.5px;
            }
            .bia-status-badge {
                display: inline-flex;
                align-items: center;
                padding: 3px 10px;
                border-radius: 9999px;
                font-size: 11.5px;
                font-weight: 700;
            }
            .bia-badge-valid { background: var(--bia-success-soft); color: var(--bia-success); }
            .bia-badge-failed { background: var(--bia-danger-soft); color: var(--bia-danger); }
            .bia-badge-pending { background: var(--bia-warning-soft); color: var(--bia-warning); }
            .bia-badge-cancelled { background: var(--bia-bg-soft); color: var(--bia-muted); border: 1px solid var(--bia-border); }
            .bia-empty {
                padding: 36px 20px;
                text-align: center;
                color: var(--bia-muted);
                font-size: 13.5px;
            }
        </style>

        {{-- 1. Hero Business Filter Banner --}}
        <div class="bia-hero">
            <div class="bia-hero-left">
                <div class="bia-hero-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3v18h18"></path>
                        <path d="M18 17V9"></path>
                        <path d="M13 17V5"></path>
                        <path d="M8 17v-3"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="bia-hero-title">
                        {{ $selectedBusiness ? $selectedBusiness->name . ' — Invoice History & Analytics' : 'All Businesses — Invoice Analytics & Reports' }}
                    </h2>
                    <p class="bia-hero-sub">
                        {{ $selectedBusiness
                            ? 'NTN: ' . $selectedBusiness->ntn_cnic . ' · Province: ' . ($selectedBusiness->province ?? 'Pakistan') . ' · Real-time FBR invoice performance'
                            : 'Select any registered business to inspect business-wise invoice counts, sales, tax collected, and FBR compliance.' }}
                    </p>
                </div>
            </div>

            <div class="bia-hero-controls">
                <select wire:model.live="selectedBusinessId" class="bia-select">
                    <option value="">All Registered Businesses ({{ $businesses->count() }})</option>
                    @foreach ($businesses as $biz)
                        <option value="{{ $biz->id }}">{{ $biz->name }} (NTN: {{ $biz->ntn_cnic }})</option>
                    @endforeach
                </select>

                @if ($selectedBusiness)
                    <button type="button" wire:click="$set('selectedBusinessId', null)" class="bia-btn-reset">
                        Show All
                    </button>
                @endif

                <a href="{{ \App\Filament\Resources\InvoiceResource::getUrl('index') }}" class="bia-btn-back">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5"></path>
                        <path d="M12 19l-7-7 7-7"></path>
                    </svg>
                    <span>Back to All Invoices</span>
                </a>
            </div>
        </div>

        {{-- 2. 6 Executive KPI Summary Cards --}}
        <div class="bia-kpi-grid">
            <div class="bia-kpi-card">
                <div class="bia-kpi-top">
                    <span class="bia-kpi-label">Total Invoices</span>
                    <span class="bia-icon-pill bia-pill-blue">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </span>
                </div>
                <div class="bia-kpi-value">{{ number_format($stats['total']) }}</div>
                <div class="bia-kpi-foot">{{ $stats['cancelled'] ?? 0 }} cancelled records included</div>
            </div>

            <div class="bia-kpi-card">
                <div class="bia-kpi-top">
                    <span class="bia-kpi-label">Successful (FBR Valid)</span>
                    <span class="bia-icon-pill bia-pill-green">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </span>
                </div>
                <div class="bia-kpi-value green">{{ number_format($stats['successful']) }}</div>
                <div class="bia-kpi-foot">{{ $validPct }}% FBR acceptance rate</div>
            </div>

            <div class="bia-kpi-card">
                <div class="bia-kpi-top">
                    <span class="bia-kpi-label">Failed / Rejected</span>
                    <span class="bia-icon-pill bia-pill-red">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </span>
                </div>
                <div class="bia-kpi-value red">{{ number_format($stats['failed']) }}</div>
                <div class="bia-kpi-foot">{{ $failedPct }}% rejection rate</div>
            </div>

            <div class="bia-kpi-card">
                <div class="bia-kpi-top">
                    <span class="bia-kpi-label">Pending / Draft</span>
                    <span class="bia-icon-pill bia-pill-amber">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </span>
                </div>
                <div class="bia-kpi-value amber">{{ number_format($stats['pending']) }}</div>
                <div class="bia-kpi-foot">Awaiting FBR submission or reconciliation</div>
            </div>

            <div class="bia-kpi-card">
                <div class="bia-kpi-top">
                    <span class="bia-kpi-label">Total Sales Volume</span>
                    <span class="bia-icon-pill bia-pill-blue">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                    </span>
                </div>
                <div class="bia-kpi-value money">PKR {{ number_format($stats['total_sales'], 2) }}</div>
                <div class="bia-kpi-foot">Gross active invoice value (excl. cancelled)</div>
            </div>

            <div class="bia-kpi-card">
                <div class="bia-kpi-top">
                    <span class="bia-kpi-label">Total Sales Tax Collected</span>
                    <span class="bia-icon-pill bia-pill-green">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </span>
                </div>
                <div class="bia-kpi-value money green">PKR {{ number_format($stats['total_tax'], 2) }}</div>
                <div class="bia-kpi-foot">Cumulative FBR sales tax reported</div>
            </div>
        </div>

        {{-- 3. FBR Success vs Failure Compliance Progress Bar --}}
        <div class="bia-progress-card">
            <div class="bia-progress-head">
                <div class="bia-progress-title">FBR Success vs Failure Compliance Breakdown</div>
                <div class="bia-legend">
                    <span><span class="bia-legend-dot" style="background: #10b981;"></span>Valid: <strong>{{ $validPct }}%</strong></span>
                    <span><span class="bia-legend-dot" style="background: #f43f5e;"></span>Failed: <strong>{{ $failedPct }}%</strong></span>
                    <span><span class="bia-legend-dot" style="background: #f59e0b;"></span>Pending / Other: <strong>{{ $pendingPct }}%</strong></span>
                </div>
            </div>
            <div class="bia-track">
                @if ($validPct > 0)
                    <div style="width: {{ $validPct }}%; background: linear-gradient(90deg, #10b981, #059669); height: 100%;"></div>
                @endif
                @if ($failedPct > 0)
                    <div style="width: {{ $failedPct }}%; background: #f43f5e; height: 100%;"></div>
                @endif
                @if ($pendingPct > 0)
                    <div style="width: {{ $pendingPct }}%; background: #f59e0b; height: 100%;"></div>
                @endif
            </div>
        </div>

        {{-- 4. Daily / Monthly / Yearly Period Analytics --}}
        <div class="bia-period-grid">
            @foreach ($periodStats as $p)
                <div class="bia-period-card">
                    <div class="bia-period-badge">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ $p['label'] }}</span>
                    </div>
                    <div class="bia-period-main">
                        <div class="bia-period-count">{{ number_format($p['count']) }} Invoices</div>
                        <div class="bia-period-chips">
                            <span class="bia-chip-ok">{{ $p['accepted'] }} Valid</span>
                            <span class="bia-chip-fail">{{ $p['failed'] }} Failed</span>
                        </div>
                    </div>
                    <div class="bia-period-rows">
                        <div class="bia-period-row">
                            <span>Total Sales</span>
                            <strong>PKR {{ number_format($p['sales'], 2) }}</strong>
                        </div>
                        <div class="bia-period-row">
                            <span>Sales Tax Collected</span>
                            <strong style="color: var(--bia-success);">PKR {{ number_format($p['tax'], 2) }}</strong>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- 5. Business-Wise Comparison Report Table --}}
        @if (!$selectedBusiness)
            <div class="bia-table-card">
                <div class="bia-table-head">
                    <h3 class="bia-table-title">Business-Wise Invoice Report</h3>
                    <span class="bia-count-pill">{{ $businessBreakdown->count() }} Businesses</span>
                </div>
                <div class="bia-table-scroll">
                    <table class="bia-table">
                        <thead>
                            <tr>
                                <th>Business</th>
                                <th>NTN / CNIC</th>
                                <th class="num">Total</th>
                                <th class="num">Successful</th>
                                <th class="num">Failed</th>
                                <th class="num">Pending</th>
                                <th class="num">Total Sales (PKR)</th>
                                <th class="num">Sales Tax (PKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($businessBreakdown as $b)
                                <tr>
                                    <td>
                                        <button type="button" wire:click="$set('selectedBusinessId', {{ $b->id }})" class="bia-link-btn">
                                            {{ $b->name }}
                                        </button>
                                    </td>
                                    <td class="bia-mono">{{ $b->ntn_cnic }}</td>
                                    <td class="num" style="font-weight: 700; color: var(--bia-text);">{{ number_format($b->total_invoices) }}</td>
                                    <td class="num" style="font-weight: 700; color: var(--bia-success);">{{ number_format($b->accepted_invoices) }}</td>
                                    <td class="num" style="font-weight: 700; color: var(--bia-danger);">{{ number_format($b->failed_invoices) }}</td>
                                    <td class="num" style="font-weight: 600; color: var(--bia-warning);">{{ number_format($b->pending_invoices) }}</td>
                                    <td class="num" style="font-weight: 700; color: var(--bia-text);">PKR {{ number_format((float) $b->total_sales_amount, 2) }}</td>
                                    <td class="num" style="font-weight: 600; color: var(--bia-success);">PKR {{ number_format((float) $b->total_tax_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="bia-empty">No registered businesses found yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- 6. Recent Invoice History Table --}}
        <div class="bia-table-card">
            <div class="bia-table-head">
                <h3 class="bia-table-title">
                    {{ $selectedBusiness ? $selectedBusiness->name . ' — Recent Invoice History' : 'Recent Invoices (All Businesses)' }}
                </h3>
                <span class="bia-count-pill">Latest {{ $recentInvoices->count() }}</span>
            </div>
            <div class="bia-table-scroll">
                <table class="bia-table">
                    <thead>
                        <tr>
                            <th>Invoice ID</th>
                            <th>Invoice #</th>
                            <th>Business</th>
                            <th>Buyer</th>
                            <th>Date</th>
                            <th class="num">Total Amount</th>
                            <th class="num">Sales Tax</th>
                            <th>Status</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentInvoices as $inv)
                            <tr>
                                <td class="bia-mono">INV-{{ str_pad((string) $inv->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <a href="{{ \App\Filament\Resources\InvoiceResource::getUrl('view', ['record' => $inv->id]) }}" class="bia-link-btn">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td style="font-weight: 600; color: var(--bia-text);">{{ $inv->business->name ?? '—' }}</td>
                                <td>{{ $inv->buyer_name ?: ($inv->customer->name ?? '—') }}</td>
                                <td style="white-space: nowrap;">{{ $inv->invoice_date?->format('d M Y') }}</td>
                                <td class="num" style="font-weight: 700; color: var(--bia-text);">PKR {{ number_format((float) $inv->total_invoice_value, 2) }}</td>
                                <td class="num" style="color: var(--bia-success); font-weight: 600;">PKR {{ number_format((float) $inv->sales_tax_amount, 2) }}</td>
                                <td>
                                    @if ($inv->status === 'accepted')
                                        <span class="bia-status-badge bia-badge-valid">Valid</span>
                                    @elseif ($inv->status === 'failed')
                                        <span class="bia-status-badge bia-badge-failed">Failed</span>
                                    @elseif ($inv->status === 'cancelled')
                                        <span class="bia-status-badge bia-badge-cancelled">Cancelled</span>
                                    @else
                                        <span class="bia-status-badge bia-badge-pending">{{ ucfirst(str_replace('_', ' ', $inv->status)) }}</span>
                                    @endif
                                </td>
                                <td class="bia-mono">{{ $inv->creator?->user_code ?? ($inv->creator?->name ?? '—') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="bia-empty">No invoices recorded yet for this selection.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
