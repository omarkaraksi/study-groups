<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RtlLtrTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AdminPermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /**
     * Test that English locale renders with LTR direction.
     */
    public function test_english_locale_renders_with_ltr(): void
    {
        $response = $this->get('/admin/login?locale=en');
        
        $response->assertOk();
        $response->assertSee('dir="ltr"', false);
        $response->assertDontSee('dir="rtl"', false);
    }

    /**
     * Test that Arabic locale renders with RTL direction.
     */
    public function test_arabic_locale_renders_with_rtl(): void
    {
        $response = $this->get('/admin/login?locale=ar');
        
        $response->assertOk();
        $response->assertSee('dir="rtl"', false);
        $response->assertDontSee('dir="ltr"', false);
    }

    /**
     * Test that default locale (English) renders with LTR direction.
     */
    public function test_default_locale_renders_with_ltr(): void
    {
        $response = $this->get('/admin/login');
        
        $response->assertOk();
        $response->assertSee('dir="ltr"', false);
    }

    /**
     * Test that authenticated user's preferred locale affects direction.
     */
    public function test_authenticated_user_arabic_locale_renders_with_rtl(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['locale' => 'ar']);
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        
        $response = $this->actingAs($user)->get('/admin');
        
        $response->assertOk();
        $response->assertSee('dir="rtl"', false);
    }

    /**
     * Test that authenticated user's English locale renders with LTR.
     */
    public function test_authenticated_user_english_locale_renders_with_ltr(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['locale' => 'en']);
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        
        $response = $this->actingAs($user)->get('/admin');
        
        $response->assertOk();
        $response->assertSee('dir="ltr"', false);
    }

    /**
     * Test that RTL CSS is loaded for Arabic locale.
     */
    public function test_rtl_css_loaded_for_arabic_locale(): void
    {
        $response = $this->get('/admin/login?locale=ar');
        
        $response->assertOk();
        $response->assertSee('tabler.rtl.min.css', false);
    }

    /**
     * Test that RTL CSS is NOT loaded for English locale.
     */
    public function test_rtl_css_not_loaded_for_english_locale(): void
    {
        $response = $this->get('/admin/login?locale=en');
        
        $response->assertOk();
        $response->assertDontSee('tabler.rtl.min.css', false);
    }

    /**
     * Test that changing locale via query parameter changes direction.
     */
    public function test_changing_locale_changes_direction(): void
    {
        // Test English
        $responseEn = $this->get('/admin/login?locale=en');
        $responseEn->assertSee('dir="ltr"', false);
        
        // Test Arabic
        $responseAr = $this->get('/admin/login?locale=ar');
        $responseAr->assertSee('dir="rtl"', false);
    }

    /**
     * Test that existing admin pages still render correctly with LTR.
     */
    public function test_existing_admin_pages_render_correctly_with_ltr(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['locale' => 'en']);
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        
        $response = $this->actingAs($user)->get('/admin');
        
        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('dir="ltr"', false);
    }

    /**
     * Test that existing admin pages still render correctly with RTL.
     */
    public function test_existing_admin_pages_render_correctly_with_rtl(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['locale' => 'ar']);
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        
        $response = $this->actingAs($user)->get('/admin');
        
        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('dir="rtl"', false);
    }

    /**
     * Test that login page works with both LTR and RTL.
     */
    public function test_login_page_works_with_both_directions(): void
    {
        // Test LTR
        $responseEn = $this->get('/admin/login?locale=en');
        $responseEn->assertOk();
        $responseEn->assertSee('Admin Login');
        $responseEn->assertSee('dir="ltr"', false);
        
        // Test RTL
        $responseAr = $this->get('/admin/login?locale=ar');
        $responseAr->assertOk();
        $responseAr->assertSee('Admin Login');
        $responseAr->assertSee('dir="rtl"', false);
    }

    /**
     * Test that invalid locale falls back to default (English/LTR).
     */
    public function test_invalid_locale_falls_back_to_english_ltr(): void
    {
        $response = $this->get('/admin/login?locale=invalid');
        
        $response->assertOk();
        $response->assertSee('dir="ltr"', false);
    }

    /**
     * Test that Accept-Language header can influence direction.
     */
    public function test_accept_language_header_influences_direction(): void
    {
        $response = $this->withHeader('Accept-Language', 'ar-AR,ar;q=0.9')->get('/admin/login');
        
        $response->assertOk();
        $response->assertSee('dir="rtl"', false);
    }

    /**
     * Test that authentication remains unchanged with RTL.
     */
    public function test_authentication_remains_unchanged_with_rtl(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        
        // Login with Arabic locale
        $response = $this->withHeader('Accept-Language', 'ar-AR')->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $response->assertRedirect();
    }

    /**
     * Test that authorization remains unchanged with RTL.
     */
    public function test_authorization_remains_unchanged_with_rtl(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['locale' => 'ar']);
        
        // User without admin access should still be denied with 403
        $response = $this->actingAs($user)->get('/admin/users');
        
        $response->assertForbidden();
    }
}