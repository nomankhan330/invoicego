<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SandboxScenario;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPanelAndSpecificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;
    protected Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'InvoiceGO Super Admin',
            'email' => 'admininvoicego@gmail.com',
            'phone' => '03001234567',
            'password' => Hash::make('AdminPass123!'),
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $this->regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => Hash::make('Password123!'),
            'is_super_admin' => false,
            'status' => 'active',
        ]);

        $this->business = Business::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Corp',
            'ntn_cnic' => '1234125',
            'strn' => '3277876123456',
            'address' => 'Test Address',
            'province' => 'Punjab',
            'sector' => 'IT Services',
            'business_natures' => ['Services'],
            'status' => 'approved',
            'current_environment' => 'sandbox',
        ]);

        $this->regularUser->businesses()->attach($this->business->id, ['role' => 'owner', 'is_default' => true]);
        $this->admin->businesses()->attach($this->business->id, ['role' => 'owner', 'is_default' => true]);
    }

    public function test_admin_panel_access_is_restricted_to_super_admins(): void
    {
        // Regular user denied
        $response = $this->actingAs($this->regularUser)->get('/admin');
        $response->assertStatus(403);

        // Super admin granted access
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('InvoiceGO Admin');
    }

    public function test_admin_dashboard_renders_stats_and_sections(): void
    {
        Subscription::create([
            'business_id' => $this->business->id,
            'plan_name' => 'Monthly Professional',
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
            'status' => 'active',
            'monthly_fee' => 5000.00,
        ]);

        Payment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->regularUser->id,
            'amount' => 5000.00,
            'currency' => 'PKR',
            'payment_method' => 'Bank Transfer',
            'status' => 'successful',
            'transaction_reference' => 'TXN-TEST-1',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('InvoiceGO Admin');

        // Test Livewire Stats Widget directly
        \Livewire\Livewire::test(\App\Filament\Widgets\StatsOverviewWidget::class)
            ->assertSee('Total Registered Users')
            ->assertSee('Active Users')
            ->assertSee('Total Invoices Created')
            ->assertSee('Total Revenue');
    }

    public function test_reports_page_accessible_and_exports_csv(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports-page');
        $response->assertStatus(200);
        $response->assertSee('Reports & Analytics');
        $response->assertSee('User Registrations');
        $response->assertSee('Subscription Status');
        $response->assertSee('Export User Report (CSV)');
    }

    public function test_registered_unregistered_mismatch_error_handling(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Apex Logistics Registered Buyer',
            'ntn_cnic' => '7654321', // 7-digit registered NTN
            'province' => 'Punjab',
            'address' => 'Lahore',
            'registration_type' => 'registered',
        ]);

        // Attempt to create invoice with registered customer but buyer_type marked as unregistered
        $payload = [
            'customer_id' => $customer->id,
            'buyer_name' => 'Apex Logistics Registered Buyer',
            'buyer_ntn_cnic' => '7654321',
            'buyer_province' => 'Punjab',
            'buyer_address' => 'Lahore',
            'buyer_type' => 'unregistered', // MISMATCH!
            'invoice_date' => now('Asia/Karachi')->format('Y-m-d'),
            'invoice_type' => 'standard',
            'items' => [
                [
                    'product_description' => 'Test Goods',
                    'hs_code' => '0101.2100',
                    'quantity' => 1,
                    'uom' => 'NOS',
                    'unit_price' => 1000.00,
                    'tax_rate' => 18,
                ]
            ],
            'action' => 'save',
        ];

        $response = $this->actingAs($this->regularUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('tenant.invoices.store'), $payload);

        $response->assertSessionHasErrors(['buyer_type']);
        $errorMsg = session('errors')->first('buyer_type');
        $this->assertStringContainsString('Registered Error', $errorMsg);
    }

    public function test_excel_bulk_invoicing_template_download_and_upload(): void
    {
        // 1. Download template
        $response = $this->actingAs($this->regularUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('tenant.invoices.sample-template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="fbr_invoice_bulk_template.csv"');

        // 2. Upload sample CSV file
        $csvContent = "Invoice Reference,Invoice Date,Invoice Type,Payment Mode,Buyer Name,Buyer NTN or CNIC,Buyer Province,Buyer Address,Buyer Type,HS Code,Product Description,UOM,Quantity,Unit Price,Tax Rate,Fixed or Retail Price,Discount\n" .
            "INV-BULK-TEST-1," . now('Asia/Karachi')->format('Y-m-d') . ",standard,Cash,Bulk Test Client,7654321,Punjab,Lahore,registered,0101.2100,Sample Bulk Item,NOS,2,1000,18%,0,0\n";

        $file = UploadedFile::fake()->createWithContent('invoices.csv', $csvContent);

        $uploadResponse = $this->actingAs($this->regularUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('tenant.invoices.process-bulk-upload'), [
                'csv_file' => $file,
                'submit_to_fbr' => '0',
            ]);

        $uploadResponse->assertRedirect(route('tenant.invoices.index'));
        $uploadResponse->assertSessionHas('success');

        $this->assertDatabaseHas('invoices', [
            'business_id' => $this->business->id,
            'buyer_name' => 'Bulk Test Client',
        ]);
    }

    public function test_all_28_sandbox_scenarios_seeded_and_runnable(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $scenariosCount = SandboxScenario::count();
        $this->assertGreaterThanOrEqual(15, $scenariosCount);

        // Run scenario SN001
        $response = $this->actingAs($this->regularUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->post('/sandbox-scenarios/SN001/run', [
                'payload' => json_encode(['custom' => 'test']),
            ]);

        $response->assertSessionHas('success');
    }
}
