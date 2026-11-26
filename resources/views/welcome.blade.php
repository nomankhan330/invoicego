@php
    $registrationEnabled = \App\Models\SystemSetting::get('enable_public_registration', false);
    $showPricingPlans = \App\Models\SystemSetting::get('show_pricing_plans', false);
    $defaultInvoiceLimit = (int) \App\Models\SystemSetting::get('default_invoice_limit', 50);
    try {
        $pricingPlans = $showPricingPlans ? \App\Models\PricingPlan::active()->ordered()->get() : collect();
    } catch (\Throwable) {
        $pricingPlans = collect();
    }

    if ($showPricingPlans && $pricingPlans->isEmpty()) {
        $pricingPlans = collect([
            (object)[
                'name' => 'Starter',
                'tagline' => 'Ideal for small traders and new businesses.',
                'price' => 'PKR 2,500',
                'billing_period' => '/month',
                'features' => [
                    'Up to 250 Invoices / mo',
                    'Real-time FBR API Sync',
                    'A4 Verified PDF Invoices',
                    'Standard Email Support',
                ],
                'button_text' => 'Get Started',
                'button_action' => 'register',
                'is_popular' => false,
            ],
            (object)[
                'name' => 'Professional',
                'tagline' => 'For distributors and growing enterprises.',
                'price' => 'PKR 5,000',
                'billing_period' => '/month',
                'features' => [
                    'Up to 2,500 Invoices / mo',
                    'Bulk Excel / CSV Upload',
                    'All 28 Sandbox Scenarios',
                    'Multi-User Staff Access',
                    'Priority WhatsApp Support',
                ],
                'button_text' => 'Start 14-Day Free Trial',
                'button_action' => 'register',
                'is_popular' => true,
            ],
            (object)[
                'name' => 'Enterprise',
                'tagline' => 'Custom integration for high-volume POS & ERP.',
                'price' => 'Custom',
                'billing_period' => '/volume',
                'features' => [
                    'Unlimited Monthly Invoices',
                    'Direct ERP / POS Webhooks',
                    'Dedicated Account Manager',
                    '99.9% Guaranteed SLA',
                ],
                'button_text' => 'Contact Sales',
                'button_action' => 'contact',
                'is_popular' => false,
            ],
        ]);
    }

    $showFaqSection = \App\Models\SystemSetting::get('show_faq_section', true);
    try {
        $faqs = $showFaqSection ? \App\Models\Faq::active()->ordered()->get() : collect();
    } catch (\Throwable) {
        $faqs = collect();
    }

    if ($showFaqSection && $faqs->isEmpty()) {
        $faqs = collect([
            (object)[
                'id' => 1,
                'question' => 'What is InvoiceGO?',
                'answer' => "InvoiceGO is Pakistan's leading cloud-based digital sales invoicing and FBR integration platform. It enables businesses to prepare, validate, electronically transmit, and archive sales invoices in 100% compliance with official FBR Digital Invoicing (DI) API v1.12 regulations.",
            ],
            (object)[
                'id' => 2,
                'question' => 'What is FBR Digital Invoicing?',
                'answer' => "FBR Digital Invoicing is the electronic transmission of sales tax invoices from registered taxpayers to the Federal Board of Revenue's centralized system in real time. Each accepted invoice receives a unique FBR Invoice Number and verified QR code.",
            ],
            (object)[
                'id' => 3,
                'question' => 'How can I create an account?',
                'answer' => 'Click on "Get Started" or "Create Account". Enter your business details, legal name, NTN or CNIC, STRN (if applicable), and province. Your workspace is configured immediately with full sandbox testing access.',
            ],
            (object)[
                'id' => 4,
                'question' => 'Can I manage multiple invoices?',
                'answer' => 'Yes! InvoiceGO allows you to create individual invoices, save incomplete invoices as drafts without restrictive validations, and upload hundreds of invoices in bulk using our standardized Excel / CSV bulk upload template.',
            ],
            (object)[
                'id' => 5,
                'question' => 'Does InvoiceGO support FBR API integration?',
                'answer' => 'Yes, InvoiceGO integrates directly with the official FBR PRAL Digital Invoicing API v1.12. It includes a built-in runner for all 28 technical sandbox scenarios (SN001–SN028) needed to qualify for your production API token.',
            ],
            (object)[
                'id' => 6,
                'question' => 'Can I access InvoiceGO from my mobile?',
                'answer' => 'Yes. InvoiceGO is 100% web-based and cloud-native. You can access your dashboard, monitor daily revenue, and issue or print invoices from any desktop, laptop, tablet, or smartphone without installing any software.',
            ],
            (object)[
                'id' => 7,
                'question' => 'Is my business information secure?',
                'answer' => 'Security is our top priority. We use AES-256 bank-grade encryption for all database records and API tokens. FBR tokens are encrypted on the server and never exposed in the browser, with tenant-level data isolation.',
            ],
            (object)[
                'id' => 8,
                'question' => 'How can I contact support?',
                'answer' => 'Our technical support team is available via email at support@invoicego.pk, via phone during business hours, or directly through the in-app support ticketing system in your portal.',
            ],
        ]);
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvoiceGO — Smart Digital Invoicing Simplified for Pakistani Businesses</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --ig-primary: #1E50FF;
            --ig-primary-hover: #1541D9;
            --ig-primary-subtle: #EEF4FF;
            --ig-primary-border: #D6E4FF;
            --ig-emerald: #00875A;
            --ig-emerald-hover: #006644;
            --ig-emerald-subtle: #E3FCEF;
            --ig-dark: #0B132B;
            --ig-dark-surface: #0F172A;
            --ig-text-main: #0F172A;
            --ig-text-muted: #64748B;
            --ig-bg-page: #FAFBFF;
            --ig-card-bg: #FFFFFF;
            --ig-card-border: #E8EEF8;
            --ig-radius-card: 18px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--ig-text-main);
            background-color: var(--ig-bg-page);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        .font-display {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Navbar */
        .site-navbar {
            background: #FFFFFF;
            border-bottom: 1px solid #EBF1FA;
            padding: 10px 0;
            position: sticky;
            top: 0;
            z-index: 1040;
            backdrop-filter: blur(10px);
        }

        .site-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 22px;
            color: #0F172A;
            letter-spacing: -0.02em;
        }

        .site-logo-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #1E50FF 0%, #1541D9 100%);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(30, 80, 255, 0.28);
        }

        .site-nav-link {
            font-size: 14.5px;
            font-weight: 500;
            color: #475569;
            padding: 6px 14px;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .site-nav-link:hover {
            color: var(--ig-primary);
        }

        .btn-nav-login {
            color: #1E293B;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.15s ease;
        }

        .btn-nav-login:hover {
            color: var(--ig-primary);
        }

        .btn-primary-blue {
            background-color: var(--ig-primary);
            color: #FFFFFF;
            font-weight: 600;
            font-size: 14px;
            padding: 9px 20px;
            border-radius: 9px;
            border: 1px solid var(--ig-primary);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-primary-blue:hover {
            background-color: var(--ig-primary-hover);
            border-color: var(--ig-primary-hover);
            color: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(30, 80, 255, 0.3);
        }

        .btn-secondary-white {
            background-color: #FFFFFF;
            color: #1E293B;
            font-weight: 600;
            font-size: 14.5px;
            padding: 11px 24px;
            border-radius: 10px;
            border: 1px solid #E2E8F0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-secondary-white:hover {
            background-color: #F8FAFC;
            border-color: #CBD5E1;
            color: #0F172A;
            transform: translateY(-1px);
        }

        /* Hero Section */
        .hero-section {
            padding: 70px 0 60px 0;
            position: relative;
            background: radial-gradient(circle at 100% 0%, rgba(224, 235, 255, 0.6) 0%, rgba(250, 251, 255, 1) 50%);
        }

        .badge-pill-soft {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #EEF4FF;
            color: #1E50FF;
            border: 1px solid #D6E4FF;
            border-radius: 30px;
            font-size: 12.5px;
            font-weight: 600;
            padding: 6px 16px;
            margin-bottom: 24px;
        }

        .badge-pill-soft .dot-blue {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #1E50FF;
        }

        .hero-h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 52px;
            font-weight: 800;
            line-height: 1.15;
            color: #0F172A;
            letter-spacing: -0.03em;
            margin-bottom: 22px;
        }

        .hero-h1 .text-highlight {
            color: var(--ig-primary);
        }

        .hero-desc {
            font-size: 17.5px;
            line-height: 1.65;
            color: #475569;
            margin-bottom: 34px;
            max-width: 540px;
        }

        .trust-checks-row {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-top: 32px;
            color: #334155;
            font-size: 13.5px;
            font-weight: 600;
        }

        .trust-check-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .trust-check-item i {
            color: #10B981;
            font-size: 15px;
        }

        /* Hero Window Mockup */
        .browser-mockup {
            background: #FFFFFF;
            border-radius: 16px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.12), 0 0 1px 1px rgba(15, 23, 42, 0.05);
            overflow: hidden;
            position: relative;
        }

        .browser-mockup-header {
            background: #0B132B;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .window-dots {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .window-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .dot-red { background: #EF4444; }
        .dot-yellow { background: #F59E0B; }
        .dot-green { background: #10B981; }

        .mockup-url-bar {
            color: #94A3B8;
            font-size: 11.5px;
            font-family: monospace;
            background: rgba(255, 255, 255, 0.08);
            padding: 4px 14px;
            border-radius: 6px;
        }

        .badge-fbr-connected {
            background: rgba(16, 185, 129, 0.18);
            color: #34D399;
            border: 1px solid rgba(16, 185, 129, 0.3);
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .mockup-body {
            padding: 24px;
            background: #FAFBFF;
        }

        .mockup-stat-tile {
            background: #FFFFFF;
            border: 1px solid #E8EEF8;
            border-radius: 12px;
            padding: 14px 16px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }

        .mockup-stat-label {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #64748B;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .mockup-stat-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.1;
        }

        .mockup-chart-card {
            background: #FFFFFF;
            border: 1px solid #E8EEF8;
            border-radius: 12px;
            padding: 16px;
            margin-top: 14px;
        }

        .mockup-invoice-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #F1F5F9;
            font-size: 12.5px;
        }

        .mockup-invoice-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .floating-speed-badge {
            position: absolute;
            bottom: -15px;
            left: -15px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08);
            z-index: 10;
        }

        .floating-speed-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #EEF4FF;
            color: #1E50FF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        /* Top 4 Cards */
        .feature-box-white {
            background: #FFFFFF;
            border: 1px solid #E8EEF8;
            border-radius: 16px;
            padding: 28px 24px;
            height: 100%;
            transition: all 0.25s ease;
        }

        .feature-box-white:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.05);
            border-color: #D6E4FF;
        }

        .icon-box-soft-blue {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #EEF4FF;
            color: #1E50FF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 18px;
        }

        .feature-box-title {
            font-size: 16px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 8px;
        }

        .feature-box-desc {
            font-size: 13.5px;
            line-height: 1.55;
            color: #64748B;
            margin-bottom: 0;
        }

        /* Section Headings */
        .section-header-centered {
            text-align: center;
            max-width: 680px;
            margin: 0 auto 50px auto;
        }

        .section-h2 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 38px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.02em;
            margin-bottom: 14px;
        }

        .section-sub {
            font-size: 16px;
            line-height: 1.6;
            color: #64748B;
        }

        /* 3 Steps Workflow */
        .step-card {
            background: #FFFFFF;
            border: 1px solid #E8EEF8;
            border-radius: 18px;
            padding: 34px 28px;
            text-align: center;
            height: 100%;
            position: relative;
            transition: all 0.2s ease;
        }

        .step-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.04);
            border-color: #D6E4FF;
        }

        .step-icon-wrap {
            position: relative;
            display: inline-block;
            margin-bottom: 22px;
        }

        .step-icon-box {
            width: 62px;
            height: 62px;
            border-radius: 16px;
            background: #1E50FF;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            box-shadow: 0 8px 18px rgba(30, 80, 255, 0.28);
        }

        .step-number-badge {
            position: absolute;
            top: -6px;
            right: -8px;
            background: #0F172A;
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 10px;
            border: 2px solid #FFFFFF;
        }

        .step-title {
            font-size: 18px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 12px;
        }

        .step-text {
            font-size: 14px;
            line-height: 1.6;
            color: #64748B;
            margin-bottom: 0;
        }

        /* Interactive SaaS Preview Section */
        .workspace-preview-shell {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 20px;
            box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        .workspace-browser-topbar {
            background: #0B132B;
            padding: 14px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .workspace-layout {
            display: flex;
            min-height: 520px;
        }

        .workspace-sidebar {
            width: 240px;
            background: #FFFFFF;
            border-right: 1px solid #EBF1FA;
            padding: 20px 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .workspace-nav-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            margin-bottom: 4px;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .workspace-nav-btn:hover {
            background: #F1F5F9;
            color: #0F172A;
        }

        .workspace-nav-btn.active {
            background: #1E50FF;
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(30, 80, 255, 0.28);
        }

        .workspace-sidebar-card {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 14px;
        }

        .workspace-main-content {
            flex-grow: 1;
            background: #FAFBFF;
            padding: 28px 32px;
            overflow-y: auto;
        }

        /* Advantage 6-grid */
        .advantage-card {
            background: #FFFFFF;
            border: 1px solid #E8EEF8;
            border-radius: 16px;
            padding: 22px 20px;
            display: flex;
            gap: 16px;
            align-items: flex-start;
            height: 100%;
            transition: all 0.2s ease;
        }

        .advantage-card:hover {
            border-color: #D6E4FF;
            box-shadow: 0 8px 20px rgba(0,0,0,0.03);
        }

        .advantage-check-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #EEF4FF;
            color: #1E50FF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* FAQ Accordion */
        .faq-accordion-item {
            background: #FFFFFF;
            border: 1px solid #E8EEF8;
            border-radius: 14px !important;
            margin-bottom: 12px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        .faq-accordion-item:hover {
            border-color: #CBD5E1;
        }

        .faq-accordion-btn {
            background: #FFFFFF !important;
            font-size: 15.5px;
            font-weight: 700;
            color: #0F172A !important;
            padding: 18px 24px;
            border: none;
            box-shadow: none !important;
        }

        .faq-accordion-body {
            font-size: 14.5px;
            line-height: 1.65;
            color: #475569;
            padding: 0 24px 20px 24px;
        }

        /* Blue CTA Banner */
        .cta-banner-blue {
            background: linear-gradient(135deg, #1855E2 0%, #1E50FF 50%, #2563EB 100%);
            border-radius: 24px;
            padding: 70px 40px;
            text-align: center;
            color: #FFFFFF;
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 50px -10px rgba(30, 80, 255, 0.35);
        }

        .cta-badge-translucent {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #FFFFFF;
            padding: 6px 18px;
            border-radius: 30px;
            font-size: 12.5px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .cta-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 44px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 18px;
            letter-spacing: -0.02em;
        }

        .cta-sub {
            font-size: 17px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
            max-width: 620px;
            margin: 0 auto 34px auto;
        }

        .btn-cta-white {
            background: #FFFFFF;
            color: #1E50FF;
            font-weight: 700;
            font-size: 15px;
            padding: 13px 30px;
            border-radius: 10px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-cta-white:hover {
            background: #F8FAFC;
            color: #1541D9;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-cta-translucent {
            background: rgba(255, 255, 255, 0.12);
            color: #FFFFFF;
            font-weight: 600;
            font-size: 15px;
            padding: 13px 26px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-cta-translucent:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }

        /* Footer */
        .site-footer {
            background: #0B132B;
            color: #94A3B8;
            padding: 70px 0 35px 0;
            font-size: 13.5px;
        }

        .footer-heading {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #FFFFFF;
            margin-bottom: 20px;
        }

        .footer-link {
            color: #94A3B8;
            text-decoration: none;
            display: block;
            margin-bottom: 11px;
            transition: color 0.15s ease;
        }

        .footer-link:hover {
            color: #FFFFFF;
        }

        @media (max-width: 991px) {
            .hero-h1 { font-size: 38px; }
            .cta-heading { font-size: 32px; }
            .workspace-layout { flex-direction: column; }
            .workspace-sidebar { width: 100%; border-right: none; border-bottom: 1px solid #EBF1FA; }
        }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <header class="site-navbar">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="{{ url('/') }}" class="site-logo">
                <img src="{{ \App\Services\BrandLogoService::lightUrl() }}" alt="InvoiceGO" style="height: 44px; width: auto; max-width: 210px; object-fit: contain; display: block;">
            </a>

            <nav class="d-none d-lg-flex align-items-center gap-1">
                <a href="{{ url('/') }}" class="site-nav-link text-primary fw-semibold">Home</a>
                <a href="#features" class="site-nav-link">Features</a>
                <a href="#how-it-works" class="site-nav-link">How It Works</a>
                <a href="#industries" class="site-nav-link">Industries</a>
                @if ($showPricingPlans)
                    <a href="#pricing" class="site-nav-link">Pricing</a>
                @endif
                @if ($showFaqSection)
                    <a href="#faq" class="site-nav-link">FAQ</a>
                @endif
            </nav>

            <div class="d-flex align-items-center gap-2">
                @auth
                    <a href="{{ route('tenant.dashboard') }}" class="btn-primary-blue">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-nav-login">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Login</span>
                    </a>
                    <a href="{{ $registrationEnabled ? route('register') : route('login') }}" class="btn-primary-blue">
                        <span>Get Started</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section (Screenshot 6: bcff23b5) -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <!-- Hero Left Column -->
                <div class="col-lg-6">
                    <div class="badge-pill-soft">
                        <span class="dot-blue"></span>
                        <span>Built for Pakistani Businesses</span>
                    </div>

                    <h1 class="hero-h1">
                        Smart Digital Invoicing.<br>
                        <span class="text-highlight">Simplified</span> for Your Business.
                    </h1>

                    <p class="hero-desc">
                        Manage your sales invoices digitally with InvoiceGO. Create professional invoices, streamline your invoicing workflow, and connect with FBR through an integrated digital invoicing solution.
                    </p>

                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <a href="{{ $registrationEnabled ? route('register') : route('login') }}" class="btn-primary-blue py-3 px-4 fs-6">
                            <span>Get Started</span>
                            <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                        <a href="#features" class="btn-secondary-white py-3 px-4 fs-6">
                            <span>Explore Features</span>
                        </a>
                    </div>

                    <div class="trust-checks-row">
                        <div class="trust-check-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>FBR Ready</span>
                        </div>
                        <div class="trust-check-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Secure Cloud</span>
                        </div>
                        <div class="trust-check-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Instant Setup</span>
                        </div>
                        @if (!$showPricingPlans && $defaultInvoiceLimit > 0)
                            <div class="trust-check-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Free Access &middot; Up to {{ number_format($defaultInvoiceLimit) }} Invoices</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Hero Right Column: macOS Dashboard Mockup -->
                <div class="col-lg-6">
                    <div class="position-relative">
                        <div class="browser-mockup">
                            <div class="browser-mockup-header">
                                <div class="window-dots">
                                    <span class="window-dot dot-red"></span>
                                    <span class="window-dot dot-yellow"></span>
                                    <span class="window-dot dot-green"></span>
                                </div>
                                <div class="mockup-url-bar">
                                    app.invoicego.pk/dashboard
                                </div>
                                <div>
                                    <span class="badge-fbr-connected">
                                        <i class="bi bi-record-fill" style="font-size: 8px;"></i>
                                        FBR Connected
                                    </span>
                                </div>
                            </div>

                            <div class="mockup-body">
                                <!-- 3 Stat Tiles -->
                                <div class="row g-2 mb-3">
                                    <div class="col-4">
                                        <div class="mockup-stat-tile">
                                            <div class="mockup-stat-label">TOTAL SALES</div>
                                            <div class="mockup-stat-value">PKR 4.85M</div>
                                            <div class="text-success small fw-semibold mt-1" style="font-size: 11px;">
                                                <i class="bi bi-arrow-up-short"></i> +18.4%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mockup-stat-tile">
                                            <div class="mockup-stat-label">TOTAL INVOICES</div>
                                            <div class="mockup-stat-value">1,248</div>
                                            <div class="text-muted small mt-1" style="font-size: 11px;">This Month</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mockup-stat-tile">
                                            <div class="mockup-stat-label">FBR STATUS</div>
                                            <div class="mockup-stat-value text-success">Synced</div>
                                            <div class="text-muted small mt-1" style="font-size: 11px;">Real-time API</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mini Chart Mockup -->
                                <div class="mockup-chart-card mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="fw-bold text-dark small" style="font-size: 12.5px;">Monthly Sales Volume</span>
                                        <span class="text-muted" style="font-size: 11px;">FY 2026</span>
                                    </div>
                                    <!-- Beautiful SVG Chart Wave -->
                                    <svg viewBox="0 0 400 90" class="w-100" style="height: 75px; overflow: visible;">
                                        <defs>
                                            <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#1E50FF" stop-opacity="0.25"/>
                                                <stop offset="100%" stop-color="#1E50FF" stop-opacity="0.0"/>
                                            </linearGradient>
                                        </defs>
                                        <path d="M 0,75 C 60,65 100,25 160,35 C 220,45 280,10 340,20 C 370,25 390,15 400,12 L 400,85 L 0,85 Z" fill="url(#chartGrad)"/>
                                        <path d="M 0,75 C 60,65 100,25 160,35 C 220,45 280,10 340,20 C 370,25 390,15 400,12" fill="none" stroke="#1E50FF" stroke-width="2.5" stroke-linecap="round"/>
                                        <circle cx="160" cy="35" r="3.5" fill="#1E50FF"/>
                                        <circle cx="340" cy="20" r="3.5" fill="#1E50FF"/>
                                    </svg>
                                    <div class="d-flex justify-content-between text-muted" style="font-size: 10px; margin-top: 4px;">
                                        <span>Jan</span>
                                        <span>Jun</span>
                                        <span>Dec</span>
                                    </div>
                                </div>

                                <!-- Recent Invoices List -->
                                <div class="bg-white rounded-3 border p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                        <span class="text-uppercase fw-bold text-muted" style="font-size: 10.5px; letter-spacing: 0.05em;">RECENT DIGITAL INVOICES</span>
                                        <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-semibold" style="font-size: 11.5px;">View All</a>
                                    </div>
                                    <div class="mockup-invoice-row">
                                        <div>
                                            <div class="fw-semibold text-dark">INV-2026-089</div>
                                            <div class="text-muted" style="font-size: 11px;">Al-Madina Traders</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-dark">PKR 145,000</div>
                                            <span class="text-success fw-medium" style="font-size: 11px;"><i class="bi bi-check2"></i> Submitted to FBR</span>
                                        </div>
                                    </div>
                                    <div class="mockup-invoice-row">
                                        <div>
                                            <div class="fw-semibold text-dark">INV-2026-088</div>
                                            <div class="text-muted" style="font-size: 11px;">Zenith Logistics</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-dark">PKR 88,500</div>
                                            <span class="text-success fw-medium" style="font-size: 11px;"><i class="bi bi-check2"></i> Submitted to FBR</span>
                                        </div>
                                    </div>
                                    <div class="mockup-invoice-row">
                                        <div>
                                            <div class="fw-semibold text-dark">INV-2026-087</div>
                                            <div class="text-muted" style="font-size: 11px;">Metro Super Market</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-dark">PKR 320,000</div>
                                            <span class="text-success fw-medium" style="font-size: 11px;"><i class="bi bi-check2"></i> Submitted to FBR</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Speed Badge -->
                        <div class="floating-speed-badge">
                            <div class="floating-speed-icon">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </div>
                            <div>
                                <div class="text-muted fw-medium" style="font-size: 11.5px;">Invoice Speed</div>
                                <div class="fw-bold text-dark" style="font-size: 14.5px;">Under 30 Seconds</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Top 4 Feature Cards (Screenshot 5: 9b3551fe) -->
    <section class="py-5" style="margin-top: 30px;">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-box-white">
                        <div class="icon-box-soft-blue">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h4 class="feature-box-title">Digital Invoice Management</h4>
                        <p class="feature-box-desc">Create, organize, and manage professional sales invoices seamlessly in one centralized hub.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-box-white">
                        <div class="icon-box-soft-blue">
                            <i class="bi bi-cpu"></i>
                        </div>
                        <h4 class="feature-box-title">FBR API Integration</h4>
                        <p class="feature-box-desc">Connect your sales workflows directly with FBR digital invoicing standards and requirements.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-box-white">
                        <div class="icon-box-soft-blue">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <h4 class="feature-box-title">Real-Time Processing</h4>
                        <p class="feature-box-desc">Process and submit sales invoices instantly with real-time status updates and confirmation logs.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-box-white">
                        <div class="icon-box-soft-blue">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <h4 class="feature-box-title">Secure Business Records</h4>
                        <p class="feature-box-desc">Keep all your financial and customer records protected with bank-grade cloud security.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Powerful Platform Features (6 Grid) (Screenshots 5 & 8: affe3bf8) -->
    <section class="py-5" id="features">
        <div class="container">
            <div class="section-header-centered">
                <div class="badge-pill-soft mb-3">
                    <span>Powerful Platform Features</span>
                </div>
                <h2 class="section-h2">Everything You Need for Smarter Invoicing</h2>
                <p class="section-sub">
                    Designed specifically for Pakistani enterprises, distributors, and retailers to streamline digital compliance and daily sales workflows.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-box-white">
                        <div class="icon-box-soft-blue">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <h4 class="feature-box-title">Digital Invoice Creation</h4>
                        <p class="feature-box-desc mb-3">
                            Create professional digital sales invoices with an easy-to-use interface, automated tax calculations, and customized itemized lists.
                        </p>
                        <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none small">
                            Learn more <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-box-white">
                        <div class="icon-box-soft-blue">
                            <i class="bi bi-cpu"></i>
                        </div>
                        <h4 class="feature-box-title">FBR Integration</h4>
                        <p class="feature-box-desc mb-3">
                            Connect your invoicing workflow with FBR through the supported Digital Invoicing API for seamless transmission and verification.
                        </p>
                        <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none small">
                            Learn more <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-box-white">
                        <div class="icon-box-soft-blue">
                            <i class="bi bi-card-checklist"></i>
                        </div>
                        <h4 class="feature-box-title">Invoice Management</h4>
                        <p class="feature-box-desc mb-3">
                            Create, view, search, filter, and manage all your sales invoices from one centralized, lightning-fast dashboard.
                        </p>
                        <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none small">
                            Learn more <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-box-white">
                        <div class="icon-box-soft-blue">
                            <i class="bi bi-building"></i>
                        </div>
                        <h4 class="feature-box-title">Business Management</h4>
                        <p class="feature-box-desc mb-3">
                            Manage business information, registration details, NTN/STRN profiles, and multi-branch company locations effortlessly.
                        </p>
                        <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none small">
                            Learn more <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-box-white">
                        <div class="icon-box-soft-blue">
                            <i class="bi bi-bar-chart-line"></i>
                        </div>
                        <h4 class="feature-box-title">Sales Reports & Analytics</h4>
                        <p class="feature-box-desc mb-3">
                            Track sales performance, invoice history, tax summaries, and business activity through clear visual reports and exports.
                        </p>
                        <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none small">
                            Learn more <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-box-white">
                        <div class="icon-box-soft-blue">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="feature-box-title">Secure Data Management</h4>
                        <p class="feature-box-desc mb-3">
                            Keep your business and invoice records organized with appropriate access controls and rigorous data security practices.
                        </p>
                        <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none small">
                            Learn more <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Simple 3-Step Workflow (Screenshot 7: 3fa5f2d1) -->
    <section class="py-5 bg-white border-top border-bottom" id="how-it-works">
        <div class="container py-3">
            <div class="section-header-centered">
                <div class="badge-pill-soft mb-3">
                    <span>Simple 3-Step Workflow</span>
                </div>
                <h2 class="section-h2">Get Started in Three Simple Steps</h2>
                <p class="section-sub">
                    From business registration to FBR-compliant digital sales invoicing in record time.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-icon-wrap">
                            <div class="step-icon-box">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <span class="step-number-badge">01</span>
                        </div>
                        <h3 class="step-title">Create Your Account</h3>
                        <p class="step-text">
                            Register your business, enter your company profile, NTN/STRN details, and set up your secure InvoiceGO account in minutes.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-icon-wrap">
                            <div class="step-icon-box">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <span class="step-number-badge">02</span>
                        </div>
                        <h3 class="step-title">Create Your Invoice</h3>
                        <p class="step-text">
                            Enter customer details, add products or services, quantities, prices, and applicable sales taxes with automated calculations.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-icon-wrap">
                            <div class="step-icon-box">
                                <i class="bi bi-send"></i>
                            </div>
                            <span class="step-number-badge">03</span>
                        </div>
                        <h3 class="step-title">Process Through FBR Integration</h3>
                        <p class="step-text">
                            Submit invoices through the configured FBR API and view the response, verification status, and digital invoice logs instantly.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive SaaS Preview (Screenshot 1: 0c1414b8) -->
    <section class="py-5" style="background: #FAFBFF;">
        <div class="container py-4">
            <div class="section-header-centered">
                <div class="badge-pill-soft mb-3">
                    <span>Interactive SaaS Preview</span>
                </div>
                <h2 class="section-h2">Your Entire Invoicing Business in One Place</h2>
                <p class="section-sub">
                    Experience the clean, intuitive InvoiceGO workspace. Try clicking through the tabs and testing the interactive invoice generator below.
                </p>
            </div>

            <div class="workspace-preview-shell">
                <!-- Browser Bar -->
                <div class="workspace-browser-topbar">
                    <div class="window-dots">
                        <span class="window-dot dot-red"></span>
                        <span class="window-dot dot-yellow"></span>
                        <span class="window-dot dot-green"></span>
                    </div>
                    <div class="mockup-url-bar text-truncate" style="max-width: 320px;">
                        https://app.invoicego.pk/workspace
                    </div>
                    <div class="d-flex align-items-center gap-2 text-white-50" style="font-size: 12px;">
                        <span>Logged in as: <strong class="text-white">Enterprise Admin</strong></span>
                        <span class="badge bg-primary text-white rounded-circle p-1" style="width: 22px; height: 22px;">EA</span>
                    </div>
                </div>

                <!-- Workspace Body Split -->
                <div class="workspace-layout">
                    <!-- Left Sidebar -->
                    <div class="workspace-sidebar">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-4 px-2">
                                <div class="bg-primary text-white rounded-3 p-1 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #1E50FF !important;">
                                    <i class="bi bi-file-earmark-arrow-up-fill fs-6"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark lh-1" style="font-size: 13.5px;">InvoiceGO Portal</div>
                                    <div class="text-muted" style="font-size: 10.5px;">FBR Digital Workspace</div>
                                </div>
                            </div>

                            <button class="workspace-nav-btn active" onclick="switchPreviewTab('dashboard', this)">
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </button>
                            <button class="workspace-nav-btn" onclick="switchPreviewTab('create-invoice', this)">
                                <i class="bi bi-file-earmark-plus"></i>
                                <span>Create Invoice</span>
                            </button>
                            <button class="workspace-nav-btn" onclick="switchPreviewTab('all-invoices', this)">
                                <i class="bi bi-journal-text"></i>
                                <span>All Invoices</span>
                            </button>
                            <button class="workspace-nav-btn" onclick="switchPreviewTab('customers', this)">
                                <i class="bi bi-people"></i>
                                <span>Customers</span>
                            </button>
                            <button class="workspace-nav-btn" onclick="switchPreviewTab('products', this)">
                                <i class="bi bi-box-seam"></i>
                                <span>Products</span>
                            </button>
                            <button class="workspace-nav-btn" onclick="switchPreviewTab('reports', this)">
                                <i class="bi bi-bar-chart"></i>
                                <span>Reports</span>
                            </button>
                        </div>

                        <div class="workspace-sidebar-card">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-circle p-1" style="width: 10px; height: 10px;"></span>
                                <span class="fw-bold text-dark" style="font-size: 12px;">FBR API Live</span>
                            </div>
                            <div class="text-muted" style="font-size: 11px; line-height: 1.4;">
                                Connected to FBR sandbox &amp; live endpoints.
                            </div>
                        </div>
                    </div>

                    <!-- Main Dynamic Pane -->
                    <div class="workspace-main-content">
                        <!-- TAB 1: DASHBOARD -->
                        <div id="tab-dashboard" class="preview-tab-pane">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
                                <div>
                                    <h4 class="fw-bold text-dark mb-1 font-display fs-5">Dashboard Overview</h4>
                                    <p class="text-muted small mb-0">Real-time summary of sales activity and FBR digital invoicing.</p>
                                </div>
                                <button class="btn btn-primary-blue btn-sm py-2 px-3" onclick="switchPreviewTab('create-invoice', document.querySelectorAll('.workspace-nav-btn')[1])">
                                    <i class="bi bi-plus-lg"></i>
                                    <span>New Invoice</span>
                                </button>
                            </div>

                            <!-- 3 Big Metric Cards -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="bg-white border rounded-3 p-3">
                                        <div class="text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.05em;">TOTAL REVENUE</div>
                                        <div class="fs-4 fw-bold text-dark mt-1">PKR 14,850,000</div>
                                        <div class="text-success small fw-semibold mt-1">
                                            <i class="bi bi-graph-up-arrow"></i> +24% vs last month
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-white border rounded-3 p-3">
                                        <div class="text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.05em;">ISSUED INVOICES</div>
                                        <div class="fs-4 fw-bold text-dark mt-1">3,420</div>
                                        <div class="text-primary small fw-semibold mt-1">
                                            <i class="bi bi-shield-check"></i> 100% FBR Compliant
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-white border rounded-3 p-3">
                                        <div class="text-uppercase text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.05em;">PENDING TAX FILING</div>
                                        <div class="fs-4 fw-bold text-success mt-1">PKR 0</div>
                                        <div class="text-muted small mt-1">Fully reconciled</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Transactions Card -->
                            <div class="bg-white border rounded-3 p-3 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <span class="text-uppercase fw-bold text-muted" style="font-size: 11.5px; letter-spacing: 0.05em;">RECENT TRANSACTIONS</span>
                                    <span class="text-primary small fw-semibold" style="cursor: pointer;">View All</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold text-dark">INV-2026-092</td>
                                                <td>Al-Fatah Stores</td>
                                                <td class="text-muted">Nov 18, 2026</td>
                                                <td class="fw-bold text-dark text-end">PKR 450,000</td>
                                                <td class="text-end">
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Submitted to FBR
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-dark">INV-2026-091</td>
                                                <td>Hyperstar Lahore</td>
                                                <td class="text-muted">Nov 17, 2026</td>
                                                <td class="fw-bold text-dark text-end">PKR 1,250,000</td>
                                                <td class="text-end">
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Submitted to FBR
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold text-dark">INV-2026-090</td>
                                                <td>Imtiaz Super Market</td>
                                                <td class="text-muted">Nov 16, 2026</td>
                                                <td class="fw-bold text-dark text-end">PKR 310,500</td>
                                                <td class="text-end">
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Submitted to FBR
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: CREATE INVOICE (Interactive Generator Preview) -->
                        <div id="tab-create-invoice" class="preview-tab-pane d-none">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h5 class="fw-bold text-dark mb-0 font-display">Create FBR Sales Invoice</h5>
                                    <span class="text-muted small">Automated tax math &amp; FBR DI API v1.12 compliance</span>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">Sandbox Live</span>
                            </div>
                            <div class="bg-white border rounded-3 p-3">
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Buyer NTN / CNIC</label>
                                        <input type="text" class="form-control form-control-sm" value="3330318202735" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Buyer Name &amp; Province</label>
                                        <input type="text" class="form-control form-control-sm" value="Al-Madina Traders (Punjab)" readonly>
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Item / HS Code</label>
                                        <input type="text" class="form-control form-control-sm" value="Oxen (0101.2100)" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold text-muted">Qty</label>
                                        <input type="number" id="preview-qty" class="form-control form-control-sm" value="10" oninput="calcPreviewInvoice()">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted">Unit Price (PKR)</label>
                                        <input type="number" id="preview-price" class="form-control form-control-sm" value="35000" oninput="calcPreviewInvoice()">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted">GST Rate</label>
                                        <input type="text" class="form-control form-control-sm" value="18% Standard" readonly>
                                    </div>
                                </div>
                                <div class="bg-light rounded-2 p-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="small text-muted">Calculated Grand Total:</div>
                                        <div class="fs-5 fw-bold text-primary" id="preview-total">PKR 413,000.00</div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">Save Draft</a>
                                        <a href="{{ route('login') }}" class="btn btn-primary-blue btn-sm">Submit to FBR →</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: ALL INVOICES -->
                        <div id="tab-all-invoices" class="preview-tab-pane d-none">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="fw-bold text-dark mb-0 font-display">Invoice Repository</h5>
                                <span class="text-muted small">Total: 3,420 invoices</span>
                            </div>
                            <div class="bg-white border rounded-3 p-3">
                                <div class="d-flex gap-2 mb-3">
                                    <input type="text" class="form-control form-control-sm" placeholder="Search by Invoice #, Buyer or NTN...">
                                    <button class="btn btn-sm btn-light border">Filter</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0" style="font-size: 12.5px;">
                                        <thead>
                                            <tr class="text-muted">
                                                <th>INVOICE #</th>
                                                <th>BUYER</th>
                                                <th>DATE</th>
                                                <th>TOTAL</th>
                                                <th>FBR STATUS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold">INV-2026-092</td>
                                                <td>Al-Fatah Stores</td>
                                                <td>18-Nov-2026</td>
                                                <td>PKR 450,000</td>
                                                <td><span class="badge bg-success-subtle text-success">Verified</span></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">INV-2026-091</td>
                                                <td>Hyperstar Lahore</td>
                                                <td>17-Nov-2026</td>
                                                <td>PKR 1,250,000</td>
                                                <td><span class="badge bg-success-subtle text-success">Verified</span></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">INV-2026-089</td>
                                                <td>Zenith Logistics</td>
                                                <td>15-Nov-2026</td>
                                                <td>PKR 88,500</td>
                                                <td><span class="badge bg-success-subtle text-success">Verified</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: CUSTOMERS -->
                        <div id="tab-customers" class="preview-tab-pane d-none">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="fw-bold text-dark mb-0 font-display">Customer Master Data</h5>
                                <button class="btn btn-sm btn-primary-blue">+ Add Customer</button>
                            </div>
                            <div class="bg-white border rounded-3 p-3">
                                <p class="text-muted small mb-2">Automated Active Taxpayer List (ATL) status checks built in.</p>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0" style="font-size: 12.5px;">
                                        <thead>
                                            <tr class="text-muted">
                                                <th>NAME</th>
                                                <th>NTN / CNIC</th>
                                                <th>PROVINCE</th>
                                                <th>ATL STATUS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="fw-semibold">Al-Fatah Stores</td>
                                                <td>41201948271</td>
                                                <td>Punjab</td>
                                                <td><span class="badge bg-success-subtle text-success">Active Taxpayer</span></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">Unregistered Retail Buyer</td>
                                                <td>3330318202735</td>
                                                <td>Sindh</td>
                                                <td><span class="badge bg-warning-subtle text-warning">4% Further Tax</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 5: PRODUCTS -->
                        <div id="tab-products" class="preview-tab-pane d-none">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="fw-bold text-dark mb-0 font-display">Product &amp; HS Codes Catalog</h5>
                                <button class="btn btn-sm btn-primary-blue">+ New Product</button>
                            </div>
                            <div class="bg-white border rounded-3 p-3">
                                <p class="text-muted small mb-2">Over 6,000 official Pakistan Customs Tariff HS codes pre-seeded.</p>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0" style="font-size: 12.5px;">
                                        <thead>
                                            <tr class="text-muted">
                                                <th>PRODUCT</th>
                                                <th>HS / PCT CODE</th>
                                                <th>UOM</th>
                                                <th>DEFAULT RATE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="fw-semibold">Pure Cotton Fabric</td>
                                                <td>5208.1100</td>
                                                <td>Meters</td>
                                                <td>18% Standard</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">Packaged Fruit Juice (MSRP)</td>
                                                <td>2009.1100</td>
                                                <td>Numbers</td>
                                                <td>3rd Schedule (Retail Price)</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 6: REPORTS -->
                        <div id="tab-reports" class="preview-tab-pane d-none">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="fw-bold text-dark mb-0 font-display">Sales Tax Return &amp; FBR Analytics</h5>
                                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-download me-1"></i> Export CSV</button>
                            </div>
                            <div class="bg-white border rounded-3 p-3">
                                <div class="row g-2 text-center">
                                    <div class="col-4 border-end">
                                        <div class="small text-muted">OUTPUT TAX (18%)</div>
                                        <div class="fw-bold fs-5 text-dark mt-1">PKR 2,673,000</div>
                                    </div>
                                    <div class="col-4 border-end">
                                        <div class="small text-muted">FURTHER TAX (4%)</div>
                                        <div class="fw-bold fs-5 text-dark mt-1">PKR 184,200</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small text-muted">FBR ACCEPTANCE RATE</div>
                                        <div class="fw-bold fs-5 text-success mt-1">100.0%</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status bar in preview -->
                        <div class="d-flex align-items-center justify-content-between text-muted small pt-3 border-top mt-3" style="font-size: 11.5px;">
                            <span>InvoiceGO v2.6 Enterprise Edition</span>
                            <span><i class="bi bi-lock-fill text-success me-1"></i> Secure SSL Encryption</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Businesses Choose InvoiceGO (Screenshot 9: 70a8136e) -->
    <section class="py-5 bg-white border-top border-bottom">
        <div class="container py-4">
            <div class="row g-5 align-items-center">
                <!-- Left Column -->
                <div class="col-lg-5">
                    <div class="badge-pill-soft mb-3">
                        <span>The InvoiceGO Advantage</span>
                    </div>
                    <h2 class="section-h2">Why Businesses Choose InvoiceGO</h2>
                    <p class="section-sub mb-4">
                        We combine modern SaaS simplicity with rigorous compliance standards to give Pakistani businesses the ultimate digital invoicing experience.
                    </p>

                    <div class="bg-light border rounded-3 p-4">
                        <div class="fw-bold text-dark fs-5 mb-1 font-display">99.9% Uptime &amp; Reliability</div>
                        <p class="text-muted small mb-0">
                            Cloud infrastructure designed for uninterrupted business operations.
                        </p>
                    </div>
                </div>

                <!-- Right Column: 6 Benefits Grid -->
                <div class="col-lg-7">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="advantage-card">
                                <div class="advantage-check-circle">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Simple and User-Friendly Interface</div>
                                    <div class="text-muted" style="font-size: 12.5px; line-height: 1.5;">Designed with an intuitive user experience so your team can create invoices instantly without extensive training.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="advantage-card">
                                <div class="advantage-check-circle">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Faster Invoice Creation</div>
                                    <div class="text-muted" style="font-size: 12.5px; line-height: 1.5;">Pre-loaded product catalogs, saved customer profiles, and automated tax math cut invoicing time by over 70%.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="advantage-card">
                                <div class="advantage-check-circle">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Centralized Invoice Management</div>
                                    <div class="text-muted" style="font-size: 12.5px; line-height: 1.5;">Access all past and present invoices, customer histories, and filing statuses in one secure cloud dashboard.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="advantage-card">
                                <div class="advantage-check-circle">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Business Performance Insights</div>
                                    <div class="text-muted" style="font-size: 12.5px; line-height: 1.5;">Visual charts and revenue analytics help you track sales trends and financial growth at a glance.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="advantage-card">
                                <div class="advantage-check-circle">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-1" style="font-size: 14.5px;">FBR API Connectivity</div>
                                    <div class="text-muted" style="font-size: 12.5px; line-height: 1.5;">Built with robust standards to connect smoothly with FBR digital invoicing protocols and standards.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="advantage-card">
                                <div class="advantage-check-circle">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-1" style="font-size: 14.5px;">Accessible Web-Based Platform</div>
                                    <div class="text-muted" style="font-size: 12.5px; line-height: 1.5;">Access InvoiceGO securely from any device, anywhere, without installing cumbersome desktop software.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Industries Section -->
    <section class="py-5" id="industries" style="background: #FAFBFF;">
        <div class="container py-4">
            <div class="section-header-centered">
                <div class="badge-pill-soft mb-3">
                    <span>Tailored For Your Sector</span>
                </div>
                <h2 class="section-h2">Built for Every Pakistani Industry</h2>
                <p class="section-sub">
                    From Fast Moving Consumer Goods (FMCG) to heavy engineering and textile exports.
                </p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-md-3 col-6">
                    <div class="bg-white border rounded-4 p-4 h-100">
                        <div class="fs-1 text-primary mb-2"><i class="bi bi-shop"></i></div>
                        <h5 class="fw-bold text-dark fs-6">Retailers &amp; Supermarkets</h5>
                        <p class="text-muted small mb-0">Tier-1 POS integration &amp; Third Schedule retail price compliance.</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="bg-white border rounded-4 p-4 h-100">
                        <div class="fs-1 text-primary mb-2"><i class="bi bi-truck"></i></div>
                        <h5 class="fw-bold text-dark fs-6">Wholesalers &amp; Distributors</h5>
                        <p class="text-muted small mb-0">High-volume batch invoicing &amp; 4% further tax automation.</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="bg-white border rounded-4 p-4 h-100">
                        <div class="fs-1 text-primary mb-2"><i class="bi bi-gear-wide-connected"></i></div>
                        <h5 class="fw-bold text-dark fs-6">Manufacturers</h5>
                        <p class="text-muted small mb-0">Itemized bills of materials, raw materials &amp; sales tax deductions.</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="bg-white border rounded-4 p-4 h-100">
                        <div class="fs-1 text-primary mb-2"><i class="bi bi-globe2"></i></div>
                        <h5 class="fw-bold text-dark fs-6">Importers &amp; Exporters</h5>
                        <p class="text-muted small mb-0">Fifth Schedule zero-rated export handling &amp; customs tariff codes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($showPricingPlans && $pricingPlans->isNotEmpty())
    <!-- Pricing Section -->
    <section class="py-5 bg-white border-top border-bottom" id="pricing">
        <div class="container py-4">
            <div class="section-header-centered">
                <div class="badge-pill-soft mb-3">
                    <span>Transparent Pricing</span>
                </div>
                <h2 class="section-h2">Simple, Predictable Plans</h2>
                <p class="section-sub">
                    Get started with our free trial or pick a plan tailored to your monthly invoice volume.
                </p>
            </div>
            <div class="row g-4 align-items-stretch justify-content-center">
                @foreach ($pricingPlans as $plan)
                    <div class="col-lg-4 col-md-6">
                        <div class="border {{ $plan->is_popular ? 'border-primary border-2 shadow-sm' : '' }} rounded-4 p-4 bg-white h-100 position-relative d-flex flex-column justify-content-between">
                            @if ($plan->is_popular)
                                <span class="badge bg-primary text-white position-absolute top-0 end-0 m-3 px-3 py-1 rounded-pill" style="background: #1E50FF !important;">Most Popular</span>
                            @endif
                            <div>
                                <h4 class="fw-bold text-dark fs-5">{{ $plan->name }}</h4>
                                <p class="text-muted small mb-3">{{ $plan->tagline }}</p>
                                <div class="my-3">
                                    <span class="fs-2 fw-bold {{ $plan->is_popular ? 'text-primary' : 'text-dark' }}">{{ $plan->price }}</span>
                                    <span class="text-muted small">{{ $plan->billing_period }}</span>
                                </div>
                                <ul class="list-unstyled small text-muted mb-4">
                                    @foreach ($plan->features ?? [] as $feat)
                                        <li class="mb-2 d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill text-success me-2 fs-6"></i>
                                            <span>{{ $feat }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div>
                                @if ($plan->button_action === 'contact')
                                    <button class="btn btn-outline-dark w-100 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#contactModal">
                                        {{ $plan->button_text }}
                                    </button>
                                @else
                                    <a href="{{ $registrationEnabled ? route('register') : route('login') }}" class="btn {{ $plan->is_popular ? 'btn-primary-blue' : 'btn-outline-primary' }} w-100 py-2 fw-semibold">
                                        {{ $plan->button_text }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Frequently Asked Questions (Screenshots 3 & 10: 2a1158b0, 67b89ff9) -->
    @if ($showFaqSection && $faqs->isNotEmpty())
    <section class="py-5" id="faq" style="background: #FAFBFF;">
        <div class="container py-4">
            <div class="section-header-centered">
                <div class="badge-pill-soft mb-3">
                    <span>Got Questions?</span>
                </div>
                <h2 class="section-h2">Frequently Asked Questions</h2>
                <p class="section-sub">
                    Find answers to common questions about InvoiceGO and digital sales invoicing.
                </p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        @foreach ($faqs as $index => $faq)
                            <div class="faq-accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button faq-accordion-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $faq->id ?? $index }}">
                                        {{ $faq->question }}
                                    </button>
                                </h2>
                                <div id="faq-{{ $faq->id ?? $index }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="faq-accordion-body">
                                        {!! nl2br(e($faq->answer)) !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Big Blue CTA Banner (Screenshot 2: ce101b91) -->
    <section class="py-5">
        <div class="container">
            <div class="cta-banner-blue">
                <div class="cta-badge-translucent">
                    <i class="bi bi-shield-check"></i>
                    <span>FBR-Compliant Digital Sales Invoicing</span>
                </div>

                <h2 class="cta-heading">Ready to Simplify Your Digital Invoicing?</h2>
                <p class="cta-sub">
                    Take control of your invoicing process with InvoiceGO. Start managing your business invoices digitally today.
                </p>

                <div class="d-flex flex-wrap align-items-center justify-content-center gap-3">
                    <a href="{{ $registrationEnabled ? route('register') : route('login') }}" class="btn-cta-white">
                        <span>Get Started – Free</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <button class="btn-cta-translucent" data-bs-toggle="modal" data-bs-target="#contactModal">
                        <i class="bi bi-chat-dots"></i>
                        <span>Contact Us</span>
                    </button>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-center gap-4 mt-4 text-white-50 small" style="font-size: 13px;">
                    <div><i class="bi bi-check2 text-white me-1"></i> Instant Account Setup</div>
                    @if (!$showPricingPlans && $defaultInvoiceLimit > 0)
                        <div><i class="bi bi-check2 text-white me-1"></i> Free Starter Quota (Up to {{ number_format($defaultInvoiceLimit) }} Invoices)</div>
                    @else
                        <div><i class="bi bi-check2 text-white me-1"></i> No Credit Card Required</div>
                    @endif
                    <div><i class="bi bi-check2 text-white me-1"></i> Secure Cloud Platform</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer (Screenshot 2: ce101b91) -->
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4 mb-5">
                <!-- Col 1: Brand Info -->
                <div class="col-lg-4 pe-lg-5">
                    <a href="{{ url('/') }}" class="d-inline-block mb-3">
                        <img src="{{ \App\Services\BrandLogoService::darkUrl() }}" alt="InvoiceGO" style="height: 48px; width: auto; max-width: 230px; object-fit: contain; display: block;">
                    </a>
                    <p class="text-white-50 small mb-2" style="line-height: 1.65;">
                        InvoiceGO is Pakistan's premier digital sales invoicing and FBR integration platform, empowering businesses with streamlined financial workflows and secure cloud compliance.
                    </p>
                    <div class="text-white-50 small mt-2" style="font-size: 12px; letter-spacing: 0.02em;">
                        <i class="bi bi-shield-check text-primary me-1"></i> FBR Compliant Digital Invoicing Platform
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="col-6 col-lg-2 offset-lg-1">
                    <div class="footer-heading">QUICK LINKS</div>
                    <a href="{{ url('/') }}" class="footer-link">Home</a>
                    <a href="#features" class="footer-link">Features</a>
                    <a href="#how-it-works" class="footer-link">How It Works</a>
                    <a href="#industries" class="footer-link">Industries</a>
                    @if ($showPricingPlans)
                        <a href="#pricing" class="footer-link">Pricing</a>
                    @endif
                </div>

                <!-- Col 3: Support -->
                <div class="col-6 col-lg-2">
                    <div class="footer-heading">SUPPORT</div>
                    @if ($showFaqSection)
                        <a href="#faq" class="footer-link">Help Center</a>
                        <a href="#faq" class="footer-link">FAQs</a>
                    @endif
                    <a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#contactModal">Contact Us</a>
                    <a href="mailto:support@invoicego.pk" class="footer-link text-white-50">support@invoicego.pk</a>
                </div>

                <!-- Col 4: Legal -->
                <div class="col-6 col-lg-3">
                    <div class="footer-heading">LEGAL &amp; COMPLIANCE</div>
                    <a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                    <a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#termsModal">Terms &amp; Conditions</a>
                    <a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#securityModal">Security Compliance</a>
                    <div class="text-white-50" style="font-size: 11px; margin-top: 15px;">
                        Times shown in Pakistan Standard Time (Asia/Karachi).
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between pt-4 border-top border-secondary border-opacity-25 text-white-50 small">
                <div>&copy; {{ date('Y') }} InvoiceGO Technologies (Pvt) Ltd. All rights reserved.</div>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white-50 text-decoration-none hover-white"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-white-50 text-decoration-none hover-white"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="text-white-50 text-decoration-none hover-white"><i class="bi bi-facebook"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Interactive Contact Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark font-display fs-5">Contact InvoiceGO Support</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Our dedicated tax &amp; FBR integration team is here to assist you.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Your Name</label>
                        <input type="text" class="form-control" placeholder="Ahmed Khan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Business Email</label>
                        <input type="email" class="form-control" placeholder="ahmed@company.pk">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Message / FBR Inquiry</label>
                        <textarea class="form-control" rows="3" placeholder="Tell us about your business sector or FBR DI requirements..."></textarea>
                    </div>
                    <button type="button" class="btn btn-primary-blue w-100 py-2" onclick="alert('Thank you! Our support team will get in touch with you shortly at your registered email.');" data-bs-dismiss="modal">
                        Send Message
                    </button>
                    <div class="text-center mt-3 text-muted small">
                        Or email us directly at <a href="mailto:support@invoicego.pk" class="text-primary text-decoration-none fw-semibold">support@invoicego.pk</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Interactive Preview Script -->
    <script>
        function switchPreviewTab(tabName, btnElement) {
            // Update active button state
            document.querySelectorAll('.workspace-nav-btn').forEach(btn => btn.classList.remove('active'));
            if (btnElement) {
                btnElement.classList.add('active');
            }

            // Hide all tab panes
            document.querySelectorAll('.preview-tab-pane').forEach(pane => pane.classList.add('d-none'));

            // Show selected tab pane
            const targetPane = document.getElementById('tab-' + tabName);
            if (targetPane) {
                targetPane.classList.remove('d-none');
            }
        }

        function calcPreviewInvoice() {
            const qty = parseFloat(document.getElementById('preview-qty').value) || 0;
            const price = parseFloat(document.getElementById('preview-price').value) || 0;
            const subtotal = qty * price;
            const gst = subtotal * 0.18;
            const grandTotal = subtotal + gst;
            document.getElementById('preview-total').innerText = 'PKR ' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    </script>
</body>
</html>
