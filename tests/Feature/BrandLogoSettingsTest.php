<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Services\BrandLogoService;
use App\Services\Contracts\TenantServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BrandLogoSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_logo_page_is_super_admin_only(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin.logo@invoicego.pk',
            'password' => bcrypt('AdminPass123!'),
            'is_super_admin' => true,
            'admin_role' => 'super_admin',
            'status' => 'active',
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin.logo@invoicego.pk',
            'password' => bcrypt('AdminPass123!'),
            'is_super_admin' => false,
            'admin_role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin)
            ->get('/admin/brand-logo-settings')
            ->assertStatus(200)
            ->assertSee('Brand & Logo Settings')
            ->assertSee('Light / Transparent Logo')
            ->assertSee('Dark Mode Logo')
            ->assertSee('Save & Apply Logo Everywhere');

        $this->actingAs($admin)
            ->get('/admin/brand-logo-settings')
            ->assertStatus(403);
    }

    public function test_dynamic_logo_endpoints_and_transparent_vs_opaque_processing(): void
    {
        // Default endpoints serve valid PNGs
        $this->get('/brand/logo/light')->assertStatus(200)->assertHeader('Content-Type', 'image/png');
        $this->get('/brand/logo/dark')->assertStatus(200)->assertHeader('Content-Type', 'image/png');

        // Create a temporary white-background logo with black + blue rectangles
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_logo_' . Str::random(6) . '.png';
        $im = imagecreatetruecolor(120, 40);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 10, 10, 10);
        $blue  = imagecolorallocate($im, 10, 90, 245);
        imagefilledrectangle($im, 0, 0, 119, 39, $white);
        imagefilledrectangle($im, 15, 10, 55, 30, $black);
        imagefilledrectangle($im, 65, 10, 105, 30, $blue);
        imagepng($im, $tmpFile);
        imagedestroy($im);

        $service = app(BrandLogoService::class);

        try {
            // 1. Transparent mode + Auto Dark mode
            $service->saveUploadedLogos($tmpFile, null, true, true);
            $this->assertTrue(BrandLogoService::hasCustomLogo());
            $this->assertTrue(BrandLogoService::isTransparentEnabled());

            $lightResp = $this->get('/brand/logo/light');
            $lightResp->assertStatus(200);
            $lightGd = imagecreatefromstring($lightResp->getContent());
            $cornerAlpha = (imagecolorat($lightGd, 0, 0) >> 24) & 0x7F;
            $this->assertGreaterThan(120, $cornerAlpha, 'Corner pixel should be transparent when makeTransparent=true');
            imagedestroy($lightGd);

            $darkResp = $this->get('/brand/logo/dark');
            $darkResp->assertStatus(200);
            $darkGd = imagecreatefromstring($darkResp->getContent());
            $darkCornerAlpha = (imagecolorat($darkGd, 0, 0) >> 24) & 0x7F;
            $this->assertGreaterThan(120, $darkCornerAlpha, 'Dark logo corner should be transparent');
            imagedestroy($darkGd);

            // 2. Keep original background mode (makeTransparent = false)
            $service->saveUploadedLogos($tmpFile, null, false, false);
            $this->assertFalse(BrandLogoService::isTransparentEnabled());
            $opaqueResp = $this->get('/brand/logo/light');
            $opaqueResp->assertStatus(200);
            $opaqueGd = imagecreatefromstring($opaqueResp->getContent());
            $opaqueCornerAlpha = (imagecolorat($opaqueGd, 0, 0) >> 24) & 0x7F;
            $this->assertEquals(0, $opaqueCornerAlpha, 'Corner pixel should stay opaque when makeTransparent=false');
            imagedestroy($opaqueGd);

            // 3. Reset to default restores clean state
            $service->resetToDefault();
            $this->assertFalse(BrandLogoService::hasCustomLogo());
            $this->get('/brand/logo/light')->assertStatus(200);
        } finally {
            @unlink($tmpFile);
            $service->resetToDefault();
        }
    }

    public function test_website_auth_and_tenant_views_use_dynamic_brand_logo_urls(): void
    {
        // Default mode serves fast Edge CDN static paths
        $this->get('/')->assertStatus(200)
            ->assertSee('/images/invoicego-logo.png')
            ->assertSee('/images/invoicego-logo-dark.png');

        // When custom logo is enabled, all views switch dynamically to /brand/logo/*
        \App\Models\SystemSetting::set(BrandLogoService::KEY_HAS_CUSTOM, '1', 'general', 'boolean');

        $this->get('/')->assertStatus(200)
            ->assertSee('/brand/logo/light')
            ->assertSee('/brand/logo/dark');

        $this->get('/login')->assertStatus(200)->assertSee('/brand/logo/light');
        $this->get('/register')->assertStatus(200)->assertSee('/brand/logo/light');

        $user = User::factory()->create();
        $business = Business::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Logo Test Biz',
            'ntn_cnic' => '1234567',
            'address' => 'Lahore',
            'province' => 'Punjab',
            'sector' => 'All Other Sectors',
            'business_natures' => ['Distributor'],
            'status' => 'approved',
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner', 'is_default' => true]);

        $this->actingAs($user);
        app(TenantServiceInterface::class)->setActiveBusiness($business);

        $this->get('/dashboard')->assertStatus(200)->assertSee('/brand/logo/light');
    }
}
