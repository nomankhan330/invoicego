<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationAndApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_pending_business_and_blocks_dashboard_until_approved(): void
    {
        $regData = [
            'name' => 'Usman Tariq',
            'phone' => '03001234567',
            'email' => 'usman.test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'business_name' => 'Tariq Enterprises',
            'ntn_cnic' => '7654321',
            'strn' => '3277876543210',
            'business_address' => 'Plot 99, Site Area, Karachi',
            'province' => 'Sindh',
            'sector' => 'Textile',
            'business_natures' => ['Manufacturer', 'Exporter'],
            'is_retailer_registered' => '1',
        ];

        $response = $this->post(route('register.post'), $regData);
        $response->assertRedirect(route('tenant.pending'));

        // Assert business was created with status = 'pending'
        $business = Business::where('ntn_cnic', '7654321')->first();
        $this->assertNotNull($business);
        $this->assertEquals('pending', $business->status);

        // Attempting to visit /dashboard while pending must redirect to /pending-approval
        $dashboardResponse = $this->get(route('tenant.dashboard'));
        $dashboardResponse->assertRedirect(route('tenant.pending'));

        // Simulate Super Admin approving the business
        $business->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // After approval, dashboard access must now succeed (HTTP 200)
        $approvedResponse = $this->get(route('tenant.dashboard'));
        $approvedResponse->assertStatus(200);
        $approvedResponse->assertSee('Tariq Enterprises');
    }
}
