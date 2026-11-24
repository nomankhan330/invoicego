<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PricingPlan;
use App\Models\User;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class UserAndInvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $supportStaff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@invoicego.pk',
            'phone' => '03001112233',
            'password' => Hash::make('AdminPass123!'),
            'is_super_admin' => true,
            'admin_role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->supportStaff = User::create([
            'name' => 'Support Agent',
            'email' => 'support@invoicego.pk',
            'phone' => '03004445566',
            'password' => Hash::make('SupportPass123!'),
            'is_super_admin' => false,
            'admin_role' => 'support_staff',
            'status' => 'active',
        ]);
    }

    public function test_admin_user_management_dashboard_create_view_and_login_flow(): void
    {
        $plan = PricingPlan::create([
            'name' => 'Professional',
            'slug' => 'professional',
            'price' => 'PKR 5,000',
            'billing_period' => '/month',
            'features' => ['All Features'],
            'button_text' => 'Start',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($this->superAdmin);

        // 1. User Management list renders 5 stats cards + Create New User button
        $listRes = $this->get('/admin/users');
        $listRes->assertStatus(200);
        $listRes->assertSee('Total Users');
        $listRes->assertSee('Active Users');
        $listRes->assertSee('Inactive Users');
        $listRes->assertSee('Suspended Users');
        $listRes->assertSee('Newly Created Users');
        $listRes->assertSee('+ Create New User');

        // 2. Create New User with Business Info + Owner Info + Account Info + FBR Config
        Livewire::test(CreateUser::class)
            ->fillForm([
                'business_name' => 'Lahore Steel Works',
                'business_type' => 'Company',
                'business_ntn' => '5566778',
                'business_strn' => '3277876111222',
                'business_city' => 'Lahore',
                'business_province' => 'Punjab',
                'business_address' => 'Plot 99, Industrial Zone, Lahore',
                'name' => 'Kamran Malik',
                'cnic' => '35201-5566778-1',
                'email' => 'kamran@lahoresteel.pk',
                'phone' => '03215566778',
                'username' => 'kamran.steel',
                'status' => 'active',
                'password' => 'ClientPass123!',
                'password_confirmation' => 'ClientPass123!',
                'subscription_plan_id' => $plan->id,
                'fbr_registration_status' => 'Registered',
                'fbr_environment' => 'sandbox',
                'fbr_pos_id' => 'POS-9901',
                'fbr_integration_status' => 'Sandbox Testing',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $createdUser = User::where('email', 'kamran@lahoresteel.pk')->first();
        $this->assertNotNull($createdUser);
        $this->assertStringStartsWith('USR-', $createdUser->user_code);
        $this->assertEquals('kamran.steel', $createdUser->username);

        $linkedBiz = $createdUser->businesses()->first();
        $this->assertNotNull($linkedBiz);
        $this->assertEquals('Lahore Steel Works', $linkedBiz->name);
        $this->assertEquals('Company', $linkedBiz->business_type);
        $this->assertEquals('Lahore', $linkedBiz->city);
        $this->assertEquals('POS-9901', $linkedBiz->fbr_pos_id);
        $this->assertEquals('Professional', $linkedBiz->subscriptions()->first()?->plan_name);

        // 3. Dedicated User Details Page renders complete profile
        $viewRes = $this->get("/admin/users/{$createdUser->id}");
        $viewRes->assertStatus(200);
        $viewRes->assertSee('Invoice & FBR Summary');
        $viewRes->assertSee('Lahore Steel Works');
        $viewRes->assertSee('Kamran Malik');
        $viewRes->assertSee('POS-9901');
        $viewRes->assertSee('Account Activity History');

        // 4. Newly created user can log in and last_login_at is recorded
        auth()->logout();
        $loginRes = $this->post('/login', [
            'email' => 'kamran@lahoresteel.pk',
            'password' => 'ClientPass123!',
        ]);
        $loginRes->assertRedirect(route('tenant.dashboard'));
        $createdUser->refresh();
        $this->assertNotNull($createdUser->last_login_at);
    }

    public function test_inactive_user_cannot_login_and_support_staff_rbac_is_enforced(): void
    {
        $inactiveUser = User::create([
            'name' => 'Draft Client',
            'email' => 'draft@client.pk',
            'password' => Hash::make('Password123!'),
            'status' => 'inactive',
            'admin_role' => 'user',
        ]);

        // Inactive account login must be blocked
        $res = $this->post('/login', [
            'email' => 'draft@client.pk',
            'password' => 'Password123!',
        ]);
        $res->assertSessionHasErrors('email');
        $this->assertGuest();

        // Support staff can access /admin/users view-only, cannot access /admin/users/create
        $this->actingAs($this->supportStaff);
        $this->get('/admin/users')->assertStatus(200);
        $this->get("/admin/users/{$inactiveUser->id}")->assertStatus(200);
        $this->get('/admin/users/create')->assertStatus(403);
    }

    public function test_admin_and_tenant_invoice_management_dashboards_and_analytics(): void
    {
        $owner = User::create([
            'name' => 'Noman Owner',
            'email' => 'owner@biz.pk',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);

        $business = Business::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Karachi Traders',
            'ntn_cnic' => '7788991',
            'address' => 'Clifton, Karachi',
            'province' => 'Sindh',
            'sector' => 'All Other Sectors',
            'business_natures' => ['Distributor'],
            'status' => 'approved',
            'current_environment' => 'sandbox',
        ]);
        $owner->businesses()->attach($business->id, ['role' => 'owner', 'is_default' => true]);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Delta Buyer Ltd',
            'ntn_cnic' => '1122334',
            'province' => 'Sindh',
            'address' => 'Karachi',
            'registration_type' => 'registered',
        ]);

        $acceptedInvoice = Invoice::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2026-1001',
            'invoice_type' => 'standard',
            'environment' => 'sandbox',
            'invoice_date' => now('Asia/Karachi')->toDateString(),
            'buyer_name' => 'Delta Buyer Ltd',
            'buyer_ntn_cnic' => '1122334',
            'status' => 'accepted',
            'sales_value_excl_tax' => 10000,
            'sales_tax_amount' => 1800,
            'total_invoice_value' => 11800,
            'fbr_invoice_number' => '7788991DI1001',
            'fbr_submitted_at' => now(),
            'created_by' => $owner->id,
        ]);

        $draftInvoice = Invoice::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2026-1002',
            'invoice_type' => 'standard',
            'environment' => 'sandbox',
            'invoice_date' => now('Asia/Karachi')->toDateString(),
            'buyer_name' => 'Delta Buyer Ltd',
            'buyer_ntn_cnic' => '1122334',
            'status' => 'draft',
            'sales_value_excl_tax' => 5000,
            'sales_tax_amount' => 900,
            'total_invoice_value' => 5900,
            'created_by' => $owner->id,
        ]);

        // 1. Admin /admin/invoices renders 8 KPI cards, table, and Business-Wise Analytics page
        $this->actingAs($this->superAdmin);
        $adminInvRes = $this->get('/admin/invoices');
        $adminInvRes->assertStatus(200);
        $adminInvRes->assertSee('Total Invoices');
        $adminInvRes->assertSee('Total Sales Tax Collected');
        $adminInvRes->assertSee('INV-2026-1001');
        $adminInvRes->assertSee('Business-Wise & Analytics');

        $analyticsRes = $this->get('/admin/invoices/business-analytics');
        $analyticsRes->assertStatus(200);
        $analyticsRes->assertSee('Business-Wise Invoice Report');
        $analyticsRes->assertSee('Karachi Traders');

        // 2. Tenant User /invoices renders 8 KPI cards, analytics, filters, and cancel action
        $this->actingAs($owner);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $tenantRes = $this->get(route('tenant.invoices.index', ['sort' => 'highest_amount']));
        $tenantRes->assertStatus(200);
        $tenantRes->assertSee('Invoice Management');
        $tenantRes->assertSee('Total Sales Tax Collected');
        $tenantRes->assertSee('INV-2026-1001');
        $tenantRes->assertSee('INV-2026-1002');

        // Cancel draft invoice
        $cancelRes = $this->post(route('tenant.invoices.cancel', $draftInvoice->uuid));
        $cancelRes->assertSessionHas('success');
        $draftInvoice->refresh();
        $this->assertEquals('cancelled', $draftInvoice->status);
    }
}
