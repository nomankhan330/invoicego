<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\HsCode;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Services\Contracts\TenantServiceInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnhancedCreateInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        HsCode::create([
            'pct_code' => '8471.30.00',
            'description' => 'Laptops and portable digital processing machines',
            'standard_tax_rate' => 18.00,
            'is_active' => true,
        ]);
    }

    protected function createBusiness(): Business
    {
        return Business::create([
            'name' => 'OC1',
            'ntn_cnic' => '1234125',
            'strn' => '9988776655',
            'province' => 'Punjab',
            'address' => 'OC1 Business Address',
            'sector' => 'All Other Sectors',
            'business_natures' => ['Manufacturer', 'Wholesaler'],
            'status' => 'approved',
            'current_environment' => 'sandbox',
            'production_submissions_enabled' => false,
        ]);
    }

    public function test_create_invoice_screen_renders_with_exact_layout_and_searchable_dropdowns(): void
    {
        $user = User::factory()->create();
        $business = $this->createBusiness();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Alpha Corporation',
            'ntn_cnic' => '7654321',
            'province' => 'Punjab',
            'address' => 'Plot 45 Industrial Area Lahore',
            'registration_type' => 'registered',
        ]);

        $product = Product::create([
            'business_id' => $business->id,
            'sku' => 'LAP-DELL-01',
            'name' => 'Dell Latitude Laptop',
            'description' => 'Business laptop core i7',
            'hs_code' => '8471.30.00',
            'uom' => 'NOS',
            'sale_type' => 'Goods (Standard)',
            'unit_price' => 120000.00,
            'default_tax_rate' => 18.00,
            'is_active' => true,
        ]);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $response = $this->get(route('tenant.invoices.create'));

        $response->assertStatus(200);

        // Header & badges matching media_1794574767306_a2f4afbf.jpg
        $response->assertSee('Create invoice');
        $response->assertSee('Seller details come from your business profile.');
        $response->assertSee('PRODUCTION');
        $response->assertDontSee('Production is not enabled yet');

        // Section 1: Invoice details
        $response->assertSee('Invoice details');
        $response->assertSee('Sale Invoice');
        $response->assertSee('Debit Note');
        $response->assertSee('Credit Note');
        $response->assertSee('Current month only');
        $response->assertSee('Shown on the invoice; not sent to FBR');
        $response->assertSee('How the buyer pays; not sent to FBR');
        $response->assertSee('Only for debit notes');

        // Section 2: Seller details
        $response->assertSee('Seller details (from business profile)');
        $response->assertSee('Edit profile');
        $response->assertSee('1234125');
        $response->assertSee('OC1');
        $response->assertSee('Punjab');

        // Section 3: Buyer details
        $response->assertSee('Buyer details');
        $response->assertSee('Saved customer');
        $response->assertSee('Search and select, or type a new buyer below');
        $response->assertSee('Buyer NTN / CNIC');
        $response->assertSee('7-digit NTN or 13-digit CNIC; optional for unregistered buyers');
        $response->assertSee('Buyer name');
        $response->assertSee('Buyer province');
        $response->assertSee('Verify registration with FBR');
        $response->assertSee('Buyer type');
        $response->assertSee('Registered');
        $response->assertSee('Unregistered');

        // Section 4: Items details
        $response->assertSee('Items details');
        $response->assertSee('Automatic calculation');
        $response->assertSee('Add item');

        // Section 5: Totals preview
        $response->assertSee('Totals preview');
        $response->assertSee('Value excl. ST');
        $response->assertSee('Sales tax');
        $response->assertSee('Further tax');
        $response->assertSee('Extra tax + FED');
        $response->assertSee('ST withheld');
        $response->assertSee('Gross invoice value (incl. taxes)');
        $response->assertSee('Net receivable (after ST withheld)');

        // Action Bar
        $response->assertSee('Cancel');
        $response->assertSee('Save as draft');
        $response->assertSee('Validate');
        $response->assertSee('Submit to FBR');

        // TomSelect CDN inclusion
        $response->assertSee('tom-select');
    }

    public function test_ajax_draft_validation_endpoint(): void
    {
        $user = User::factory()->create();
        $business = $this->createBusiness();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $pktNow = Carbon::now('Asia/Karachi');

        // Valid payload test
        $validPayload = [
            'buyer_name' => 'Metro Superstore',
            'buyer_ntn_cnic' => '1234567',
            'buyer_province' => 'PUNJAB',
            'buyer_address' => 'Gulberg III Lahore',
            'buyer_type' => 'registered',
            'invoice_date' => $pktNow->format('Y-m-d'),
            'invoice_type' => 'standard',
            'items' => [
                [
                    'product_description' => 'Test Item',
                    'hs_code' => '8471.30.00',
                    'quantity' => 2,
                    'uom' => 'NOS',
                    'unit_price' => 50000,
                    'tax_rate' => '18%',
                ]
            ],
        ];

        $response = $this->postJson(route('tenant.invoices.validate-draft'), $validPayload);
        $response->assertStatus(200);
        $response->assertJson(['valid' => true]);

        // Invalid date payload test
        $invalidPayload = $validPayload;
        $invalidPayload['invoice_date'] = $pktNow->copy()->subMonths(2)->format('Y-m-d');

        $responseInvalid = $this->postJson(route('tenant.invoices.validate-draft'), $invalidPayload);
        $responseInvalid->assertStatus(422);
        $responseInvalid->assertJson(['valid' => false]);
    }

    public function test_buyer_verification_atl_endpoint(): void
    {
        $user = User::factory()->create();
        $business = $this->createBusiness();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $response = $this->getJson(route('tenant.invoices.verify-buyer', ['ntn' => '7654321']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'registered' => true,
            'status' => 'Active in FBR ATL',
        ]);
    }

    public function test_store_invoice_with_all_enhanced_fields(): void
    {
        $user = User::factory()->create();
        $business = $this->createBusiness();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $pktNow = Carbon::now('Asia/Karachi');

        $payload = [
            'buyer_name' => 'Direct Walk-in Buyer',
            'buyer_ntn_cnic' => '3520112345671',
            'buyer_province' => 'PUNJAB',
            'buyer_address' => 'Mall Road Lahore',
            'buyer_type' => 'unregistered',
            'invoice_date' => $pktNow->format('Y-m-d'),
            'invoice_time' => '02:30 PM',
            'payment_mode' => 'Bank Transfer',
            'reference_invoice_no' => 'REF-001',
            'invoice_type' => 'standard',
            'environment' => 'sandbox',
            'items' => [
                [
                    'sale_type' => 'Goods (Standard)',
                    'hs_code' => '8471.30.00',
                    'product_description' => 'Dell Workstation i9',
                    'quantity' => 1,
                    'uom' => 'NOS',
                    'unit_price' => 200000.00,
                    'tax_rate' => '18%',
                    'discount_amount' => 5000.00,
                    'extra_tax_amount' => 1000.00,
                    'fed_payable_amount' => 500.00,
                    'further_tax_rate' => 4.00,
                    'st_withheld_rate' => 0.00,
                    'sro_schedule_no' => 'SRO 212(I)/2020',
                    'sro_item_serial' => 'Item-12',
                ]
            ],
        ];

        $response = $this->post(route('tenant.invoices.store'), $payload);

        $invoice = Invoice::latest('id')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('Direct Walk-in Buyer', $invoice->buyer_name);
        $this->assertEquals('unregistered', $invoice->buyer_type);
        $this->assertEquals('Bank Transfer', $invoice->payment_mode);
        $this->assertEquals('02:30 PM', $invoice->invoice_time);
        $this->assertEquals(200000.00, (float) $invoice->sales_value_excl_tax);
        $this->assertEquals(36000.00, (float) $invoice->sales_tax_amount); // 18% of 200000
        $this->assertEquals(8000.00, (float) $invoice->further_tax_amount); // 4% of 200000 for unregistered
        $this->assertEquals(1000.00, (float) $invoice->extra_tax_amount);
        $this->assertEquals(500.00, (float) $invoice->fed_amount);
        $this->assertEquals(5000.00, (float) $invoice->discount_amount);

        // Item level check
        $item = $invoice->items()->first();
        $this->assertNotNull($item);
        $this->assertEquals('Goods (Standard)', $item->sale_type);
        $this->assertEquals('SRO 212(I)/2020', $item->sro_schedule_no);
        $this->assertEquals('Item-12', $item->sro_item_serial);

        $response->assertRedirect(route('tenant.invoices.show', $invoice->uuid));
    }

    public function test_edit_invoice_renders_with_prepopulated_data(): void
    {
        $user = User::factory()->create();
        $business = $this->createBusiness();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Pre-saved Buyer',
            'ntn_cnic' => '7654321',
            'province' => 'Punjab',
            'address' => 'Lahore',
            'registration_type' => 'registered',
        ]);

        $pktNow = Carbon::now('Asia/Karachi');

        $invoice = Invoice::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'buyer_name' => 'Pre-saved Buyer',
            'buyer_ntn_cnic' => '7654321',
            'buyer_province' => 'Punjab',
            'buyer_address' => 'Lahore',
            'buyer_type' => 'registered',
            'invoice_number' => 'INV-202611-0001',
            'invoice_type' => 'standard',
            'environment' => 'sandbox',
            'invoice_date' => $pktNow->format('Y-m-d'),
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $response = $this->get(route('tenant.invoices.edit', $invoice->uuid));

        $response->assertStatus(200);
        $response->assertSee('Edit invoice: INV-202611-0001');
        $response->assertSee('Pre-saved Buyer');
        $response->assertSee('7654321');
    }
}
