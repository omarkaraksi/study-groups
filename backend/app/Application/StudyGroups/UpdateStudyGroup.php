<?php

namespace App\Application\StudyGroups;

use App\Domain\StudyGroups\Models\StudyGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateStudyGroup
{
    /**
     * @param array{
     *     name?: string,
     *     description?: string,
     *     visibility?: string,
     *     category_id?: int|null,
     *     academic_level_id?: int|null,
     *     subject_ids?: array<int, int>,
     *     max_members?: int|null,
     *     rules?: string|null,
     *     status?: string
     * } $attributes
     */
    public function handle(User $user, StudyGroup $group, array $attributes): StudyGroup
    {
        return DB::transaction(function () use ($group, $attributes): StudyGroup {
            $subjectIds = $attributes['subject_ids'] ?? null;
            unset($attributes['subject_ids']);

            $group->update($attributes);

            if ($subjectIds !== null) {
                $group->subjects()->sync($subjectIds);
            }

            return $group->load(['owner', 'category', 'academicLevel', 'subjects']);
        });
    }
}
