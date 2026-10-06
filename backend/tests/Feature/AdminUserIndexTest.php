<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_authorized_admin_can_access_users_index(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $admin->permissions()->attach(Permission::where('slug', 'admin.access')->first());
        $admin->permissions()->attach(Permission::where('slug', 'users.view')->first());

        $this->actingAs($admin);

        $response = $this->get(route('admin.users.index'));
        $response->assertStatus(200);
    }

    public function test_authenticated_user_with_admin_access_can_view_users(): void
    {
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
        ]);
        // User has admin.access but not users.view - should still work since admin.access grants admin access
        $user->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $this->actingAs($user);

        $response = $this->get(route('admin.users.index'));
        $response->assertStatus(200);
    }

    public function test_authenticated_user_without_admin_access_is_denied(): void
    {
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
        ]);
        // User has users.view but not admin.access - should be caught by middleware first
        $user->permissions()->attach(Permission::where('slug', 'users.view')->first());

        $this->actingAs($user);

        $response = $this->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_authenticated_user_without_any_permissions_is_denied(): void
    {
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
        ]);
        // User has neither admin.access nor users.view
        $this->actingAs($user);

        $response = $this->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_is_redirected_to_admin_login(): void
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_users_index_displays_expected_user_data(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'name' => 'Admin User'
        ]);
        $admin->permissions()->attach(Permission::where('slug', 'admin.access')->first());
        $admin->permissions()->attach(Permission::where('slug', 'users.view')->first());

        // Create some test users
        $user1 = User::factory()->create(['name' => 'Test User 1', 'email' => 'test1@example.com']);
        $user2 = User::factory()->create(['name' => 'Test User 2', 'email' => 'test2@example.com']);

        $this->actingAs($admin);

        $response = $this->get(route('admin.users.index'));
        $response->assertStatus(200);
        
        // Check that the response contains the user data
        $response->assertSee('Test User 1');
        $response->assertSee('test1@example.com');
        $response->assertSee('Test User 2');
        $response->assertSee('test2@example.com');
    }

    // Show
    public function test_admin_can_view_individual_user(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $admin->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

        $this->actingAs($admin);

        $response = $this->get(route('admin.users.show', $user));
        $response->assertStatus(200);
        $response->assertSee('Test User');
        $response->assertSee('test@example.com');
    }

    public function test_user_without_admin_access_cannot_view_individual_user(): void
    {
        $user = User::factory()->create([
            'email' => 'regular@test.com',
            'password' => bcrypt('password'),
        ]);

        $targetUser = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

        $this->actingAs($user);

        $response = $this->get(route('admin.users.show', $targetUser));
        $response->assertStatus(403);
    }

    // Edit/Update
    public function test_admin_can_edit_user(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $admin->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

        $this->actingAs($admin);

        $response = $this->get(route('admin.users.edit', $user));
        $response->assertStatus(200);
        $response->assertSee('Test User');
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $admin->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

        $this->actingAs($admin);

        $response = $this->patch(route('admin.users.update', $user), [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('admin.users.show', $user));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated User']);
    }

    public function test_admin_can_update_user_roles(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $admin->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
        $adminRole = \App\Models\Role::where('slug', 'admin')->first();
        $moderatorRole = \App\Models\Role::where('slug', 'moderator')->first();

        $this->actingAs($admin);

        $response = $this->patch(route('admin.users.update', $user), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'roles' => [$moderatorRole->id],
        ]);

        $response->assertRedirect(route('admin.users.show', $user));
        $this->assertTrue($user->fresh()->roles()->where('id', $moderatorRole->id)->exists());
    }

    public function test_user_without_admin_access_cannot_update_user(): void
    {
        $user = User::factory()->create([
            'email' => 'regular@test.com',
            'password' => bcrypt('password'),
        ]);

        $targetUser = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

        $this->actingAs($user);

        $response = $this->patch(route('admin.users.update', $targetUser), [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(403);
    }

    // Delete
    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $admin->permissions()->attach(Permission::where('slug', 'admin.access')->first());

        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

        $this->actingAs($admin);

        $response = $this->delete(route('admin.users.destroy', $user));
        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_without_admin_access_cannot_delete_user(): void
    {
        $user = User::factory()->create([
            'email' => 'regular@test.com',
            'password' => bcrypt('password'),
        ]);

        $targetUser = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

        $this->actingAs($user);

        $response = $this->delete(route('admin.users.destroy', $targetUser));
        $response->assertStatus(403);
    }
}