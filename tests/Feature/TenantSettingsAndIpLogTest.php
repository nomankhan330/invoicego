<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\User;
use App\Services\NotificationAlertService;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TenantSettingsAndIpLogTest extends TestCase
{
    use RefreshDatabase;

    private function createAuthenticatedTenantUser(): array
    {
        $user = User::factory()->create([
            'name' => 'Noman Business Owner',
            'email' => 'owner.settings@invoicego.test',
            'phone' => '03001234567',
            'cnic' => '42101-1234567-1',
            'password' => Hash::make('OldPassword@123'),
            'status' => 'active',
            'email_notifications_enabled' => true,
        ]);

        $business = Business::create([
            'name' => 'OC1 Enterprises',
            'ntn_cnic' => '1234567',
            'strn' => '3277876123456',
            'address' => 'Plot 12, Industrial Estate',
            'city' => 'Lahore',
            'province' => 'Punjab',
            'sector' => 'Steel, Services',
            'business_natures' => ['Manufacturer', 'Wholesaler'],
            'is_retailer_registered' => false,
            'status' => 'approved',
            'current_environment' => 'sandbox',
            'approved_at' => now(),
        ]);

        $business->users()->attach($user->id, ['role' => 'owner']);

        return [$user, $business];
    }

    public function test_settings_page_renders_two_tabs_sidebar_logout_and_multi_checkbox_sectors(): void
    {
        [$user, $business] = $this->createAuthenticatedTenantUser();

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->withHeaders(['X-Vercel-Forwarded-For' => '110.93.195.255'])
            ->get(route('tenant.settings'));

        $response->assertOk();
        // 2 tabs only (NO Team tab)
        $response->assertSee('Business profile');
        $response->assertSee('Account &amp; security', false);
        $response->assertDontSee('>Team<', false);
        $response->assertDontSee('Registered as a retailer');

        // Sidebar logout right below Settings
        $response->assertSee('<span>Logout</span>', false);

        // Two-factor authentication & Email notifications cards
        $response->assertSee('Two-factor authentication');
        $response->assertSee('Set up two-factor authentication');
        $response->assertSee('Email notifications');
        $response->assertSee('Email me about rejected or uncertain FBR submissions');

        // Multi-checkbox Sector options from FBR IRIS
        $response->assertSee('name="sector[]"', false);
        $response->assertSee('Potassium Chlorate');
        $response->assertSee('Cement or Concrete Blocks');
        $response->assertSee('Wholesale / Retails');
        $response->assertSee('All Other Sectors');

        // Detected Public IP displayed in Account & Security tab
        $response->assertSee('Current Public IP: 110.93.195.255');
    }

    public function test_business_profile_update_saves_multiple_sectors_and_records_public_ip_in_activity_log(): void
    {
        [$user, $business] = $this->createAuthenticatedTenantUser();

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->withHeaders(['X-Forwarded-For' => '10.0.0.5, 203.99.184.42, 172.16.0.2'])
            ->post(route('tenant.settings.update'), [
                'profile_form' => '1',
                'name' => 'OC1 Updated Trading Co',
                'strn' => '3277876999999',
                'address' => 'Suite 404, Shahrah-e-Faisal',
                'city' => 'Karachi',
                'province' => 'Sindh',
                'phone' => '03112233445',
                'business_natures' => ['Importer', 'Distributor', 'Service Provider'],
                'sector' => ['Services', 'Pharmaceuticals', 'Textile'],
            ]);

        $response->assertRedirect(route('tenant.settings'));
        $response->assertSessionHas('success');

        $business->refresh();
        $user->refresh();

        $this->assertSame('OC1 Updated Trading Co', $business->name);
        $this->assertSame('3277876999999', $business->strn);
        $this->assertSame('Karachi', $business->city);
        $this->assertSame('Sindh', $business->province);
        $this->assertSame('Services, Pharmaceuticals, Textile', $business->sector);
        $this->assertSame(['Importer', 'Distributor', 'Service Provider'], $business->business_natures);
        $this->assertSame('03112233445', $user->phone);

        // Verify Public IP captured cleanly in activity_logs
        $log = ActivityLog::withoutGlobalScopes()
            ->where('business_id', $business->id)
            ->where('action', 'business_profile_updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('203.99.184.42', $log->ip_address);
        $this->assertSame('203.99.184.42', $log->properties['public_ip'] ?? null);
    }

    public function test_account_security_update_changes_password_and_displays_public_ip_history(): void
    {
        [$user, $business] = $this->createAuthenticatedTenantUser();

        $response = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->withHeaders(['CF-Connecting-IP' => '182.185.44.91'])
            ->post(route('tenant.settings.account'), [
                'owner_name' => 'Noman Updated Owner',
                'owner_phone' => '03219876543',
                'owner_cnic' => '42101-7654321-9',
                'current_password' => 'OldPassword@123',
                'new_password' => 'NewSecure@456',
                'new_password_confirmation' => 'NewSecure@456',
            ]);

        $response->assertRedirect(route('tenant.settings', ['tab' => 'security']));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Noman Updated Owner', $user->name);
        $this->assertSame('03219876543', $user->phone);
        $this->assertTrue(Hash::check('NewSecure@456', $user->password));

        $log = ActivityLog::withoutGlobalScopes()
            ->where('business_id', $business->id)
            ->where('action', 'account_security_updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('182.185.44.91', $log->ip_address);

        // Reload settings page and confirm IP history row is rendered
        $page = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->get(route('tenant.settings', ['tab' => 'security']));

        $page->assertOk();
        $page->assertSee('182.185.44.91');
        $page->assertSee('ACCOUNT SECURITY UPDATED');
    }

    public function test_two_factor_authentication_full_lifecycle_and_login_challenge(): void
    {
        [$user, $business] = $this->createAuthenticatedTenantUser();
        $twoFactor = app(TwoFactorService::class);

        // 1. Start 2FA setup
        $setupRes = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->post(route('tenant.settings.two-factor.setup'));

        $setupRes->assertRedirect(route('tenant.settings', ['tab' => 'security']));
        $pendingSecret = session('two_factor:pending_secret');
        $this->assertNotEmpty($pendingSecret);

        // 2. Reject invalid 6-digit code
        $badConfirm = $this->actingAs($user)
            ->withSession([
                'active_business_id' => $business->id,
                'two_factor:pending_secret' => $pendingSecret,
            ])
            ->post(route('tenant.settings.two-factor.confirm'), [
                'totp_code' => '000000',
            ]);
        $badConfirm->assertSessionHasErrors('totp_code');
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());

        // 3. Confirm with valid TOTP code
        $validCode = $twoFactor->getCode($pendingSecret);
        $confirmRes = $this->actingAs($user)
            ->withSession([
                'active_business_id' => $business->id,
                'two_factor:pending_secret' => $pendingSecret,
            ])
            ->post(route('tenant.settings.two-factor.confirm'), [
                'totp_code' => $validCode,
            ]);

        $confirmRes->assertRedirect(route('tenant.settings', ['tab' => 'security']));
        $confirmRes->assertSessionHas('two_factor_recovery_codes_display');

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertCount(8, $user->two_factor_recovery_codes);
        $recoveryCode = $user->two_factor_recovery_codes[0];

        // 4. Logout and test login redirects to /two-factor-challenge
        $this->post(route('logout'));
        $this->assertGuest();

        $loginRes = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'OldPassword@123',
        ]);
        $loginRes->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();

        // 5. Complete login via single-use recovery code
        $challengeRes = $this->post(route('two-factor.verify'), [
            'code' => $recoveryCode,
        ]);
        $challengeRes->assertRedirect(route('tenant.dashboard'));
        $this->assertAuthenticatedAs($user);

        // Recovery code is consumed (7 left)
        $this->assertCount(7, $user->fresh()->two_factor_recovery_codes);

        // 6. Disable 2FA with password confirmation
        $disableRes = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->post(route('tenant.settings.two-factor.disable'), [
                'disable_password' => 'OldPassword@123',
            ]);

        $disableRes->assertRedirect(route('tenant.settings', ['tab' => 'security']));
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_email_notifications_toggle_persists_and_controls_fbr_issue_alerts(): void
    {
        [$user, $business] = $this->createAuthenticatedTenantUser();

        // Toggle OFF via AJAX JSON
        $offRes = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->postJson(route('tenant.settings.notifications'), [
                'email_notifications_enabled' => false,
            ]);

        $offRes->assertOk()->assertJson([
            'success' => true,
            'email_notifications_enabled' => false,
        ]);
        $this->assertFalse($user->fresh()->email_notifications_enabled);

        // Verify FBR alert service does NOT send email when disabled
        Mail::fake();
        $invoice = new Invoice([
            'business_id' => $business->id,
            'created_by' => $user->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date' => now()->format('Y-m-d'),
            'invoice_type' => 'standard',
            'buyer_name' => 'Buyer Co',
            'buyer_type' => 'registered',
            'buyer_province' => 'Punjab',
            'buyer_address' => 'Lahore',
            'environment' => 'sandbox',
            'status' => 'failed',
        ]);

        app(NotificationAlertService::class)->notifyFbrSubmissionIssue($business, $invoice, 'Test rejection');
        $this->assertSame(0, Mail::sent(\Illuminate\Mail\Mailable::class)->count());

        // Toggle ON via AJAX JSON
        $onRes = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->postJson(route('tenant.settings.notifications'), [
                'email_notifications_enabled' => true,
            ]);

        $onRes->assertOk()->assertJson([
            'success' => true,
            'email_notifications_enabled' => true,
        ]);
        $this->assertTrue($user->fresh()->email_notifications_enabled);
    }
}
