<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\HsCode;
use App\Models\User;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DraftStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_draft_from_create_form(): void
    {
        HsCode::create([
            'pct_code' => '8471.30.00',
            'description' => 'Test HS Code',
            'standard_tax_rate' => 18.00,
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'OC1',
            'ntn_cnic' => '1234125',
            'strn' => '9988776655',
            'province' => 'Punjab',
            'address' => 'OC1 Address',
            'sector' => 'All Other Sectors',
            'business_natures' => ['Manufacturer'],
            'status' => 'approved',
            'current_environment' => 'sandbox',
            'production_submissions_enabled' => false,
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $payload = [
            'action' => 'draft',
            'invoice_type' => 'Sale Invoice',
            'invoice_number' => '',
            'invoice_date' => now('Asia/Karachi')->format('Y-m-d'),
            'invoice_time' => '05:08 PM',
            'supplier_province' => 'Punjab',
            'destination_of_supply' => 'Punjab',
            'payment_mode' => 'Cash',
            'reference_invoice_no' => '',
            'customer_id' => '',
            'buyer_ntn_cnic' => '',
            'buyer_name' => 'Walk In Customer',
            'buyer_province' => 'Punjab',
            'buyer_type' => 'unregistered',
            'buyer_address' => 'Pakistan',
            'items' => [
                1 => [
                    'product_id' => '',
                    'sale_type' => 'Goods at Standard Rate (Default)',
                    'hs_code' => '8471.30.00',
                    'tax_rate' => '18.00%',
                    'product_description' => 'Custom Goods Description',
                    'uom' => 'MT',
                    'quantity' => '2',
                    'unit_price' => '1500.00',
                    'discount_amount' => '0.00',
                    'fixed_notified_value' => '',
                    'retail_price' => '',
                    'extra_tax_amount' => '0.00',
                    'further_tax_rate' => '4.00',
                    'cancel_further_tax' => '0',
                    'sro_schedule_no' => '',
                    'sro_item_serial' => '',
                    'fed_payable_amount' => '0.00',
                    'st_withheld_rate' => '0.00',
                ]
            ]
        ];

        $response = $this->post(route('tenant.invoices.store'), $payload);

        if ($response->isRedirect() && session('errors')) {
            dump(session('errors')->all());
        }

        $this->assertDatabaseHas('invoices', [
            'buyer_name' => 'Walk In Customer',
            'status' => 'draft',
        ]);
    }
}

