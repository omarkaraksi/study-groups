<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Database\Seeders\AdminPermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminPermissionSeeder::class);
        $this->seed(RolePermissionSeeder::class);
    }

    // Guest
    public function test_guest_can_open_admin_login(): void
    {
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_access_protected_admin_crud(): void
    {
        $response = $this->get(route('admin.study-groups.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    // Login
    public function test_valid_admin_credentials_can_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $response = $this->post(route('admin.login'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => 'admin@test.com',
            'password' => 'wrong',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_successful_login_redirects_to_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $response = $this->post(route('admin.login'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_session_is_regenerated_after_successful_login(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $this->get(route('admin.login'));
        $oldSession = session()->getId();

        $this->post(route('admin.login'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $this->assertNotEquals($oldSession, session()->getId());
    }

    // Authorization
    public function test_authenticated_user_with_permission_can_access_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    public function test_authenticated_user_without_permission_receives_403(): void
    {
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_permission_checks_are_enforced_server_side(): void
    {
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('admin.study-groups.index'));
        $response->assertStatus(403);
    }

    // Logout
    public function test_authenticated_admin_can_log_out(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $this->actingAs($user);

        $response = $this->post(route('admin.logout'));
        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_session_is_invalidated_on_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $this->actingAs($user);
        $this->post(route('admin.logout'));

        $this->assertGuest();
    }

    public function test_protected_admin_pages_are_no_longer_accessible_after_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $this->actingAs($user);
        $this->post(route('admin.logout'));

        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    // Study Groups
    public function test_study_groups_admin_crud_remains_protected(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $this->actingAs($user);

        $response = $this->get(route('admin.study-groups.index'));
        $response->assertStatus(200);
    }
}
