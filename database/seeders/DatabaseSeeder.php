<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Business;
use App\Models\Customer;
use App\Models\HsCode;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SandboxScenario;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Role-Based Admin Accounts (Super Admin, Admin, Support Staff)
        $primaryAdmin = User::firstOrCreate(
            ['email' => 'superadmin@invoicego.pk'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'phone' => '03001112233',
                'password' => Hash::make('AdminPass123!'),
                'is_super_admin' => true,
                'admin_role' => 'super_admin',
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@invoicego.pk'],
            [
                'name' => 'Admin Manager',
                'username' => 'admin',
                'phone' => '03002223344',
                'password' => Hash::make('AdminPass123!'),
                'is_super_admin' => false,
                'admin_role' => 'admin',
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'supportstaff@invoicego.pk'],
            [
                'name' => 'Support Staff',
                'username' => 'supportstaff',
                'phone' => '03003334455',
                'password' => Hash::make('AdminPass123!'),
                'is_super_admin' => false,
                'admin_role' => 'support_staff',
                'status' => 'active',
            ]
        );

        // 2. Registered Business Owner User: Noman Khan
        $demoUser = User::firstOrCreate(
            ['email' => 'nomankhan@invoicego.pk'],
            [
                'name' => 'Noman Khan',
                'username' => 'nomankhan',
                'phone' => '03219876543',
                'cnic' => '35201-1234567-1',
                'password' => Hash::make('Password123!'),
                'is_super_admin' => false,
                'admin_role' => 'user',
                'status' => 'active',
            ]
        );

        // 3. Business Account for Noman Khan
        $demoBusiness = Business::firstOrCreate(
            ['ntn_cnic' => '1234125'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Noman Khan Enterprises',
                'business_type' => 'Company',
                'strn' => '3277876123456',
                'address' => 'Plot 42, Industrial Area, Phase 2, Lahore',
                'city' => 'Lahore',
                'province' => 'Punjab',
                'sector' => 'All Other Sectors',
                'business_natures' => ['Manufacturer', 'Distributor', 'Retailer'],
                'is_retailer_registered' => true,
                'status' => 'approved',
                'current_environment' => 'sandbox',
                'fbr_registration_status' => 'Registered',
                'fbr_integration_status' => 'Sandbox Testing',
                'production_submissions_enabled' => false,
                'invoice_limit' => 50,
                'approved_by' => $primaryAdmin->id,
                'approved_at' => now(),
            ]
        );

        // Link Noman Khan to Noman Khan Enterprises as Owner
        $demoUser->businesses()->syncWithoutDetaching([
            $demoBusiness->id => [
                'role' => 'owner',
                'is_default' => true,
            ]
        ]);

        // 4. Seed 30-Day Monthly Subscription for Noman Khan Enterprises
        $proPlan = \App\Models\PricingPlan::where('slug', 'professional')->first();
        $subscription = Subscription::firstOrCreate(
            ['business_id' => $demoBusiness->id],
            [
                'pricing_plan_id' => $proPlan?->id,
                'plan_name' => $proPlan?->name ?? 'Professional',
                'starts_at' => now(),
                'expires_at' => now()->addDays(30),
                'status' => 'active',
                'monthly_fee' => 5000.00,
                'auto_renew' => true,
                'notes' => 'Active 30-day billing cycle for Noman Khan Enterprises.',
            ]
        );

        // 5. Seed Verified Payment
        Payment::firstOrCreate(
            ['transaction_reference' => 'TXN-INVGO-202601'],
            [
                'business_id' => $demoBusiness->id,
                'user_id' => $demoUser->id,
                'subscription_id' => $subscription->id,
                'amount' => 5000.00,
                'currency' => 'PKR',
                'payment_method' => 'Bank Transfer',
                'status' => 'successful',
                'receipt_url' => null,
                'notes' => 'Initial subscription activation verified by admin.',
                'verified_by' => $primaryAdmin->id,
                'verified_at' => now(),
            ]
        );

        // 6. Seed Standard HS / PCT Codes
        $this->call(HsCodeSeeder::class);

        // 7. Seed Official FBR Sandbox Scenarios (SN001 - SN028) from Scenario Data specification
        $this->call(SandboxScenarioSeeder::class);

        // 8. Seed Sample Customers and Products for Demo Business
        $customer1 = Customer::firstOrCreate(
            ['business_id' => $demoBusiness->id, 'ntn_cnic' => '7654321'],
            [
                'name' => 'Apex Logistics Pvt Ltd',
                'strn' => '1700987654321',
                'address' => 'Gulberg III, Main Boulevard, Lahore',
                'province' => 'Punjab',
                'registration_type' => 'registered',
                'fbr_verification_status' => 'verified',
                'contact_person' => 'Kamran Shah',
                'phone' => '03009988776',
                'email' => 'finance@apexlogistics.pk',
            ]
        );

        $customer2 = Customer::firstOrCreate(
            ['business_id' => $demoBusiness->id, 'ntn_cnic' => '3520112345678'],
            [
                'name' => 'Tariq Traders (Unregistered)',
                'strn' => null,
                'address' => 'Shop #12, Shah Alam Market, Lahore',
                'province' => 'Punjab',
                'registration_type' => 'unregistered',
                'fbr_verification_status' => 'verified',
                'contact_person' => 'Tariq Mehmood',
                'phone' => '03215544332',
                'email' => 'tariqtraders@example.com',
            ]
        );

        $hs1 = HsCode::where('pct_code', '8471.3010')->first() ?? HsCode::first();

        Product::firstOrCreate(
            ['business_id' => $demoBusiness->id, 'sku' => 'LAP-DELL-5520'],
            [
                'hs_code_id' => $hs1?->id,
                'name' => 'Dell Latitude 5520 Laptop',
                'description' => 'Intel Core i7 11th Gen, 16GB RAM, 512GB SSD',
                'uom' => 'NOS',
                'unit_price' => 185000.00,
                'default_tax_rate' => 18.00,
                'retail_price' => 205000.00,
                'is_active' => true,
            ]
        );

        // 9. Seed Sample Support Ticket
        SupportTicket::firstOrCreate(
            ['ticket_number' => 'TCK-2026-001'],
            [
                'business_id' => $demoBusiness->id,
                'user_id' => $demoUser->id,
                'subject' => 'Assistance with PRAL Production Sandbox Qualification',
                'category' => 'FBR Integration',
                'priority' => 'high',
                'status' => 'open',
                'message' => 'Hello team, we have completed our initial 5 test invoices and would like to verify our production API credentials.',
                'admin_reply' => 'Dear Noman, thank you for reaching out. Please execute scenarios SN001 through SN005 in your sandbox panel so our system can auto-verify your compliance.',
                'replied_by' => $primaryAdmin->id,
                'replied_at' => now(),
            ]
        );

        // 10. Seed Announcements
        Announcement::firstOrCreate(
            ['title' => 'FBR DI API v1.12 Compliance Update Active'],
            [
                'message' => 'All invoice submissions are actively validating with the updated FBR Digital Invoicing API v1.12 specification, including 4% Further Tax calculations and mandatory HS code validations.',
                'type' => 'info',
                'is_active' => true,
                'created_by' => $primaryAdmin->id,
                'starts_at' => now(),
                'expires_at' => now()->addDays(60),
            ]
        );

        // 11. Seed System Settings
        $settings = [
            'site_name' => 'InvoiceGO',
            'site_tagline' => 'Official FBR Digital Invoicing & Tax Compliance Platform',
            'software_version' => 'v2.4.0 (FBR DI API v1.12)',
            'company_name' => 'InvoiceGO Technologies (Pvt) Ltd',
            'company_ntn' => '1234125',
            'company_strn' => '3277876123456',
            'company_address' => 'Floor 4, Software Technology Park, Ferozepur Road, Lahore, Pakistan',
            'support_phone' => '+92 42 111 468 642',
            'support_email' => 'support@invoicego.pk',
            'admin_contact' => 'admininvoicego@gmail.com',
            'fbr_sandbox_url' => 'https://gw.fbr.gov.pk/im/v1/sandbox',
            'fbr_production_url' => 'https://gw.fbr.gov.pk/im/v1/production',
            'fbr_api_timeout' => '30',
            'payment_bank_name' => 'Meezan Bank Limited',
            'payment_account_title' => 'InvoiceGO Technologies (Pvt) Ltd',
            'payment_account_iban' => 'PK42MEZN0001234567890123',
            'backup_frequency' => 'Daily at 02:00 AM PST',
            'backup_retention_days' => '30',
            'enable_public_registration' => '0',
            'show_pricing_plans' => '0',
            'show_faq_section' => '1',
            'default_invoice_limit' => '50',
        ];

        foreach ($settings as $key => $val) {
            $settingType = match (true) {
                in_array($key, ['enable_public_registration', 'show_pricing_plans', 'show_faq_section'], true) => 'boolean',
                $key === 'default_invoice_limit' => 'integer',
                default => 'string',
            };
            $settingGroup = $key === 'default_invoice_limit' ? 'invoice' : 'general';

            SystemSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $val, 'group' => $settingGroup, 'type' => $settingType]
            );
        }

        // 11b. Seed Website Pricing Plans
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
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
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
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
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
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
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            \App\Models\PricingPlan::firstOrCreate(['slug' => $planData['slug']], $planData);
        }

        // 11c. Seed Website FAQs
        $faqs = [
            [
                'question' => 'What is InvoiceGO?',
                'answer' => "InvoiceGO is Pakistan's leading cloud-based digital sales invoicing and FBR integration platform. It enables businesses to prepare, validate, electronically transmit, and archive sales invoices in 100% compliance with official FBR Digital Invoicing (DI) API v1.12 regulations.",
                'category' => 'General',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'question' => 'What is FBR Digital Invoicing?',
                'answer' => "FBR Digital Invoicing is the electronic transmission of sales tax invoices from registered taxpayers to the Federal Board of Revenue's centralized system in real time. Each accepted invoice receives a unique FBR Invoice Number and verified QR code.",
                'category' => 'FBR Integration',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'question' => 'How can I create an account?',
                'answer' => "Click on \"Get Started\" or \"Create Account\". Enter your business details, legal name, NTN or CNIC, STRN (if applicable), and province. Your workspace is configured immediately with full sandbox testing access.",
                'category' => 'Account & Billing',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'question' => 'Can I manage multiple invoices?',
                'answer' => "Yes! InvoiceGO allows you to create individual invoices, save incomplete invoices as drafts without restrictive validations, and upload hundreds of invoices in bulk using our standardized Excel / CSV bulk upload template.",
                'category' => 'Invoicing & Features',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'question' => 'Does InvoiceGO support FBR API integration?',
                'answer' => "Yes, InvoiceGO integrates directly with the official FBR PRAL Digital Invoicing API v1.12. It includes a built-in runner for all 28 technical sandbox scenarios (SN001–SN028) needed to qualify for your production API token.",
                'category' => 'FBR Integration',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'question' => 'Can I access InvoiceGO from my mobile?',
                'answer' => "Yes. InvoiceGO is 100% web-based and cloud-native. You can access your dashboard, monitor daily revenue, and issue or print invoices from any desktop, laptop, tablet, or smartphone without installing any software.",
                'category' => 'General',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'question' => 'Is my business information secure?',
                'answer' => "Security is our top priority. We use AES-256 bank-grade encryption for all database records and API tokens. FBR tokens are encrypted on the server and never exposed in the browser, with tenant-level data isolation.",
                'category' => 'Security & Privacy',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'question' => 'How can I contact support?',
                'answer' => "Our technical support team is available via email at support@invoicego.pk, via phone during business hours, or directly through the in-app support ticketing system in your portal.",
                'category' => 'Support',
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $faqData) {
            \App\Models\Faq::firstOrCreate(['question' => $faqData['question']], $faqData);
        }

        // 12. Seed Activity Logs
        $logs = [
            [
                'business_id' => $demoBusiness->id,
                'user_id' => $primaryAdmin->id,
                'action' => 'login',
                'description' => 'Super Administrator (admininvoicego@gmail.com) authenticated successfully.',
                'properties' => ['browser' => 'Chrome', 'os' => 'Windows', 'panel' => 'admin'],
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subHours(5),
            ],
            [
                'business_id' => $demoBusiness->id,
                'user_id' => $primaryAdmin->id,
                'action' => 'business_approved',
                'description' => "Business 'OC1' (NTN: 1234125) approved for Sandbox testing.",
                'properties' => ['status' => 'approved', 'reviewed_by' => 'InvoiceGO Super Admin'],
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subHours(4),
            ],
            [
                'business_id' => $demoBusiness->id,
                'user_id' => $primaryAdmin->id,
                'action' => 'payment_verified',
                'description' => 'Verified initial subscription activation payment of Rs 5,000.00 (Ref: TXN-INVGO-202601).',
                'properties' => ['amount' => 5000, 'method' => 'Bank Transfer'],
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subHours(3),
            ],
            [
                'business_id' => $demoBusiness->id,
                'user_id' => $demoUser->id,
                'action' => 'sandbox_scenario_run',
                'description' => 'Executed Sandbox Qualification Scenario SN001 - Standard Rate Goods Supply.',
                'properties' => ['scenario_code' => 'SN001', 'status' => 'passed'],
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subHours(2),
            ],
            [
                'business_id' => $demoBusiness->id,
                'user_id' => $demoUser->id,
                'action' => 'invoice_created',
                'description' => 'Invoice INV-202601-0001 created for Apex Logistics Pvt Ltd (Total: Rs 449,000.00).',
                'properties' => ['invoice_number' => 'INV-202601-0001', 'total' => 449000],
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subHour(),
            ],
            [
                'business_id' => $demoBusiness->id,
                'user_id' => $demoUser->id,
                'action' => 'fbr_submission',
                'description' => 'Invoice INV-202601-0001 accepted by FBR DI API (FBR #: 6110196137679DIABSXED179925).',
                'properties' => ['fbr_invoice_number' => '6110196137679DIABSXED179925', 'result' => 'ACCEPTED'],
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subMinutes(30),
            ],
        ];

        foreach ($logs as $log) {
            ActivityLog::firstOrCreate(
                ['description' => $log['description']],
                $log
            );
        }
    }
}
