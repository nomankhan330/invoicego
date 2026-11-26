<div style="display: inline-flex; align-items: center; gap: 8px; line-height: 1;">
    <style>
        .ig-admin-logo-light { display: block !important; height: 34px; width: auto; max-width: 155px; object-fit: contain; }
        .ig-admin-logo-dark  { display: none !important;  height: 34px; width: auto; max-width: 155px; object-fit: contain; }
        html.dark .ig-admin-logo-light { display: none !important; }
        html.dark .ig-admin-logo-dark  { display: block !important; }
        .ig-admin-pill {
            background: #EFF6FF;
            color: #1E50FF;
            border: 1px solid #BFDBFE;
            font-size: 9.5px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            white-space: nowrap;
        }
        html.dark .ig-admin-pill {
            background: rgba(30, 80, 255, 0.18);
            color: #60A5FA;
            border-color: rgba(96, 165, 250, 0.35);
        }
    </style>
    <img src="{{ \App\Services\BrandLogoService::lightUrl() }}" alt="InvoiceGO" class="ig-admin-logo-light">
    <img src="{{ \App\Services\BrandLogoService::darkUrl() }}" alt="InvoiceGO" class="ig-admin-logo-dark">
    <span class="ig-admin-pill">Admin</span>
    <span class="sr-only">InvoiceGO Admin</span>
</div>
