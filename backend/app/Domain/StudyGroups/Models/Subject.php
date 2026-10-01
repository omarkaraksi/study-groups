<?php

namespace App\Domain\StudyGroups\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'description'])]
class Subject extends Model
{
    use SoftDeletes;
}
