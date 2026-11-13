<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_cannot_access_another_tenants_data(): void
    {
        // 1. Create Tenant A
        $userA = User::factory()->create(['name' => 'Owner A', 'email' => 'owner_a@example.com']);
        $businessA = Business::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Business Alpha',
            'ntn_cnic' => '1111111',
            'address' => 'Alpha Street, Lahore',
            'province' => 'Punjab',
            'sector' => 'All Other Sectors',
            'business_natures' => ['Manufacturer'],
            'status' => 'approved',
        ]);
        $userA->businesses()->attach($businessA->id, ['role' => 'owner']);

        // 2. Create Tenant B
        $userB = User::factory()->create(['name' => 'Owner B', 'email' => 'owner_b@example.com']);
        $businessB = Business::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Business Beta',
            'ntn_cnic' => '2222222',
            'address' => 'Beta Boulevard, Karachi',
            'province' => 'Sindh',
            'sector' => 'Textile',
            'business_natures' => ['Exporter'],
            'status' => 'approved',
        ]);
        $userB->businesses()->attach($businessB->id, ['role' => 'owner']);

        // 3. Create customer and invoice belonging to Tenant A
        $customerA = Customer::create([
            'business_id' => $businessA->id,
            'name' => 'Customer of Alpha',
            'ntn_cnic' => '7777777',
            'registration_type' => 'registered',
        ]);

        $invoiceA = Invoice::create([
            'uuid' => (string) Str::uuid(),
            'business_id' => $businessA->id,
            'customer_id' => $customerA->id,
            'invoice_number' => 'INV-ALPHA-001',
            'invoice_date' => now()->format('Y-m-d'),
            'status' => 'draft',
            'created_by' => $userA->id,
        ]);

        // 4. Act as User B with Business B active
        $this->actingAs($userB);
        app(TenantServiceInterface::class)->setActiveBusiness($businessB);

        // Assert Eloquent queries for Customer and Invoice automatically isolate to Business B
        $customersVisibleToB = Customer::all();
        $this->assertFalse($customersVisibleToB->contains('id', $customerA->id));

        $invoicesVisibleToB = Invoice::all();
        $this->assertFalse($invoicesVisibleToB->contains('id', $invoiceA->id));

        // Attempting to directly view Invoice A via HTTP route must fail with 404 (not found in Tenant B scope)
        $response = $this->get(route('tenant.invoices.show', $invoiceA->uuid));
        $response->assertStatus(404);
    }
}
