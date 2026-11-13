<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\HsCode;
use App\Models\Product;
use App\Models\User;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerAndProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_crud_matching_screenshot_3_and_4(): void
    {
        $user = User::factory()->create(['name' => 'Owner', 'email' => 'owner@test.com']);
        $business = Business::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Corp',
            'ntn_cnic' => '1234567',
            'address' => 'Mall 1, Lahore',
            'province' => 'PUNJAB',
            'sector' => 'All Other Sectors',
            'business_natures' => ['Retailer'],
            'status' => 'approved',
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        // 1. Assert initial customer index page shows empty state matching Screenshot 4
        $indexResponse = $this->get(route('tenant.customers.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSeeText('0 registered · 0 unregistered buyers');
        $indexResponse->assertSee('No customers yet');
        $indexResponse->assertSee('Add buyers once and reuse them on invoices.');

        // 2. Create customer via POST (Matching Screenshot 3 fields)
        $customerData = [
            'name' => 'Al-Rehman Traders',
            'registration_type' => 'registered',
            'ntn_cnic' => '7654321',
            'strn' => '3277876543210',
            'province' => 'PUNJAB',
            'category' => 'Wholesale',
            'address' => 'Shop 45, Shah Alam Market, Lahore',
            'phone' => '03001234567',
            'email' => 'contact@alrehman.pk',
        ];

        $storeResponse = $this->post(route('tenant.customers.store'), $customerData);
        $storeResponse->assertRedirect(route('tenant.customers.index'));

        $customer = Customer::where('name', 'Al-Rehman Traders')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Wholesale', $customer->category);
        $this->assertEquals('registered', $customer->registration_type);
        $this->assertEquals('7654321', $customer->ntn_cnic);

        // 3. Edit customer via PUT
        $updateData = $customerData;
        $updateData['name'] = 'Al-Rehman Wholesale Ltd';
        $updateData['category'] = 'Corporate';

        $updateResponse = $this->put(route('tenant.customers.update', $customer->id), $updateData);
        $updateResponse->assertRedirect(route('tenant.customers.index'));

        $customer->refresh();
        $this->assertEquals('Al-Rehman Wholesale Ltd', $customer->name);
        $this->assertEquals('Corporate', $customer->category);

        // 4. Verify index now displays 1 registered buyer
        $indexAfterResponse = $this->get(route('tenant.customers.index'));
        $indexAfterResponse->assertSeeText('1 registered · 0 unregistered buyers');
        $indexAfterResponse->assertSee('Al-Rehman Wholesale Ltd');

        // 5. Delete customer
        $deleteResponse = $this->delete(route('tenant.customers.destroy', $customer->id));
        $deleteResponse->assertRedirect(route('tenant.customers.index'));
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_product_crud_matching_screenshot_1_and_2(): void
    {
        $user = User::factory()->create(['name' => 'Owner', 'email' => 'owner2@test.com']);
        $business = Business::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Tech Supplies',
            'ntn_cnic' => '7654321',
            'address' => 'Blue Area, Islamabad',
            'province' => 'ISLAMABAD CAPITAL TERRITORY',
            'sector' => 'Electronics',
            'business_natures' => ['Distributor'],
            'status' => 'approved',
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        // 1. Assert initial products index shows empty state matching Screenshot 2
        $indexResponse = $this->get(route('tenant.products.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Saved products autofill invoice items.');
        $indexResponse->assertSee('No products yet');
        $indexResponse->assertSee('Add products with their HS code, UOM and sale type to speed up invoicing.');

        // 2. Create product via POST (Matching Screenshot 1 fields)
        $productData = [
            'sku' => 'LAP-DELL-5530',
            'unit_price' => 250000.00,
            'description' => 'Dell Latitude 5530 Business Laptop',
            'hs_code' => '8471.30.00',
            'uom' => 'NOS',
            'sale_type' => 'Goods (Standard)',
            'default_tax_rate' => 18.00,
            'sro_schedule_no' => 'Fifth Schedule',
            'sro_item_serial' => '12-A',
            'retail_price' => 260000.00,
        ];

        $storeResponse = $this->post(route('tenant.products.store'), $productData);
        $storeResponse->assertRedirect(route('tenant.products.index'));

        $product = Product::where('sku', 'LAP-DELL-5530')->first();
        $this->assertNotNull($product);
        $this->assertEquals('Dell Latitude 5530 Business Laptop', $product->description);
        $this->assertEquals('8471.30.00', $product->hs_code);
        $this->assertEquals('Goods (Standard)', $product->sale_type);
        $this->assertEquals('Fifth Schedule', $product->sro_schedule_no);
        $this->assertEquals('12-A', $product->sro_item_serial);
        $this->assertEquals(260000.00, $product->retail_price);

        // 3. Edit product via PUT
        $updateData = $productData;
        $updateData['unit_price'] = 245000.00;
        $updateData['description'] = 'Dell Latitude 5530 Business Laptop (Upgraded)';

        $updateResponse = $this->put(route('tenant.products.update', $product->id), $updateData);
        $updateResponse->assertRedirect(route('tenant.products.index'));

        $product->refresh();
        $this->assertEquals(245000.00, $product->unit_price);
        $this->assertEquals('Dell Latitude 5530 Business Laptop (Upgraded)', $product->description);

        // 4. Verify index now lists the product
        $indexAfterResponse = $this->get(route('tenant.products.index'));
        $indexAfterResponse->assertSee('LAP-DELL-5530');
        $indexAfterResponse->assertSee('245,000.00');

        // 5. Delete product
        $deleteResponse = $this->delete(route('tenant.products.destroy', $product->id));
        $deleteResponse->assertRedirect(route('tenant.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
