<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Contracts\FbrIntegrationServiceInterface;
use App\Services\Contracts\FbrValidationServiceInterface;
use App\Services\Contracts\TenantServiceInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FbrValidationAndInvoicingTest extends TestCase
{
    use RefreshDatabase;

    public function test_fbr_current_month_validation_and_unregistered_further_tax(): void
    {
        $validator = app(FbrValidationServiceInterface::class);

        // 1. Test current month check in Pakistan Standard Time
        $pktNow = Carbon::now('Asia/Karachi');
        $this->assertTrue($validator->isCurrentMonthInPakistan($pktNow));

        $lastMonth = $pktNow->copy()->subMonth();
        $this->assertFalse($validator->isCurrentMonthInPakistan($lastMonth));

        // 2. Test mathematical calculations for registered vs unregistered buyer
        $calcRegistered = $validator->calculateItemTotals([
            'quantity' => 2,
            'unit_price' => 10000.00,
            'tax_rate' => 18.00,
            'discount_amount' => 0.00,
        ], false);

        $this->assertEquals(20000.00, $calcRegistered['value_excl_tax']);
        $this->assertEquals(3600.00, $calcRegistered['sales_tax_amount']);
        $this->assertEquals(0.00, $calcRegistered['further_tax_amount']);
        $this->assertEquals(23600.00, $calcRegistered['total_value_incl_tax']);

        // Unregistered buyer: 4% Further Tax (§3(1A)) applies: 4% of 20,000 = 800. Total = 24,400.
        $calcUnregistered = $validator->calculateItemTotals([
            'quantity' => 2,
            'unit_price' => 10000.00,
            'tax_rate' => 18.00,
            'further_tax_rate' => 4.00,
            'discount_amount' => 0.00,
        ], true);

        $this->assertEquals(20000.00, $calcUnregistered['value_excl_tax']);
        $this->assertEquals(3600.00, $calcUnregistered['sales_tax_amount']);
        $this->assertEquals(800.00, $calcUnregistered['further_tax_amount']);
        $this->assertEquals(24400.00, $calcUnregistered['total_value_incl_tax']);
    }

    public function test_invoice_creation_and_fbr_submission_workflow(): void
    {
        $user = User::factory()->create(['name' => 'Tax Manager', 'email' => 'tax@example.com']);
        $business = Business::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Apex Traders',
            'ntn_cnic' => '1234567',
            'address' => 'Mall Road, Lahore',
            'province' => 'Punjab',
            'sector' => 'All Other Sectors',
            'business_natures' => ['Retailer'],
            'status' => 'approved',
            'current_environment' => 'sandbox',
            'sandbox_token' => 'mock_sandbox_token_123',
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Metro Retailers',
            'ntn_cnic' => '7654321',
            'registration_type' => 'registered',
            'fbr_verification_status' => 'verified',
        ]);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $pktNow = Carbon::now('Asia/Karachi');

        // Post valid invoice payload
        $invoiceData = [
            'customer_id' => $customer->id,
            'invoice_date' => $pktNow->format('Y-m-d'),
            'invoice_type' => 'standard',
            'environment' => 'sandbox',
            'items' => [
                [
                    'product_description' => 'Dell Laptop Latitude',
                    'hs_code' => '8471.30.00',
                    'quantity' => 1,
                    'uom' => 'NOS',
                    'unit_price' => 100000.00,
                    'tax_rate' => 18.00,
                    'further_tax_rate' => 0.00,
                    'st_withheld_rate' => 0.00,
                    'discount_amount' => 0.00,
                ],
            ],
            'submit_to_fbr' => '1',
        ];

        $response = $this->post(route('tenant.invoices.store'), $invoiceData);

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        $this->assertEquals('accepted', $invoice->status);
        $this->assertNotNull($invoice->fbr_invoice_number);
        $this->assertStringStartsWith('DI-', $invoice->fbr_invoice_number);
        $this->assertNotNull($invoice->fbr_qr_code);

        $response->assertRedirect(route('tenant.invoices.show', $invoice->uuid));

        // Test past month rejection rule
        $pastMonthData = $invoiceData;
        $pastMonthData['invoice_date'] = $pktNow->copy()->subMonths(2)->format('Y-m-d');

        $invalidResponse = $this->post(route('tenant.invoices.store'), $pastMonthData);
        $invalidResponse->assertSessionHasErrors('invoice_date');
    }
}
