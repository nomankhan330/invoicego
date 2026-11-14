<?php

namespace Database\Seeders;

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
        // 1. Super Admin User specified in document: admininvoicego@gmail.com
        $primaryAdmin = User::firstOrCreate(
            ['email' => 'admininvoicego@gmail.com'],
            [
                'name' => 'InvoiceGO Super Admin',
                'phone' => '03001234567',
                'password' => Hash::make('AdminPass123!'),
                'is_super_admin' => true,
                'status' => 'active',
            ]
        );

        // Keep secondary admin for backward test compatibility
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@invoicego.pk'],
            [
                'name' => 'Super Administrator',
                'phone' => '03001234567',
                'password' => Hash::make('AdminPass123!'),
                'is_super_admin' => true,
                'status' => 'active',
            ]
        );

        // 2. Demo User matching Screenshot 4 (Noman, nomanoc1a@gmail.com)
        $demoUser = User::firstOrCreate(
            ['email' => 'nomanoc1a@gmail.com'],
            [
                'name' => 'Noman',
                'phone' => '03219876543',
                'password' => Hash::make('Password123!'),
                'is_super_admin' => false,
                'status' => 'active',
            ]
        );

        // 3. Demo Business matching Screenshot 4 (OC1, NTN 1234125)
        $demoBusiness = Business::firstOrCreate(
            ['ntn_cnic' => '1234125'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'OC1',
                'strn' => '3277876123456',
                'address' => 'Plot 42, Industrial Area, Phase 2, Lahore',
                'province' => 'Punjab',
                'sector' => 'All Other Sectors',
                'business_natures' => ['Manufacturer', 'Retailer'],
                'is_retailer_registered' => true,
                'status' => 'approved',
                'current_environment' => 'sandbox',
                'production_submissions_enabled' => false,
                'approved_by' => $primaryAdmin->id,
                'approved_at' => now(),
            ]
        );

        // Link Users to Business as Owner
        $demoUser->businesses()->syncWithoutDetaching([
            $demoBusiness->id => [
                'role' => 'owner',
                'is_default' => true,
            ]
        ]);

        $primaryAdmin->businesses()->syncWithoutDetaching([
            $demoBusiness->id => [
                'role' => 'owner',
                'is_default' => true,
            ]
        ]);

        $superAdmin->businesses()->syncWithoutDetaching([
            $demoBusiness->id => [
                'role' => 'owner',
                'is_default' => true,
            ]
        ]);

        // 4. Seed 30-Day Monthly Subscription for Business
        $subscription = Subscription::firstOrCreate(
            ['business_id' => $demoBusiness->id],
            [
                'plan_name' => 'Monthly Professional Plan',
                'starts_at' => now(),
                'expires_at' => now()->addDays(30),
                'status' => 'active',
                'monthly_fee' => 5000.00,
                'auto_renew' => true,
                'notes' => 'Active 30-day billing cycle for OC1 business.',
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

        // 7. Seed Complete Sandbox Scenarios (SN001 - SN028)
        $scenarios = [
            ['scenario_code' => 'SN001', 'title' => 'Standard Rate Goods Supply', 'description' => 'Standard rate goods supplied to registered buyers with 18% sales tax.'],
            ['scenario_code' => 'SN002', 'title' => 'Reduced Rate Supply', 'description' => 'Supplies taxable at reduced rates specified in the Eighth Schedule.'],
            ['scenario_code' => 'SN003', 'title' => 'Unregistered Buyer with Further Tax', 'description' => 'Supplies to unregistered persons requiring 4% further tax under section 3(1A).'],
            ['scenario_code' => 'SN004', 'title' => 'Exempt Supply', 'description' => 'Goods exempt from sales tax under Sixth Schedule.'],
            ['scenario_code' => 'SN005', 'title' => 'Zero-rated Export Supply', 'description' => 'Exports of goods chargeable to sales tax at zero rate under Fifth Schedule.'],
            ['scenario_code' => 'SN006', 'title' => 'Services Supply at Provincial Rate', 'description' => 'Taxable services invoiced according to provincial sales tax on services enactments.'],
            ['scenario_code' => 'SN007', 'title' => 'Debit Note for Upward Price Adjustment', 'description' => 'Debit note issued under section 9 for upward revision of price or quantity.'],
            ['scenario_code' => 'SN008', 'title' => 'Credit Note for Return / Discount', 'description' => 'Credit note issued under section 9 for sales return or downward revision.'],
            ['scenario_code' => 'SN009', 'title' => 'Withholding Sales Tax Deduction', 'description' => 'Supplies where buyer is an active withholding agent deducting sales tax at source.'],
            ['scenario_code' => 'SN010', 'title' => 'Third Schedule Goods (MRP Basis)', 'description' => 'Supplies of goods where tax is levied on retail price printed on the pack.'],
            ['scenario_code' => 'SN011', 'title' => 'Debit Note Cancellation', 'description' => 'Debit note issued for cancellation of standard sales tax invoice.'],
            ['scenario_code' => 'SN012', 'title' => 'Supply to Withholding Agent (Company to Company)', 'description' => 'B2B supply subject to standard withholding tax rules.'],
            ['scenario_code' => 'SN013', 'title' => 'Export of Goods with Zero-rating & SRO', 'description' => 'Direct export goods with international consignee details.'],
            ['scenario_code' => 'SN014', 'title' => 'Supply of Goods with Extra Tax Levied', 'description' => 'Supplies subject to extra tax under special regulatory orders.'],
            ['scenario_code' => 'SN015', 'title' => 'Advance Payment & Sales Tax Adjustment', 'description' => 'Invoice issued against advance payment receipt.'],
            ['scenario_code' => 'SN016', 'title' => 'Mixed Supply (Standard + Exempt Items)', 'description' => 'Multi-line invoice containing both standard and exempt items.'],
            ['scenario_code' => 'SN017', 'title' => 'Multi-Rate Goods Supply', 'description' => 'Invoice containing line items with multiple differing sales tax rates.'],
            ['scenario_code' => 'SN018', 'title' => 'Credit Note for Partial Sales Return', 'description' => 'Credit note issued for return of a specific portion of previous goods.'],
            ['scenario_code' => 'SN019', 'title' => 'Debit Note for Supplementary Differential Tax', 'description' => 'Debit note issued to account for tax rate increase.'],
            ['scenario_code' => 'SN020', 'title' => 'Supply to Government / Public Sector Entity', 'description' => 'Supply to state-owned enterprise with mandatory sales tax withholding.'],
            ['scenario_code' => 'SN021', 'title' => 'Toll Manufacturing Services Invoice', 'description' => 'Service invoice for contract conversion or processing.'],
            ['scenario_code' => 'SN022', 'title' => 'Supply from EPZ to Tariff Area', 'description' => 'Goods entering domestic tariff area from Export Processing Zone.'],
            ['scenario_code' => 'SN023', 'title' => 'Special FED & Sales Tax Combination', 'description' => 'Goods subject to both Federal Excise Duty and Sales Tax.'],
            ['scenario_code' => 'SN024', 'title' => 'Supply of Energy / Electric Power', 'description' => 'Utility and bulk energy distribution tax invoice.'],
            ['scenario_code' => 'SN025', 'title' => 'End-to-End Invoice Reversal & Reconciliation', 'description' => 'Full credit note offsetting an accepted invoice.'],
            ['scenario_code' => 'SN026', 'title' => 'Tier-1 Retailer POS Integration Test', 'description' => 'Invoice issued by registered Tier-1 retailer through integrated POS.'],
            ['scenario_code' => 'SN027', 'title' => 'Retailer Cash vs Digital Payment', 'description' => 'Tier-1 retailer invoice paid through digital POS card terminal.'],
            ['scenario_code' => 'SN028', 'title' => 'Retailer Customer Return', 'description' => 'Return and refund slip processed by registered retailer.'],
        ];

        foreach ($scenarios as $item) {
            SandboxScenario::firstOrCreate(['scenario_code' => $item['scenario_code']], $item);
        }

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
        ];

        foreach ($settings as $key => $val) {
            SystemSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $val, 'group' => 'general', 'type' => 'string']
            );
        }
    }
}
