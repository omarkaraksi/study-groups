<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSwitcherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AdminPermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /**
     * Test that language switcher is visible in the navbar.
     */
    public function test_language_switcher_is_visible_in_navbar(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        
        $response = $this->actingAs($user)->get('/admin');
        
        $response->assertOk();
        $response->assertSee('🌐', false); // Language icon
        $response->assertSee('English', false);
    }

    /**
     * Test that switching to Arabic sets locale to ar.
     */
    public function test_switching_to_arabic_sets_locale_to_ar(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        $user->profile()->create(['locale' => 'en']);
        
        $response = $this->actingAs($user)->get('/admin/locale/ar');
        
        $response->assertRedirect();
        
        // Check that the user's profile was updated
        $user->refresh();
        $this->assertEquals('ar', $user->profile->locale);
    }

    /**
     * Test that switching to English sets locale to en.
     */
    public function test_switching_to_english_sets_locale_to_en(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        $user->profile()->create(['locale' => 'ar']);
        
        $response = $this->actingAs($user)->get('/admin/locale/en');
        
        $response->assertRedirect();
        
        // Check that the user's profile was updated
        $user->refresh();
        $this->assertEquals('en', $user->profile->locale);
    }

    /**
     * Test that Arabic locale results in RTL direction.
     */
    public function test_arabic_locale_results_in_rtl(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        $user->profile()->create(['locale' => 'ar']);
        
        $response = $this->actingAs($user)->get('/admin');
        
        $response->assertOk();
        $response->assertSee('dir="rtl"', false);
        $response->assertSee('العربية', false); // Arabic language name in switcher
    }

    /**
     * Test that English locale results in LTR direction.
     */
    public function test_english_locale_results_in_ltr(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        $user->profile()->create(['locale' => 'en']);
        
        $response = $this->actingAs($user)->get('/admin');
        
        $response->assertOk();
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('English', false); // English language name in switcher
    }

    /**
     * Test that authenticated user's locale preference is persisted.
     */
    public function test_authenticated_user_locale_preference_is_persisted(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        $user->profile()->create(['locale' => 'en']);
        
        // Switch to Arabic
        $this->actingAs($user)->get('/admin/locale/ar');
        
        // Refresh user and check profile
        $user->refresh();
        $this->assertEquals('ar', $user->profile->locale);
        
        // Switch back to English
        $this->actingAs($user)->get('/admin/locale/en');
        
        // Refresh user and check profile again
        $user->refresh();
        $this->assertEquals('en', $user->profile->locale);
    }

    /**
     * Test that unsupported locale values are rejected safely.
     */
    public function test_unsupported_locale_values_are_rejected_safely(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        
        $response = $this->actingAs($user)->get('/admin/locale/invalid');
        
        $response->assertStatus(400);
        $response->assertSee('Unsupported locale');
    }

    /**
     * Test that language switcher works for guests (unauthenticated users).
     */
    public function test_language_switcher_works_for_guests(): void
    {
        // Test switching to Arabic as guest
        $response = $this->get('/admin/locale/ar');
        
        $response->assertRedirect();
        
        // For guests, locale should be stored in session
        // The middleware will handle session-based locale
        $this->assertTrue(session()->has('locale'));
    }

    /**
     * Test that language switcher shows currently selected language.
     */
    public function test_language_switcher_shows_currently_selected_language(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        $user->profile()->create(['locale' => 'ar']);
        
        $response = $this->actingAs($user)->get('/admin');
        
        $response->assertOk();
        $response->assertSee('العربية', false); // Arabic should be shown as current
        $response->assertSee('English', false);   // English should be available
    }

    /**
     * Test that switching language and then accessing a page shows the correct language.
     */
    public function test_switching_language_then_accessing_page_shows_correct_language(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        $user->profile()->create(['locale' => 'en']);
        
        // Switch to Arabic
        $this->actingAs($user)->get('/admin/locale/ar');
        
        // Access dashboard - should show Arabic
        $response = $this->actingAs($user)->get('/admin');
        
        $response->assertOk();
        $response->assertSee('dir="rtl"', false);
        $response->assertSee('العربية', false);
    }

    /**
     * Test that switching language multiple times works correctly.
     */
    public function test_switching_language_multiple_times_works_correctly(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        $user->profile()->create(['locale' => 'en']);
        
        // Switch to Arabic
        $this->actingAs($user)->get('/admin/locale/ar');
        $user->refresh();
        $this->assertEquals('ar', $user->profile->locale);
        
        // Switch back to English
        $this->actingAs($user)->get('/admin/locale/en');
        $user->refresh();
        $this->assertEquals('en', $user->profile->locale);
        
        // Switch to Arabic again
        $this->actingAs($user)->get('/admin/locale/ar');
        $user->refresh();
        $this->assertEquals('ar', $user->profile->locale);
    }

    /**
     * Test that authentication remains unchanged with language switcher.
     */
    public function test_authentication_remains_unchanged_with_language_switcher(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        
        // User can login
        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
        
        // Switch language
        $this->actingAs($user)->get('/admin/locale/ar');
        
        // User should still be authenticated
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test that authorization remains unchanged with language switcher.
     */
    public function test_authorization_remains_unchanged_with_language_switcher(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        $user->profile()->create(['locale' => 'ar']);
        
        // User with admin access can access users
        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertOk();
        
        // Switch language
        $this->actingAs($user)->get('/admin/locale/en');
        
        // User should still be able to access users
        $response = $this->actingAs($user)->get('/admin/users');
        $response->assertOk();
    }

    /**
     * Test that language switcher dropdown shows both languages.
     */
    public function test_language_switcher_dropdown_shows_both_languages(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        
        $response = $this->actingAs($user)->get('/admin');
        
        $response->assertOk();
        $response->assertSee('English', false);
        $response->assertSee('العربية', false);
    }

    /**
     * Test that language switcher respects the current locale in the UI.
     */
    public function test_language_switcher_respects_current_locale_in_ui(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        $user->profile()->create(['locale' => 'ar']);
        
        $response = $this->actingAs($user)->get('/admin');
        
        $response->assertOk();
        // When Arabic is selected, it should show as active
        $response->assertSee('active', false); // The active class should be present
    }

    /**
     * Test that CRUD action buttons are translated to Arabic when locale is Arabic.
     */
    public function test_crud_action_buttons_translated_to_arabic(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        $user->profile()->create(['locale' => 'ar']);
        
        $response = $this->actingAs($user)->get('/admin/users');
        
        $response->assertOk();
        $response->assertSee('عرض', false); // Arabic for "View"
        $response->assertSee('تعديل', false); // Arabic for "Edit"
        $response->assertSee('حذف', false); // Arabic for "Delete"
    }

    /**
     * Test that CRUD table headers are translated to Arabic when locale is Arabic.
     */
    public function test_crud_table_headers_translated_to_arabic(): void
    {
        $user = User::factory()->create();
        $user->permissions()->attach(\App\Models\Permission::where('slug', 'admin.access')->first());
        $user->profile()->create(['locale' => 'ar']);
        
        $response = $this->actingAs($user)->get('/admin/users');
        
        $response->assertOk();
        $response->assertSee('المعرف', false); // Arabic for "ID"
        $response->assertSee('الاسم', false); // Arabic for "Name"
        $response->assertSee('البريد الإلكتروني', false); // Arabic for "Email"
    }
}