<?php

namespace App\Domain\StudyGroups\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'description', 'is_active'])]
class AcademicLevel extends Model
{
    use SoftDeletes;
}
