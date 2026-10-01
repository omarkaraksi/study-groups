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
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:65535'],
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
            'rules' => ['nullable', 'string', 'max:65535'],
            'created_by' => ['prohibited'],
            'slug' => ['prohibited'],
            'status' => ['prohibited'],
            'cover_image' => ['prohibited'],
        ];
    }
}
