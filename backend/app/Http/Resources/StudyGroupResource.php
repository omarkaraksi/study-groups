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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'max_members' => $this->max_members,
            'rules' => $this->rules,
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
