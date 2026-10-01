<?php

namespace App\Domain\StudyGroups\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['parent_id', 'name', 'slug', 'description', 'image', 'is_active'])]
class Category extends Model
{
    use SoftDeletes;
}
