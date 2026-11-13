<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ScreensRenderingTest extends TestCase
{
    use RefreshDatabase;
    public function test_screen_1_landing_page_renders_with_brand_and_onboarding(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('InvoiceGO');
        $response->assertSee('FBR DIGITAL INVOICING · DI API V1.12');
        $response->assertSee('Sales tax invoices for Pakistani businesses');
        $response->assertSee('From sign-up to live invoicing');
        $response->assertSee('What InvoiceGO does');
        $response->assertSee('Sandbox scenarios, built in');
        $response->assertSee('How access to FBR works');
    }

    public function test_screen_2_signin_page_renders_with_split_purple_layout(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('InvoiceGO');
        $response->assertSee('FBR Digital Invoicing,');
        $response->assertSee('done carefully.');
        $response->assertSee('Business account sign-in');
        $response->assertSee('Sign in');
        $response->assertSee('Forgot password?');
        $response->assertSee('Create account');
    }

    public function test_screen_3_register_page_renders_with_fbr_profile_fields(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Create your business account');
        $response->assertSee('Your details');
        $response->assertSee('Business profile');
        $response->assertSee('NTN / CNIC');
        $response->assertSee('STRN');
        $response->assertSee('Province');
        $response->assertSee('Sector');
        $response->assertSee('Business nature');
        $response->assertSee('Registered as a retailer in my sales tax profile');
        $response->assertSee('Create account');
    }

    public function test_screen_4_tenant_dashboard_renders_matching_screenshot_4(): void
    {
        $user = User::create([
            'name' => 'Noman',
            'email' => 'nomanoc1a@gmail.com',
            'phone' => '03001234567',
            'password' => bcrypt('password'),
        ]);

        $business = Business::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'OC1',
            'ntn_cnic' => '1234125',
            'address' => 'Plot 42, Industrial Area, Lahore',
            'province' => 'Punjab',
            'sector' => 'All Other Sectors',
            'business_natures' => ['Manufacturer', 'Retailer'],
            'is_retailer_registered' => true,
            'status' => 'approved',
            'current_environment' => 'sandbox',
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner', 'is_default' => true]);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // Header and PST clock
        $response->assertSee('Times shown in Pakistan Standard Time (Asia/Karachi)');
        $response->assertSee('Dashboard');
        $response->assertSee('Create invoice');

        // Sidebar
        $response->assertSee('OC1');
        $response->assertSee('Sandbox only');
        $response->assertSee('WORKSPACE');
        $response->assertSee('HS / PCT codes');
        $response->assertSee('Sandbox scenarios');
        $response->assertSee('FBR integration');

        // 8 KPI metric tiles
        $response->assertSee('TOTAL INVOICES (PRODUCTION)');
        $response->assertSee('0 sandbox test invoices kept separately');
        $response->assertSee('THIS MONTH');
        $response->assertSee('ACCEPTED BY FBR');
        $response->assertSee('FAILED SUBMISSIONS');
        $response->assertSee('DRAFTS');
        $response->assertSee('PENDING SUBMISSION');
        $response->assertSee('NEED RECONCILIATION');
        $response->assertSee('SCENARIOS PASSED');

        // Middle Row
        $response->assertSee('Production sales (accepted invoices)');
        $response->assertSee('Sales value (excl. ST)');
        $response->assertSee('Sales tax');
        $response->assertSee('Further tax');
        $response->assertSee('ST withheld');
        $response->assertSee('Gross invoice value');
        $response->assertSee('Daily, monthly, quarterly and yearly reports');

        // FBR Integration card
        $response->assertSee('FBR integration');
        $response->assertSee('Sandbox token');
        $response->assertSee('Production token');
        $response->assertSee('Production submission');

        // Bottom Row
        $response->assertSee('Recent invoices');
        $response->assertSee('Recent FBR activity');
        $response->assertSee('No invoices yet.');
        $response->assertSee('No FBR calls yet.');
    }
}
