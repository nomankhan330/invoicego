<x-filament-widgets::widget>
    <div class="um-stats-wrap">
        <style>
            .um-stats-wrap {
                --um-bg: #ffffff;
                --um-border: #e2e8f0;
                --um-text: #0f172a;
                --um-muted: #64748b;
                --um-shadow: 0 1px 3px rgba(15, 23, 42, 0.06), 0 1px 2px rgba(15, 23, 42, 0.04);
                --um-blue: #1e50ff;
                --um-blue-soft: #eff6ff;
                --um-green: #059669;
                --um-green-soft: #ecfdf5;
                --um-amber: #d97706;
                --um-amber-soft: #fffbeb;
                --um-red: #e11d48;
                --um-red-soft: #fff1f2;
                --um-sky: #0284c7;
                --um-sky-soft: #f0f9ff;
            }

            :is(.dark) .um-stats-wrap,
            .dark .um-stats-wrap,
            html.dark .um-stats-wrap {
                --um-bg: #111827;
                --um-border: #243044;
                --um-text: #f8fafc;
                --um-muted: #94a3b8;
                --um-shadow: 0 6px 18px rgba(0, 0, 0, 0.32);
                --um-blue: #60a5fa;
                --um-blue-soft: rgba(30, 80, 255, 0.16);
                --um-green: #34d399;
                --um-green-soft: rgba(16, 185, 129, 0.16);
                --um-amber: #fbbf24;
                --um-amber-soft: rgba(245, 158, 11, 0.16);
                --um-red: #fb7185;
                --um-red-soft: rgba(244, 63, 94, 0.16);
                --um-sky: #38bdf8;
                --um-sky-soft: rgba(14, 165, 233, 0.16);
            }

            .um-stats-grid {
                display: grid;
                grid-template-columns: repeat(1, minmax(0, 1fr));
                gap: 14px;
            }
            @media (min-width: 640px) {
                .um-stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            }
            @media (min-width: 900px) {
                .um-stats-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            }
            @media (min-width: 1200px) {
                .um-stats-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); }
            }

            .um-stat-card {
                background: var(--um-bg);
                border: 1px solid var(--um-border);
                border-radius: 14px;
                padding: 18px 20px;
                box-shadow: var(--um-shadow);
                position: relative;
                overflow: hidden;
                transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
            }
            .um-stat-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
            }
            .um-stat-card.accent-blue::before { background: linear-gradient(90deg, #1e50ff, #3b82f6); }
            .um-stat-card.accent-green::before { background: linear-gradient(90deg, #10b981, #059669); }
            .um-stat-card.accent-amber::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
            .um-stat-card.accent-red::before { background: linear-gradient(90deg, #f43f5e, #e11d48); }
            .um-stat-card.accent-sky::before { background: linear-gradient(90deg, #0ea5e9, #2563eb); }

            .um-stat-card:hover {
                transform: translateY(-2px);
                border-color: #1e50ff;
            }

            .um-stat-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                margin-bottom: 12px;
            }
            .um-stat-label {
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--um-muted);
            }
            .um-stat-icon {
                width: 34px;
                height: 34px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .um-icon-blue { background: var(--um-blue-soft); color: var(--um-blue); }
            .um-icon-green { background: var(--um-green-soft); color: var(--um-green); }
            .um-icon-amber { background: var(--um-amber-soft); color: var(--um-amber); }
            .um-icon-red { background: var(--um-red-soft); color: var(--um-red); }
            .um-icon-sky { background: var(--um-sky-soft); color: var(--um-sky); }

            .um-stat-val {
                font-size: 28px;
                font-weight: 800;
                line-height: 1.1;
                color: var(--um-text);
                letter-spacing: -0.02em;
                margin-bottom: 8px;
                font-variant-numeric: tabular-nums;
            }
            .um-stat-val.green { color: var(--um-green); }
            .um-stat-val.amber { color: var(--um-amber); }
            .um-stat-val.red { color: var(--um-red); }
            .um-stat-val.sky { color: var(--um-sky); }

            .um-stat-sub {
                font-size: 11.5px;
                color: var(--um-muted);
                display: flex;
                align-items: center;
                gap: 5px;
            }
        </style>

        <div class="um-stats-grid">
            {{-- 1. Total Users --}}
            <div class="um-stat-card accent-blue">
                <div class="um-stat-top">
                    <span class="um-stat-label">Total Users</span>
                    <span class="um-stat-icon um-icon-blue">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                </div>
                <div class="um-stat-val">{{ number_format($total) }}</div>
                <div class="um-stat-sub">All registered accounts</div>
            </div>

            {{-- 2. Active Users --}}
            <div class="um-stat-card accent-green">
                <div class="um-stat-top">
                    <span class="um-stat-label">Active Users</span>
                    <span class="um-stat-icon um-icon-green">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </span>
                </div>
                <div class="um-stat-val green">{{ number_format($active) }}</div>
                <div class="um-stat-sub">{{ $activePct }}% authorized to sign in</div>
            </div>

            {{-- 3. Inactive Users --}}
            <div class="um-stat-card accent-amber">
                <div class="um-stat-top">
                    <span class="um-stat-label">Inactive Users</span>
                    <span class="um-stat-icon um-icon-amber">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </span>
                </div>
                <div class="um-stat-val amber">{{ number_format($inactive) }}</div>
                <div class="um-stat-sub">Draft or deactivated</div>
            </div>

            {{-- 4. Suspended Users --}}
            <div class="um-stat-card accent-red">
                <div class="um-stat-top">
                    <span class="um-stat-label">Suspended Users</span>
                    <span class="um-stat-icon um-icon-red">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </span>
                </div>
                <div class="um-stat-val red">{{ number_format($suspended) }}</div>
                <div class="um-stat-sub">Blocked from portal</div>
            </div>

            {{-- 5. Newly Created Users --}}
            <div class="um-stat-card accent-sky">
                <div class="um-stat-top">
                    <span class="um-stat-label">Newly Created Users</span>
                    <span class="um-stat-icon um-icon-sky">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    </span>
                </div>
                <div class="um-stat-val sky">{{ number_format($newlyCreated) }}</div>
                <div class="um-stat-sub">Joined in last 7 days</div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
