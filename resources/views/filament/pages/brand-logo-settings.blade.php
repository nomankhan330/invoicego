<x-filament-panels::page>
    <style>
        .ig-logo-preview-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        @media (max-width: 1024px) {
            .ig-logo-preview-grid {
                grid-template-columns: 1fr;
            }
        }
        .ig-logo-preview-card {
            border-radius: 14px;
            border: 1px solid #E2E8F0;
            background: #FFFFFF;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        }
        html.dark .ig-logo-preview-card {
            border-color: rgba(148, 163, 184, 0.2);
            background: #111827;
        }
        .ig-logo-preview-stage {
            height: 112px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .ig-logo-preview-stage.light-stage {
            background: #FFFFFF;
            background-image: radial-gradient(#E2E8F0 1px, transparent 1px);
            background-size: 14px 14px;
        }
        .ig-logo-preview-stage.dark-stage {
            background: #0F172A;
        }
        .ig-logo-preview-stage.blue-stage {
            background: linear-gradient(135deg, #1855E2 0%, #1E50FF 100%);
        }
        .ig-logo-blue-pill {
            background: #FFFFFF;
            padding: 10px 18px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2);
        }
        .ig-logo-preview-meta {
            padding: 12px 16px;
            border-top: 1px solid #E2E8F0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            font-size: 12px;
        }
        html.dark .ig-logo-preview-meta {
            border-top-color: rgba(148, 163, 184, 0.16);
        }
        .ig-logo-meta-title {
            font-weight: 700;
            color: #0F172A;
        }
        html.dark .ig-logo-meta-title {
            color: #F8FAFC;
        }
        .ig-logo-meta-sub {
            color: #64748B;
            font-size: 11px;
        }
        html.dark .ig-logo-meta-sub {
            color: #94A3B8;
        }
        .ig-logo-status-pill {
            font-size: 10.5px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 999px;
            background: #EFF6FF;
            color: #1E50FF;
            border: 1px solid #BFDBFE;
            white-space: nowrap;
        }
        html.dark .ig-logo-status-pill {
            background: rgba(30, 80, 255, 0.2);
            color: #60A5FA;
            border-color: rgba(96, 165, 250, 0.35);
        }
        .ig-logo-places-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            background: #F8FAFF;
            font-size: 12px;
            color: #334155;
        }
        html.dark .ig-logo-places-strip {
            border-color: rgba(148, 163, 184, 0.18);
            background: rgba(30, 80, 255, 0.08);
            color: #CBD5E1;
        }
        .ig-place-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            background: #FFFFFF;
            border: 1px solid #DBEAFE;
            color: #1D4ED8;
            font-weight: 600;
            font-size: 11.5px;
        }
        html.dark .ig-place-chip {
            background: #1E293B;
            border-color: rgba(96, 165, 250, 0.3);
            color: #93C5FD;
        }
    </style>

    <!-- Live 3-Context Logo Previews -->
    <div class="ig-logo-preview-grid">
        <div class="ig-logo-preview-card">
            <div class="ig-logo-preview-stage light-stage">
                <img src="{{ $lightLogoUrl }}" alt="Light Brand Logo" style="max-height: 52px; max-width: 230px; width: auto; object-fit: contain;">
            </div>
            <div class="ig-logo-preview-meta">
                <div>
                    <div class="ig-logo-meta-title">Light / Transparent Logo</div>
                    <div class="ig-logo-meta-sub">Website Navbar, User Portal, Print A4, Admin Light</div>
                </div>
                <span class="ig-logo-status-pill">{{ $isCustom ? 'Custom' : 'Default' }}</span>
            </div>
        </div>

        <div class="ig-logo-preview-card">
            <div class="ig-logo-preview-stage dark-stage">
                <img src="{{ $darkLogoUrl }}" alt="Dark Brand Logo" style="max-height: 52px; max-width: 230px; width: auto; object-fit: contain;">
            </div>
            <div class="ig-logo-preview-meta">
                <div>
                    <div class="ig-logo-meta-title">Dark Mode Logo</div>
                    <div class="ig-logo-meta-sub">Website Dark Footer &amp; Admin Dark Sidebar</div>
                </div>
                <span class="ig-logo-status-pill">{{ $isAutoDark ? 'Auto Dark' : 'Custom' }}</span>
            </div>
        </div>

        <div class="ig-logo-preview-card">
            <div class="ig-logo-preview-stage blue-stage">
                <div class="ig-logo-blue-pill">
                    <img src="{{ $lightLogoUrl }}" alt="Auth Pane Logo" style="max-height: 44px; max-width: 200px; width: auto; object-fit: contain; display: block;">
                </div>
            </div>
            <div class="ig-logo-preview-meta">
                <div>
                    <div class="ig-logo-meta-title">Login &amp; Register Badge</div>
                    <div class="ig-logo-meta-sub">Royal Blue Auth Panel Preview</div>
                </div>
                <span class="ig-logo-status-pill">{{ $isTransparent ? 'Transparent' : 'Original BG' }}</span>
            </div>
        </div>
    </div>

    <!-- Upload & Processing Form -->
    <form wire:submit="save">
        {{ $this->form }}

        <div style="margin-top: 20px; display: flex; align-items: center; gap: 12px;">
            <x-filament::button type="submit" icon="heroicon-o-cloud-arrow-up" size="lg">
                Save &amp; Apply Logo Everywhere
            </x-filament::button>
        </div>
    </form>

    <!-- Connected Locations Indicator -->
    <div class="ig-logo-places-strip">
        <strong style="margin-right: 4px;">Auto-Synced Locations:</strong>
        <span class="ig-place-chip">Website Navbar</span>
        <span class="ig-place-chip">Website Dark Footer</span>
        <span class="ig-place-chip">Admin Panel (Light &amp; Dark)</span>
        <span class="ig-place-chip">User Portal Sidebar</span>
        <span class="ig-place-chip">Login &amp; Register</span>
        <span class="ig-place-chip">Printable A4 FBR Invoices</span>
        <span class="ig-place-chip">Account Status Pages</span>
    </div>
</x-filament-panels::page>
