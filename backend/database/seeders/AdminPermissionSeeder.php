<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class AdminPermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::firstOrCreate(
            ['slug' => 'access-admin'],
            ['name' => 'Access Admin Panel']
        );
    }
}
