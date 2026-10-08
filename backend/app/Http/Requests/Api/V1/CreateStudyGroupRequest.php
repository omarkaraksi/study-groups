<?php

namespace App\Http\Requests\Api\V1;

use App\Domain\StudyGroups\Models\StudyGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateStudyGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', StudyGroup::class) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required_without:translations', 'string', 'max:255'],
            'description' => ['required_without:translations', 'string', 'max:65535'],
            'rules' => ['nullable', 'string', 'max:65535'],
            'visibility' => ['required', Rule::in(['public', 'private'])],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'academic_level_id' => [
                'nullable',
                'integer',
                Rule::exists('academic_levels', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'subject_ids' => ['sometimes', 'array'],
            'subject_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('subjects', 'id')->whereNull('deleted_at'),
            ],
            'max_members' => ['nullable', 'integer', 'min:1'],
            'created_by' => ['prohibited'],
            'slug' => ['prohibited'],
            'status' => ['prohibited'],
            'cover_image' => ['prohibited'],
        ];

        // Add translation rules only if translations is present
        if ($this->filled('translations')) {
            $rules['translations'] = ['array'];
            $rules['translations.en'] = ['required', 'array'];
            $rules['translations.en.name'] = ['required', 'string', 'max:255'];
            $rules['translations.en.description'] = ['required', 'string', 'max:65535'];
            $rules['translations.en.rules'] = ['nullable', 'string', 'max:65535'];
            
            if ($this->filled('translations.ar')) {
                $rules['translations.ar'] = ['array'];
                $rules['translations.ar.name'] = ['required', 'string', 'max:255'];
                $rules['translations.ar.description'] = ['required', 'string', 'max:65535'];
                $rules['translations.ar.rules'] = ['nullable', 'string', 'max:65535'];
            }
        }

        return $rules;
    }

    /**
     * Get the validated translations data.
     *
     * @return array<string, array<string, mixed>>
     */
    public function translations(): array
    {
        if (! $this->filled('translations')) {
            return [];
        }

        return collect($this->validated('translations', []))
            ->filter(fn ($translation) => is_array($translation) && isset($translation['name']))
            ->toArray();
    }
}
