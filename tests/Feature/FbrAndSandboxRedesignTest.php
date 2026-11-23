<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\SandboxScenario;
use App\Models\User;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FbrAndSandboxRedesignTest extends TestCase
{
    use RefreshDatabase;

    protected function createBusiness(): Business
    {
        return Business::create([
            'name' => 'Test Business',
            'ntn_cnic' => '1234125',
            'strn' => '9988776655',
            'province' => 'Punjab',
            'address' => 'Test Address',
            'sector' => 'All Other Sectors',
            'business_natures' => ['Manufacturer'],
            'status' => 'approved',
            'current_environment' => 'sandbox',
            'production_submissions_enabled' => false,
            'fbr_sandbox_url' => 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata_sb',
            'fbr_sandbox_validation_url' => 'https://gw.fbr.gov.pk/di_data/v1/di/validatedata_sb',
            'fbr_sandbox_token' => 'test-sandbox-token',
        ]);
    }

    public function test_sandbox_scenarios_screen_renders_with_redesigned_layout_and_json_tab(): void
    {
        $user = User::factory()->create();
        $business = $this->createBusiness();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $scenario = SandboxScenario::create([
            'scenario_code' => 'SN001',
            'title' => 'Standard Rate Sale to Registered Buyer',
            'description' => 'Test scenario 1',
            'payload_template' => [
                'InvoiceType' => 'Standard',
                'InvoiceDate' => '2026-11-23',
                'SellerNTN' => '1234125',
                'BuyerNTN' => '7654321',
                'BuyerName' => 'Alpha Corp',
                'Items' => [
                    [
                        'ItemCode' => 'ITEM01',
                        'ItemDescription' => 'Test Item',
                        'Quantity' => 1,
                        'SaleValue' => 1000,
                        'TaxRate' => 18,
                        'SalesTax' => 180,
                    ]
                ]
            ],
            'expected_outcome' => 'success',
            'is_mandatory' => true,
        ]);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $response = $this->get(route('tenant.sandbox-scenarios'));

        $response->assertStatus(200);
        $response->assertSee('Sandbox scenarios');
        $response->assertSee('SN001');
        $response->assertSee('Reset to template');
        $response->assertSee('Form');
        $response->assertSee('JSON');
        $response->assertSee('Check schema & preview request');
        $response->assertSee('Validate with FBR sandbox');
        $response->assertSee('Validate & submit to sandbox');
        $response->assertSee('Attempt history');
    }

    public function test_sandbox_scenario_can_be_validated_with_custom_json_payload(): void
    {
        Http::fake([
            'https://gw.fbr.gov.pk/*' => Http::response([
                'Status' => 'Valid',
                'Message' => 'Invoice payload conforms to FBR schema',
            ], 200),
        ]);

        $user = User::factory()->create();
        $business = $this->createBusiness();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $scenario = SandboxScenario::create([
            'scenario_code' => 'SN001',
            'title' => 'Standard Rate Sale to Registered Buyer',
            'description' => 'Test scenario 1',
            'payload_template' => ['InvoiceType' => 'Standard'],
            'expected_outcome' => 'success',
            'is_mandatory' => true,
        ]);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $customPayload = [
            'InvoiceType' => 'Standard',
            'SellerNTN' => '1234125',
            'BuyerNTN' => '7654321',
            'CustomField' => 'EditedInJsonTab',
        ];

        $response = $this->postJson(route('tenant.sandbox-scenarios.validate', $scenario->id), [
            'custom_payload' => json_encode($customPayload),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonPath('details.scenario', 'SN001');
    }

    public function test_fbr_integration_screen_renders_with_exact_layout(): void
    {
        $user = User::factory()->create();
        $business = $this->createBusiness();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $response = $this->get(route('tenant.fbr-integration'));

        $response->assertStatus(200);
        $response->assertSee('FBR integration');
        $response->assertSee('SANDBOX');
        $response->assertSee('Sandbox credentials');
        $response->assertSee('Sandbox URL (post)');
        $response->assertSee('Sandbox validation URL');
        $response->assertSee('Sandbox token / API key');
        $response->assertSee('PRODUCTION');
        $response->assertSee('Production credentials');
        $response->assertSee('Production URL (post)');
        $response->assertSee('Production validation URL');
        $response->assertSee('Production token / API key');
        $response->assertSee('Production submission');
        $response->assertSee('Enable production');
        $response->assertSee('Recent FBR calls');
    }

    public function test_fbr_sandbox_credentials_can_be_saved_and_verified(): void
    {
        Http::fake([
            'https://gw.fbr.gov.pk/*' => Http::response(['status' => 'OK'], 200),
        ]);

        $user = User::factory()->create();
        $business = $this->createBusiness();
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $response = $this->post(route('tenant.fbr-integration.sandbox.save'), [
            'fbr_sandbox_url' => 'https://gw.fbr.gov.pk/di_data/v1/di/postinvoicedata_sb',
            'fbr_sandbox_validation_url' => 'https://gw.fbr.gov.pk/di_data/v1/di/validatedata_sb',
            'fbr_sandbox_token' => 'updated-sb-token-123',
        ]);

        $response->assertRedirect(route('tenant.fbr-integration'));
        $business->refresh();
        $this->assertEquals('updated-sb-token-123', $business->fbr_sandbox_token);

        // Verify Sandbox connection
        $verifyResponse = $this->post(route('tenant.fbr-integration.sandbox.verify'));
        $verifyResponse->assertRedirect(route('tenant.fbr-integration'));
        $business->refresh();
        $this->assertEquals('connected', $business->sandbox_connection_status);
        $this->assertNotNull($business->sandbox_verified_at);
    }

    public function test_fbr_production_submission_toggle(): void
    {
        $user = User::factory()->create();
        $business = $this->createBusiness();
        $business->update([
            'production_submissions_enabled' => false,
            'fbr_production_token' => 'prod-token',
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $response = $this->post(route('tenant.fbr-integration.production.toggle'), [
            'enable' => 1,
        ]);

        $response->assertRedirect(route('tenant.fbr-integration'));
        $business->refresh();
        $this->assertTrue((bool)$business->production_submissions_enabled);
        $this->assertEquals('production', $business->current_environment);

        // Disable
        $this->post(route('tenant.fbr-integration.production.toggle'), [
            'enable' => 0,
        ]);
        $business->refresh();
        $this->assertFalse((bool)$business->production_submissions_enabled);
        $this->assertEquals('sandbox', $business->current_environment);
    }
}
