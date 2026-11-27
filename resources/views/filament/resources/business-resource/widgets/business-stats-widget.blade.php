<x-filament-widgets::widget>
    <style>
        .ig-biz-kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
            width: 100%;
        }
        @media (max-width: 1200px) {
            .ig-biz-kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 768px) {
            .ig-biz-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .ig-biz-kpi-card {
            position: relative;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
            transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
            overflow: hidden;
        }
        .ig-biz-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.07);
        }
        html.dark .ig-biz-kpi-card {
            background: #111827;
            border-color: rgba(148, 163, 184, 0.18);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);
        }
        .ig-biz-kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
        }
        .ig-biz-kpi-card.tone-blue::before    { background: #1E50FF; }
        .ig-biz-kpi-card.tone-emerald::before { background: #10B981; }
        .ig-biz-kpi-card.tone-amber::before   { background: #F59E0B; }
        .ig-biz-kpi-card.tone-rose::before    { background: #EF4444; }
        .ig-biz-kpi-card.tone-sky::before     { background: #0EA5E9; }

        .ig-biz-kpi-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748B;
            margin-bottom: 4px;
        }
        html.dark .ig-biz-kpi-label { color: #94A3B8; }

        .ig-biz-kpi-val {
            font-size: 26px;
            font-weight: 800;
            line-height: 1.1;
            color: #0F172A;
        }
        html.dark .ig-biz-kpi-val { color: #F8FAFC; }

        .ig-biz-kpi-desc {
            font-size: 11.5px;
            margin-top: 4px;
            font-weight: 600;
        }
        .tone-blue .ig-biz-kpi-desc    { color: #1E50FF; }
        .tone-emerald .ig-biz-kpi-desc { color: #059669; }
        .tone-amber .ig-biz-kpi-desc   { color: #D97706; }
        .tone-rose .ig-biz-kpi-desc    { color: #DC2626; }
        .tone-sky .ig-biz-kpi-desc     { color: #0284C7; }
        html.dark .tone-blue .ig-biz-kpi-desc    { color: #60A5FA; }
        html.dark .tone-emerald .ig-biz-kpi-desc { color: #34D399; }
        html.dark .tone-amber .ig-biz-kpi-desc   { color: #FBBF24; }
        html.dark .tone-rose .ig-biz-kpi-desc    { color: #F87171; }
        html.dark .tone-sky .ig-biz-kpi-desc     { color: #38BDF8; }

        .ig-biz-kpi-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .tone-blue .ig-biz-kpi-icon    { background: #EFF6FF; color: #1E50FF; }
        .tone-emerald .ig-biz-kpi-icon { background: #ECFDF5; color: #059669; }
        .tone-amber .ig-biz-kpi-icon   { background: #FFFBEB; color: #D97706; }
        .tone-rose .ig-biz-kpi-icon    { background: #FEF2F2; color: #DC2626; }
        .tone-sky .ig-biz-kpi-icon     { background: #F0F9FF; color: #0284C7; }
        html.dark .tone-blue .ig-biz-kpi-icon    { background: rgba(30, 80, 255, 0.16); color: #60A5FA; }
        html.dark .tone-emerald .ig-biz-kpi-icon { background: rgba(16, 185, 129, 0.16); color: #34D399; }
        html.dark .tone-amber .ig-biz-kpi-icon   { background: rgba(245, 158, 11, 0.16); color: #FBBF24; }
        html.dark .tone-rose .ig-biz-kpi-icon    { background: rgba(239, 68, 68, 0.16); color: #F87171; }
        html.dark .tone-sky .ig-biz-kpi-icon     { background: rgba(14, 165, 233, 0.16); color: #38BDF8; }
    </style>

    <div class="ig-biz-kpi-grid">
        @foreach ($cards as $card)
            <div class="ig-biz-kpi-card tone-{{ $card['tone'] }}">
                <div style="min-width: 0;">
                    <div class="ig-biz-kpi-label">{{ $card['label'] }}</div>
                    <div class="ig-biz-kpi-val">{{ $card['value'] }}</div>
                    <div class="ig-biz-kpi-desc">{{ $card['desc'] }}</div>
                </div>
                <div class="ig-biz-kpi-icon">
                    @if ($card['icon'] === 'building')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/></svg>
                    @elseif ($card['icon'] === 'check')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    @elseif ($card['icon'] === 'clock')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    @elseif ($card['icon'] === 'ban')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    @else
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
