<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Customer;
use App\Models\HsCode;
use App\Models\Product;
use App\Models\SandboxScenario;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin User
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
                'approved_by' => $superAdmin->id,
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

        $superAdmin->businesses()->syncWithoutDetaching([
            $demoBusiness->id => [
                'role' => 'owner',
                'is_default' => true,
            ]
        ]);

        // 4. Seed Standard HS / PCT Codes
        $this->call(HsCodeSeeder::class);

        // 5. Seed Sandbox Scenarios (SN001 - SN028)
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
            ['scenario_code' => 'SN026', 'title' => 'Tier-1 Retailer POS Integration Test', 'description' => 'Invoice issued by registered Tier-1 retailer through integrated POS.'],
            ['scenario_code' => 'SN027', 'title' => 'Retailer Cash vs Digital Payment', 'description' => 'Tier-1 retailer invoice paid through digital POS card terminal.'],
            ['scenario_code' => 'SN028', 'title' => 'Retailer Customer Return', 'description' => 'Return and refund slip processed by registered retailer.'],
        ];

        foreach ($scenarios as $item) {
            SandboxScenario::firstOrCreate(['scenario_code' => $item['scenario_code']], $item);
        }

        // 6. Seed Sample Customers and Products for Demo Business
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
                'is_active' => true,
            ]
        );
    }
}
