<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\HsCode;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePrintAndCalculationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Business $business;
    protected Customer $registeredCustomer;
    protected Customer $unregisteredCustomer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->business = Business::create([
            'name' => 'Apex Technologies Pvt Ltd',
            'ntn_cnic' => '6110188932805',
            'strn' => '123456789',
            'province' => 'Punjab',
            'address' => 'Plot 45, Phase 5, DHA Lahore',
            'business_type' => 'company',
            'sector' => 'All Other Sectors',
            'business_natures' => ['Manufacturer'],
            'status' => 'approved',
            'current_environment' => 'sandbox',
            'production_submissions_enabled' => false,
        ]);
        $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);

        $this->registeredCustomer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Corporate Buyer Ltd',
            'ntn_cnic' => '1234567',
            'strn' => '987654321',
            'province' => 'Sindh',
            'registration_type' => 'registered',
        ]);

        $this->unregisteredCustomer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Taxpayer 3330318202735',
            'ntn_cnic' => '3330318202735',
            'province' => 'Capital Territory',
            'address' => 'G-9/4, Islamabad',
            'registration_type' => 'unregistered',
        ]);

        HsCode::firstOrCreate(['pct_code' => '0101.2100'], [
            'description' => 'Pure-bred breeding animals',
            'default_tax_rate' => 18.00,
            'is_active' => true,
        ]);

        app(TenantServiceInterface::class)->setActiveBusiness($this->business);
    }

    public function test_print_view_renders_matching_design_with_verified_fbr_qr_box(): void
    {
        $invoice = Invoice::create([
            'business_id' => $this->business->id,
            'created_by' => $this->user->id,
            'customer_id' => $this->unregisteredCustomer->id,
            'invoice_number' => 'INV-202601-0016',
            'invoice_date' => '2026-01-18',
            'invoice_type' => 'standard',
            'environment' => 'sandbox',
            'status' => 'submitted',
            'fbr_invoice_number' => '6110196137679DIABSXED179925',
            'fbr_qr_code' => 'https://fbr.gov.pk/verify?inv=INV-202601-0016&fbr=6110196137679DIABSXED179925',
            'sales_value_excl_tax' => 350000.00,
            'sales_tax_amount' => 81000.00,
            'further_tax_amount' => 18000.00,
            'total_invoice_value' => 449000.00,
            'net_receivable_amount' => 449000.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'business_id' => $this->business->id,
            'product_description' => 'Oxen',
            'hs_code' => '0101.2100',
            'quantity' => 10,
            'uom' => 'Numbers, pieces, units',
            'unit_price' => 35000.00,
            'value_excl_tax' => 350000.00,
            'sale_type' => '3rd Schedule Goods',
            'fixed_notified_value' => 45000.00,
            'retail_price' => 45000.00,
            'tax_rate' => 18.00,
            'sales_tax_amount' => 81000.00,
            'further_tax_rate' => 4.00,
            'further_tax_amount' => 18000.00,
            'total_value_incl_tax' => 449000.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('tenant.invoices.print', $invoice->uuid));

        $response->assertOk();
        $response->assertSee('FBR TAX INVOICE');
        $response->assertSee('INV-202601-0016');
        $response->assertSee('6110196137679DIABSXED179925');
        $response->assertSee('FBR VERIFIED INVOICE');
        $response->assertSee('SELLER INFORMATION');
        $response->assertSee('BUYER INFORMATION');
        $response->assertSee('Taxpayer 3330318202735');
        $response->assertSee('Unregistered');
        $response->assertSee('350,000.00');
        $response->assertSee('81,000.00');
        $response->assertSee('18,000.00');
        $response->assertSee('Rs 449,000.00');
    }

    public function test_draft_invoice_print_view_shows_pending_notice_without_qr_code(): void
    {
        $invoice = Invoice::create([
            'business_id' => $this->business->id,
            'created_by' => $this->user->id,
            'customer_id' => $this->unregisteredCustomer->id,
            'invoice_number' => 'INV-202601-0017',
            'invoice_date' => '2026-01-18',
            'invoice_type' => 'standard',
            'environment' => 'sandbox',
            'status' => 'draft',
            'fbr_invoice_number' => null,
            'sales_value_excl_tax' => 10000.00,
            'sales_tax_amount' => 1800.00,
            'further_tax_amount' => 400.00,
            'total_invoice_value' => 12200.00,
            'net_receivable_amount' => 12200.00,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'business_id' => $this->business->id,
            'product_description' => 'Test Goods',
            'hs_code' => '0101.2100',
            'quantity' => 1,
            'uom' => 'NOS',
            'unit_price' => 10000.00,
            'value_excl_tax' => 10000.00,
            'tax_rate' => 18.00,
            'sales_tax_amount' => 1800.00,
            'further_tax_rate' => 4.00,
            'further_tax_amount' => 400.00,
            'total_value_incl_tax' => 12200.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('tenant.invoices.print', $invoice->uuid));

        $response->assertOk();
        $response->assertSee('FBR TAX INVOICE');
        $response->assertSee('FBR Invoicing Pending');
        $response->assertDontSee('FBR VERIFIED INVOICE');
    }

    public function test_store_invoice_with_fixed_notified_value_and_further_tax(): void
    {
        $payload = [
            'action' => 'save',
            'customer_id' => $this->unregisteredCustomer->id,
            'buyer_type' => 'unregistered',
            'invoice_type' => 'Sale Invoice',
            'invoice_date' => now('Asia/Karachi')->format('Y-m-d'),
            'supplier_province' => 'Punjab',
            'destination_of_supply' => 'Punjab',
            'payment_mode' => 'Cash',
            'items' => [
                [
                    'product_description' => 'Oxen Supply',
                    'hs_code' => '0101.2100',
                    'quantity' => 10,
                    'uom' => 'Numbers, pieces, units',
                    'unit_price' => 35000.00,
                    'fixed_notified_value' => 45000.00,
                    'tax_rate' => '18.00%',
                    'sales_tax_amount' => 81000.00,
                    'further_tax_amount' => 18000.00,
                    'discount_amount' => 0.00,
                    'sale_type' => '3rd Schedule Goods',
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->post(route('tenant.invoices.store'), $payload);
        $response->assertSessionHasNoErrors();

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'business_id' => $this->business->id,
            'sales_value_excl_tax' => 350000.00,
            'sales_tax_amount' => 81000.00,
            'further_tax_amount' => 18000.00,
            'total_invoice_value' => 449000.00,
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'product_description' => 'Oxen Supply',
            'hs_code' => '0101.2100',
            'fixed_notified_value' => 45000.00,
            'retail_price' => 45000.00,
            'sales_tax_amount' => 81000.00,
            'further_tax_amount' => 18000.00,
        ]);
    }
}
