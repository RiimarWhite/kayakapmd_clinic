<?php

namespace Tests\Feature;

use App\Models\AdminModel;
use App\Models\KayakapProfileModel;
use App\Models\ThemeModel;
use Database\Seeders\AdminSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Detailed Comment: Feature test suite for Theme Customization & Admin Settings integration.
 * Tests access permissions, API fetch/update/reset flows, logo upload, profile relation,
 * and dynamic CSS variable rendering.
 */
class AdminThemeSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(UserSeeder::class);

        // Ensure facility master profile exists for test environment
        if (!KayakapProfileModel::first()) {
            KayakapProfileModel::create([
                'clientcode'      => 'CLI001',
                'HOSP_NAME'       => 'Kayakap Medical Clinic',
                'TEL_NO'          => '09123456789',
                'EMAIL_ADD'       => 'info@kayakapmd.com',
                'DATE_REGISTERED' => now(),
            ]);
        }
    }

    /**
     * Detailed Comment: Verifies an authenticated admin can access the Theme Settings view.
     */
    public function test_admin_can_access_theme_settings_page(): void
    {
        $admin = AdminModel::first();

        $response = $this->actingAs($admin, 'admin')->get('/admin/settings/theme');

        $response->assertStatus(200);
        $response->assertSee('Theme & Branding Settings', false);
        $response->assertSee('Teal & Lime (2-Color Brand)', false);
    }

    /**
     * Detailed Comment: Verifies an unauthenticated user is redirected away from Theme Settings.
     */
    public function test_unauthenticated_user_redirected_from_theme_settings_page(): void
    {
        $response = $this->get('/admin/settings/theme');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * Detailed Comment: Verifies admin can fetch active theme settings via API.
     */
    public function test_admin_can_fetch_theme_settings_api(): void
    {
        $admin = AdminModel::first();

        $response = $this->actingAs($admin, 'admin')->postJson('/api/admin/theme/fetch');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'theme' => [
                'header_bg',
                'header_accent_color',
                'app_background',
                'footer_bg',
                'footer_text_color',
                'primary_button_bg',
            ],
        ]);
    }

    /**
     * Detailed Comment: Verifies admin can update theme settings to custom colors (including Teal & Lime palette).
     */
    public function test_admin_can_update_theme_settings(): void
    {
        $admin = AdminModel::first();

        $payload = [
            'theme_name'            => 'Teal & Lime Brand',
            'app_background'        => '#F8F9FA',
            'header_bg'             => '#027F9F',
            'header_text_color'     => '#FFFFFF',
            'header_accent_color'   => '#B2C10E',
            'footer_bg'             => '#F1F5F8',
            'footer_text_color'     => '#495057',
            'sidebar_bg'            => '#E9ECEF',
            'sidebar_text_color'    => '#212529',
            'primary_button_bg'     => '#027F9F',
            'primary_button_text'   => '#FFFFFF',
            'secondary_button_bg'   => '#B2C10E',
            'secondary_button_text' => '#212529',
            'text_color'            => '#212529',
        ];

        $response = $this->actingAs($admin, 'admin')->postJson('/api/admin/theme/update', $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('theme', [
            'header_bg'            => '#027F9F',
            'header_accent_color'  => '#B2C10E',
            'primary_button_bg'    => '#027F9F',
            'secondary_button_bg'  => '#B2C10E',
        ]);
    }

    /**
     * Detailed Comment: Verifies admin can upload a custom logo for the theme.
     */
    public function test_admin_can_upload_custom_logo(): void
    {
        $admin = AdminModel::first();
        $fakeLogo = UploadedFile::fake()->image('custom_clinic_logo.png', 120, 80);

        $payload = [
            'theme_name'            => 'Branded Theme',
            'app_background'        => '#F8F9FA',
            'header_bg'             => '#027F9F',
            'header_text_color'     => '#FFFFFF',
            'header_accent_color'   => '#B2C10E',
            'footer_bg'             => '#F1F5F8',
            'footer_text_color'     => '#495057',
            'sidebar_bg'            => '#E9ECEF',
            'sidebar_text_color'    => '#212529',
            'primary_button_bg'     => '#027F9F',
            'primary_button_text'   => '#FFFFFF',
            'secondary_button_bg'   => '#B2C10E',
            'secondary_button_text' => '#212529',
            'text_color'            => '#212529',
            'logo_file'             => $fakeLogo,
        ];

        $response = $this->actingAs($admin, 'admin')->post('/api/admin/theme/update', $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $theme = ThemeModel::first();
        $this->assertNotNull($theme);
        $this->assertStringContainsString('images/theme/clinic_custom_logo_', $theme->logo_path);

        // Detailed Comment: Cleanup uploaded test image artifact from public directory
        if ($theme->logo_path && File::exists(public_path($theme->logo_path))) {
            File::delete(public_path($theme->logo_path));
        }
    }

    /**
     * Detailed Comment: Verifies admin can reset theme settings back to factory default colors.
     */
    public function test_admin_can_reset_theme_to_defaults(): void
    {
        $admin = AdminModel::first();

        // Mutate first to non-default
        ThemeModel::create([
            'theme_name'          => 'Temporary Custom',
            'header_bg'           => '#000000',
            'header_accent_color' => '#111111',
            'is_active'           => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->postJson('/api/admin/theme/reset');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('theme', [
            'header_bg'           => '#f4c79f',
            'header_accent_color' => '#ffa500',
            'logo_path'           => 'images/logo.png',
        ]);
    }

    /**
     * Detailed Comment: Verifies ThemeModel and KayakapProfileModel relationships link cleanly via clientcode.
     */
    public function test_theme_links_to_kayakapmd_profile(): void
    {
        $profile = KayakapProfileModel::first();
        $this->assertNotNull($profile);

        $theme = ThemeModel::create([
            'clientcode'          => $profile->clientcode,
            'theme_name'          => 'Linked Profile Theme',
            'header_bg'           => '#027F9F',
            'header_accent_color' => '#B2C10E',
            'is_active'           => true,
        ]);

        $this->assertEquals($profile->clientcode, $theme->profile->clientcode);
        $this->assertEquals($theme->id, $profile->theme->id);
    }
}
