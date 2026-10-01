<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ShowStudyGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('study_group')) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
