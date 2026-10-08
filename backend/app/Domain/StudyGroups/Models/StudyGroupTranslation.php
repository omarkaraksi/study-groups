<?php

namespace App\Domain\StudyGroups\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['study_group_id', 'locale', 'name', 'description', 'rules'])]
class StudyGroupTranslation extends Model
{
    /**
     * Get the study group that owns the translation.
     */
    public function studyGroup()
    {
        return $this->belongsTo(StudyGroup::class);
    }
}
