<?php

namespace App\Domain\StudyGroups\Policies;

use App\Domain\StudyGroups\Models\StudyGroup;
use App\Models\User;

class StudyGroupPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, StudyGroup $group): bool
    {
        return $user->id === $group->created_by;
    }

    public function view(User $user, StudyGroup $group): bool
    {
        if ($group->visibility === 'public') {
            return true;
        }

        return $group->members()->where('user_id', $user->id)->exists();
    }
}
