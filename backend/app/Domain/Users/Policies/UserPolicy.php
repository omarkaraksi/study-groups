<?php

namespace App\Domain\Users\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user, User $model = null): bool
    {
        // For admin user index, we check for users.view permission
        // Users with admin.access should also be able to view users as they have admin access
        // $model is null when checking class-level permission
        return $user->hasPermission('users.view') || $user->hasPermission('admin.access');
    }

    public function edit(User $user, User $model = null): bool
    {
        // Users with admin.access should be able to edit users
        return $user->hasPermission('users.edit') || $user->hasPermission('admin.access');
    }

    public function delete(User $user, User $model = null): bool
    {
        // Users with admin.access should be able to delete users
        return $user->hasPermission('users.delete') || $user->hasPermission('admin.access');
    }
}