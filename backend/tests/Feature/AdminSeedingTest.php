<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSeedingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(AdminUserSeeder::class);
    }

    public function test_admin_user_exists_after_seeding(): void
    {
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
    }

    public function test_moderator_user_exists_after_seeding(): void
    {
        $this->assertDatabaseHas('users', ['email' => 'moderator@example.com']);
    }

    public function test_admin_has_admin_role(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertTrue($admin->roles()->where('slug', 'admin')->exists());
    }

    public function test_moderator_has_moderator_role(): void
    {
        $moderator = User::where('email', 'moderator@example.com')->first();
        $this->assertTrue($moderator->roles()->where('slug', 'moderator')->exists());
    }

    public function test_admin_has_admin_access_permission(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertTrue($admin->hasPermission('admin.access'));
    }

    public function test_admin_has_all_study_group_crud_permissions(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertTrue($admin->hasPermission('study-groups.view'));
        $this->assertTrue($admin->hasPermission('study-groups.create'));
        $this->assertTrue($admin->hasPermission('study-groups.update'));
        $this->assertTrue($admin->hasPermission('study-groups.delete'));
    }

    public function test_moderator_has_admin_access_permission(): void
    {
        $moderator = User::where('email', 'moderator@example.com')->first();
        $this->assertTrue($moderator->hasPermission('admin.access'));
    }

    public function test_moderator_has_study_groups_view_permission(): void
    {
        $moderator = User::where('email', 'moderator@example.com')->first();
        $this->assertTrue($moderator->hasPermission('study-groups.view'));
    }

    public function test_moderator_does_not_have_study_group_create_permission(): void
    {
        $moderator = User::where('email', 'moderator@example.com')->first();
        $this->assertFalse($moderator->hasPermission('study-groups.create'));
    }

    public function test_moderator_does_not_have_study_group_update_permission(): void
    {
        $moderator = User::where('email', 'moderator@example.com')->first();
        $this->assertFalse($moderator->hasPermission('study-groups.update'));
    }

    public function test_moderator_does_not_have_study_group_delete_permission(): void
    {
        $moderator = User::where('email', 'moderator@example.com')->first();
        $this->assertFalse($moderator->hasPermission('study-groups.delete'));
    }

    public function test_re_running_seeder_does_not_create_duplicates(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $this->assertEquals(1, User::where('email', 'admin@example.com')->count());
        $this->assertEquals(1, User::where('email', 'moderator@example.com')->count());
        $this->assertEquals(1, Role::where('slug', 'admin')->count());
        $this->assertEquals(1, Role::where('slug', 'moderator')->count());
        $this->assertEquals(5, Permission::count());
    }
}
