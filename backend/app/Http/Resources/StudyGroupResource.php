<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudyGroupResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = $request->query('locale', app()->getLocale());
        $translation = $this->whenLoaded('translations', function () use ($locale) {
            return $this->getTranslation($locale);
        });

        return [
            'id' => $this->id,
            'name' => $translation?->name ?? $this->name,
            'slug' => $this->slug,
            'description' => $translation?->description ?? $this->description,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'max_members' => $this->max_members,
            'rules' => $translation?->rules ?? $this->rules,
            'translations' => $this->whenLoaded('translations', function () {
                return $this->translations
                    ->map(fn ($t): array => [
                        'locale' => $t->locale,
                        'name' => $t->name,
                        'description' => $t->description,
                        'rules' => $t->rules,
                    ])
                    ->keyBy('locale')
                    ->all();
            }),
            'owner' => $this->whenLoaded('owner', fn (): array => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'email' => $this->owner->email,
            ]),
            'category' => $this->whenLoaded('category', fn (): ?array => $this->category
                ? ['id' => $this->category->id, 'name' => $this->category->name, 'slug' => $this->category->slug]
                : null),
            'academic_level' => $this->whenLoaded('academicLevel', fn (): ?array => $this->academicLevel
                ? [
                    'id' => $this->academicLevel->id,
                    'name' => $this->academicLevel->name,
                    'slug' => $this->academicLevel->slug,
                ]
                : null),
            'subjects' => $this->whenLoaded(
                'subjects',
                fn (): array => $this->subjects
                    ->map(fn ($subject): array => [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'slug' => $subject->slug,
                    ])
                    ->all(),
            ),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
