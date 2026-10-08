<?php

namespace App\Application\StudyGroups;

use App\Domain\StudyGroups\Models\StudyGroup;
use App\Domain\StudyGroups\Models\StudyGroupTranslation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateStudyGroup
{
    /**
     * @param array{
     *     name?: string,
     *     description?: string,
     *     rules?: string|null,
     *     visibility: string,
     *     category_id?: int|null,
     *     academic_level_id?: int|null,
     *     subject_ids?: array<int, int>,
     *     max_members?: int|null,
     *     translations?: array<string, array<string, mixed>>
     * } $attributes
     */
    public function handle(User $creator, array $attributes): StudyGroup
    {
        return DB::transaction(function () use ($creator, $attributes): StudyGroup {
            $subjectIds = $attributes['subject_ids'] ?? [];
            unset($attributes['subject_ids']);
            
            // Extract translations if present
            $translations = $attributes['translations'] ?? [];
            unset($attributes['translations']);

            // Determine the name for slug generation
            $nameForSlug = $translations['en']['name'] ?? $attributes['name'] ?? 'study-group';

            // Create the main entity
            $group = StudyGroup::query()->create([
                'name' => $translations['en']['name'] ?? $attributes['name'] ?? '',
                'description' => $translations['en']['description'] ?? $attributes['description'] ?? '',
                'rules' => $translations['en']['rules'] ?? $attributes['rules'] ?? null,
                'slug' => $this->uniqueSlug($nameForSlug),
                'created_by' => $creator->getKey(),
                'status' => 'active',
                'visibility' => $attributes['visibility'] ?? 'private',
                'category_id' => $attributes['category_id'] ?? null,
                'academic_level_id' => $attributes['academic_level_id'] ?? null,
                'max_members' => $attributes['max_members'] ?? null,
            ]);

            // Create translations if provided
            if (! empty($translations)) {
                foreach ($translations as $locale => $translationData) {
                    StudyGroupTranslation::query()->create([
                        'study_group_id' => $group->id,
                        'locale' => $locale,
                        'name' => $translationData['name'],
                        'description' => $translationData['description'] ?? '',
                        'rules' => $translationData['rules'] ?? null,
                    ]);
                }
            }

            if ($subjectIds !== []) {
                $group->subjects()->sync($subjectIds);
            }

            $group->members()->attach($creator->getKey(), [
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            return $group->load(['owner', 'category', 'academicLevel', 'subjects', 'translations']);
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
