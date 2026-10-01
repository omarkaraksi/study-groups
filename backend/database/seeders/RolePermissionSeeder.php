<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Admin Access', 'slug' => 'admin.access', 'is_active' => true],
            ['name' => 'Study Groups View', 'slug' => 'study-groups.view', 'is_active' => true],
            ['name' => 'Study Groups Create', 'slug' => 'study-groups.create', 'is_active' => true],
            ['name' => 'Study Groups Update', 'slug' => 'study-groups.update', 'is_active' => true],
            ['name' => 'Study Groups Delete', 'slug' => 'study-groups.delete', 'is_active' => true],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(
                ['slug' => $p['slug']],
                ['name' => $p['name'], 'is_active' => $p['is_active']]
            );
        }

        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin', 'is_active' => true]
        );

        $moderatorRole = Role::firstOrCreate(
            ['slug' => 'moderator'],
            ['name' => 'Moderator', 'is_active' => true]
        );

        $adminPerms = Permission::whereIn('slug', [
            'admin.access', 'study-groups.view', 'study-groups.create', 'study-groups.update', 'study-groups.delete',
        ])->get();

        $moderatorPerms = Permission::whereIn('slug', [
            'admin.access', 'study-groups.view',
        ])->get();

        $adminRole->permissions()->syncWithoutDetaching($adminPerms->pluck('id'));
        $moderatorRole->permissions()->syncWithoutDetaching($moderatorPerms->pluck('id'));
    }
}
