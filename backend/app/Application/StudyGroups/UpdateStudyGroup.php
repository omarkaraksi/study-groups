<?php

namespace App\Application\StudyGroups;

use App\Domain\StudyGroups\Models\StudyGroup;
use App\Domain\StudyGroups\Models\StudyGroupTranslation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateStudyGroup
{
    /**
     * @param array{
     *     name?: string,
     *     description?: string,
     *     rules?: string|null,
     *     visibility?: string,
     *     category_id?: int|null,
     *     academic_level_id?: int|null,
     *     subject_ids?: array<int, int>,
     *     max_members?: int|null,
     *     translations?: array<string, array<string, mixed>>
     * } $attributes
     */
    public function handle(User $user, StudyGroup $group, array $attributes): StudyGroup
    {
        return DB::transaction(function () use ($group, $attributes): StudyGroup {
            $subjectIds = $attributes['subject_ids'] ?? null;
            unset($attributes['subject_ids']);
            
            // Extract translations if present
            $translations = $attributes['translations'] ?? [];
            unset($attributes['translations']);

            // If translations are provided, update from English translation
            if (! empty($translations)) {
                // Update main entity from English translation
                if (isset($translations['en'])) {
                    $group->update([
                        'name' => $translations['en']['name'] ?? $group->name,
                        'description' => $translations['en']['description'] ?? $group->description,
                        'rules' => $translations['en']['rules'] ?? $group->rules,
                    ]);
                }

                // Handle all translations
                foreach ($translations as $locale => $translationData) {
                    // Upsert translation
                    StudyGroupTranslation::query()->updateOrCreate(
                        [
                            'study_group_id' => $group->id,
                            'locale' => $locale,
                        ],
                        [
                            'name' => $translationData['name'] ?? null,
                            'description' => $translationData['description'] ?? null,
                            'rules' => $translationData['rules'] ?? null,
                        ]
                    );
                }
            } else {
                // Old format: update main entity directly
                // Filter out translatable fields that should go to main entity
                $updatableFields = ['name', 'description', 'rules', 'visibility', 'category_id', 'academic_level_id', 'max_members'];
                $filteredAttributes = array_intersect_key($attributes, array_flip($updatableFields));
                
                if (! empty($filteredAttributes)) {
                    $group->update($filteredAttributes);
                }
            }

            if ($subjectIds !== null) {
                $group->subjects()->sync($subjectIds);
            }

            return $group->load(['owner', 'category', 'academicLevel', 'subjects', 'translations']);
        });
    }
}
