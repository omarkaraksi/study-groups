<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudyGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('study_group')) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'max:65535'],
            'rules' => ['nullable', 'string', 'max:65535'],
            'visibility' => ['sometimes', 'required', Rule::in(['public', 'private'])],
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
            'status' => ['prohibited'],
            'created_by' => ['prohibited'],
            'slug' => ['prohibited'],
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

    /**
     * Get the default English translation from request.
     * Works with both old format (name, description, rules) and new format (translations.en).
     */
    public function getDefaultTranslation(): array
    {
        if ($this->filled('translations.en')) {
            return $this->validated('translations.en', []);
        }

        return [
            'name' => $this->input('name'),
            'description' => $this->input('description'),
            'rules' => $this->input('rules'),
        ];
    }

    /**
     * Check if request uses translations format.
     */
    public function usesTranslations(): bool
    {
        return $this->filled('translations');
    }
}
