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
        $response->assertSee('Smart Digital Invoicing.');
        $response->assertSee('Simplified');
        $response->assertSee('Built for Pakistani Businesses');
        $response->assertSee('Everything You Need for Smarter Invoicing');
        $response->assertSee('Get Started in Three Simple Steps');
        $response->assertSee('Your Entire Invoicing Business in One Place');
        $response->assertSee('Why Businesses Choose InvoiceGO');
        $response->assertSee('Frequently Asked Questions');
        $response->assertSee('Ready to Simplify Your Digital Invoicing?');
    }

    public function test_screen_2_signin_page_renders_with_split_purple_layout(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('InvoiceGO');
        $response->assertSee('Digital Invoicing Simplified for Pakistan');
        $response->assertSee('Sign In');
        $response->assertSee('Enter your login credentials provided by admin');
        $response->assertSee('Business Email');
        $response->assertSee('Forgot password?');
        $response->assertDontSee('Create Account');
        $response->assertSee('Sign In to Dashboard');
    }

    public function test_registration_toggle_setting_dynamically_controls_website_and_login_view(): void
    {
        // 1. When disabled (default)
        \App\Models\SystemSetting::set('enable_public_registration', '0', 'general', 'boolean');
        $resDisabled = $this->get('/login');
        $resDisabled->assertSee('Enter your login credentials provided by admin');
        $resDisabled->assertDontSee('Create Account');

        // 2. When enabled by Admin
        \App\Models\SystemSetting::set('enable_public_registration', '1', 'general', 'boolean');
        $resEnabled = $this->get('/login');
        $resEnabled->assertSee('Create Account');
        $resEnabled->assertSee('Sign In to Your Workspace');
        $resEnabled->assertSee('Enter your credentials to access InvoiceGO');
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

    public function test_pricing_section_visibility_can_be_toggled_from_system_setting(): void
    {
        // 1. When pricing is enabled
        \App\Models\SystemSetting::set('show_pricing_plans', '1', 'general', 'boolean');
        $res = $this->get('/');
        $res->assertSee('Simple, Predictable Plans');
        $res->assertSee('Pricing');

        // 2. When pricing is disabled
        \App\Models\SystemSetting::set('show_pricing_plans', '0', 'general', 'boolean');
        $resHidden = $this->get('/');
        $resHidden->assertDontSee('Simple, Predictable Plans');
    }
}
