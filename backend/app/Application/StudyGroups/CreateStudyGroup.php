<?php

namespace App\Application\StudyGroups;

use App\Domain\StudyGroups\Models\StudyGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateStudyGroup
{
    /**
     * @param array{
     *     name: string,
     *     description: string,
     *     visibility: string,
     *     category_id?: int|null,
     *     academic_level_id?: int|null,
     *     subject_ids?: array<int, int>,
     *     max_members?: int|null,
     *     rules?: string|null
     * } $attributes
     */
    public function handle(User $creator, array $attributes): StudyGroup
    {
        return DB::transaction(function () use ($creator, $attributes): StudyGroup {
            $subjectIds = $attributes['subject_ids'] ?? [];
            unset($attributes['subject_ids']);

            $group = StudyGroup::query()->create([
                ...$attributes,
                'slug' => $this->uniqueSlug($attributes['name']),
                'created_by' => $creator->getKey(),
                'status' => 'active',
            ]);

            if ($subjectIds !== []) {
                $group->subjects()->sync($subjectIds);
            }

            $group->members()->attach($creator->getKey(), [
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            return $group->load(['owner', 'category', 'academicLevel', 'subjects']);
        });
    }

    private function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'study-group';
        $slug = Str::limit($baseSlug, 255, '');
        $suffix = 2;

        while (StudyGroup::withTrashed()->where('slug', $slug)->exists()) {
            $ending = '-'.$suffix++;
            $slug = Str::limit($baseSlug, 255 - strlen($ending), '').$ending;
        }

        return $slug;
    }
}
