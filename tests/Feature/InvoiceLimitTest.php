<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvoiceLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function makeTenantUser(int $invoiceLimit = 50): array
    {
        $user = User::create([
            'name' => 'Noman Khan',
            'email' => 'noman.quota.' . Str::random(5) . '@invoicego.pk',
            'phone' => '03219876543',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
            'admin_role' => 'user',
        ]);

        $business = Business::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Noman Khan Enterprises',
            'ntn_cnic' => '1234125',
            'strn' => '3277876123456',
            'address' => 'Plot 42, Lahore',
            'province' => 'Punjab',
            'sector' => 'All Other Sectors',
            'business_natures' => ['Distributor'],
            'status' => 'approved',
            'current_environment' => 'sandbox',
            'invoice_limit' => $invoiceLimit,
        ]);

        $user->businesses()->attach($business->id, ['role' => 'owner', 'is_default' => true]);

        return [$user, $business];
    }

    protected function seedInvoicesForBusiness(Business $business, User $user, int $count, string $status = 'draft'): void
    {
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Test Buyer',
            'ntn_cnic' => '7654321',
            'address' => 'Lahore',
            'province' => 'Punjab',
            'registration_type' => 'registered',
        ]);

        for ($i = 1; $i <= $count; $i++) {
            Invoice::create([
                'uuid' => (string) Str::uuid(),
                'business_id' => $business->id,
                'customer_id' => $customer->id,
                'created_by' => $user->id,
                'invoice_number' => 'INV-TEST-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT) . '-' . Str::random(3),
                'invoice_type' => 'standard',
                'invoice_date' => now('Asia/Karachi')->format('Y-m-d'),
                'buyer_name' => "Buyer {$i}",
                'buyer_ntn_cnic' => '7654321',
                'buyer_province' => 'Punjab',
                'buyer_address' => 'Lahore',
                'buyer_type' => 'registered',
                'environment' => 'sandbox',
                'status' => $status,
                'sales_tax_amount' => 180,
                'total_invoice_value' => 1180,
            ]);
        }
    }

    public function test_50th_invoice_is_allowed_and_51st_invoice_is_strictly_blocked(): void
    {
        [$user, $business] = $this->makeTenantUser(50);
        $this->seedInvoicesForBusiness($business, $user, 49);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        // 50th invoice should succeed
        $res50 = $this->post('/invoices', [
            'action' => 'draft',
            'invoice_type' => 'standard',
            'invoice_date' => now('Asia/Karachi')->format('Y-m-d'),
            'buyer_name' => 'Buyer 50',
            'buyer_province' => 'Punjab',
            'buyer_address' => 'Lahore',
            'buyer_type' => 'unregistered',
            'items' => [
                [
                    'hs_code' => '0101.2100',
                    'product_description' => 'Item 50',
                    'uom' => 'NOS',
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'tax_rate' => '18%',
                ],
            ],
        ]);

        $res50->assertRedirect();
        $this->assertEquals(50, $business->fresh()->getInvoiceUsageCount());
        $this->assertTrue($business->fresh()->hasReachedInvoiceLimit());

        // 51st invoice MUST be strictly blocked
        $res51 = $this->post('/invoices', [
            'action' => 'draft',
            'invoice_type' => 'standard',
            'invoice_date' => now('Asia/Karachi')->format('Y-m-d'),
            'buyer_name' => 'Buyer 51 Blocked',
            'buyer_province' => 'Punjab',
            'buyer_address' => 'Lahore',
            'buyer_type' => 'unregistered',
            'items' => [
                [
                    'hs_code' => '0101.2100',
                    'product_description' => 'Item 51',
                    'uom' => 'NOS',
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'tax_rate' => '18%',
                ],
            ],
        ]);

        $res51->assertRedirect(route('tenant.invoices.index'));
        $res51->assertSessionHas('error');
        $this->assertStringContainsString('50 / 50', session('error'));
        $this->assertStringContainsString('#51', session('error'));
        $this->assertEquals(50, $business->fresh()->getInvoiceUsageCount());

        // Also check JSON response returns 422
        $resJson = $this->postJson('/invoices', ['action' => 'draft']);
        $resJson->assertStatus(422);
        $this->assertEquals(50, $business->fresh()->getInvoiceUsageCount());

        // UI displays limit reached state on /invoices and /invoices/create
        $this->get('/invoices')->assertSee('Invoice Limit Reached (50 / 50)');
        $this->get('/invoices/create')->assertSee('Invoice Limit Reached (50 / 50)');
    }

    public function test_bulk_upload_blocks_when_csv_exceeds_remaining_invoice_quota(): void
    {
        [$user, $business] = $this->makeTenantUser(50);
        $this->seedInvoicesForBusiness($business, $user, 49);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $today = now('Asia/Karachi')->format('Y-m-d');
        $csv = implode("\n", [
            'Invoice Reference,Invoice Date,Invoice Type,Payment Mode,Buyer Name,Buyer NTN,Buyer Province,Buyer Address,Buyer Type,HS Code,Product Description,UOM,Quantity,Unit Price,Tax Rate,Retail Price,Discount',
            "BULK-50,{$today},standard,Cash,Buyer 50,7654321,Punjab,Lahore,registered,0101.2100,Item A,NOS,1,1000,18%,0,0",
            "BULK-51,{$today},standard,Cash,Buyer 51,7654321,Punjab,Lahore,registered,0101.2100,Item B,NOS,1,1000,18%,0,0",
        ]);

        $file = UploadedFile::fake()->createWithContent('bulk.csv', $csv);

        $res = $this->post('/invoices/bulk-upload', [
            'csv_file' => $file,
        ]);

        $res->assertRedirect();
        $res->assertSessionHas('error');
        $this->assertStringContainsString('Bulk upload blocked', session('error'));
        $this->assertEquals(49, $business->fresh()->getInvoiceUsageCount());
    }

    public function test_cancelled_invoice_frees_quota_and_admin_limit_increase_unlocks_creation(): void
    {
        [$user, $business] = $this->makeTenantUser(2);
        $this->seedInvoicesForBusiness($business, $user, 2);

        $this->assertTrue($business->hasReachedInvoiceLimit());

        // Cancelling 1 invoice frees 1 slot
        $firstInvoice = $business->invoices()->first();
        $firstInvoice->update(['status' => 'cancelled']);

        $this->assertEquals(1, $business->fresh()->getInvoiceUsageCount());
        $this->assertFalse($business->fresh()->hasReachedInvoiceLimit());
        $this->assertEquals(1, $business->fresh()->getRemainingInvoicesCount());

        // Setting limit to 0 makes it unlimited
        $business->update(['invoice_limit' => 0]);
        $quota = $business->fresh()->getInvoiceQuotaSummary();
        $this->assertTrue($quota['unlimited']);
        $this->assertFalse($quota['reached']);
    }

    public function test_public_website_hides_subscriptions_by_default_and_shows_dynamic_invoice_limit(): void
    {
        // Migration sets show_pricing_plans = 0 and default_invoice_limit = 50
        $res = $this->get('/');
        $res->assertStatus(200);
        $res->assertDontSee('Simple, Predictable Plans');
        $res->assertSee('Up to 50 Invoices');

        // Changing global limit in System Settings dynamically updates the website
        SystemSetting::set('default_invoice_limit', '75', 'invoice', 'integer');
        $resUpdated = $this->get('/');
        $resUpdated->assertStatus(200);
        $resUpdated->assertSee('Up to 75 Invoices');
    }
}
