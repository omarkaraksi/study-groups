<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Castable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    #[Fillable(['user_id', 'bio', 'avatar', 'date_of_birth', 'academic_level_id', 'institution', 'field_of_study', 'city', 'area', 'locale'])]
    protected $fillable = [
        'user_id',
        'bio',
        'avatar',
        'date_of_birth',
        'academic_level_id',
        'institution',
        'field_of_study',
        'city',
        'area',
        'locale',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
