<?php

namespace App\Domain\StudyGroups\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'slug',
    'description',
    'cover_image',
    'created_by',
    'category_id',
    'academic_level_id',
    'status',
    'visibility',
    'max_members',
    'rules',
])]
class StudyGroup extends Model
{
    use SoftDeletes;

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function academicLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'study_group_subjects');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'study_group_members')
            ->withPivot(['role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(StudyGroupTranslation::class);
    }

    /**
     * Get the translation for the current app locale, falling back to English.
     */
    public function getTranslation(string $locale = null): ?StudyGroupTranslation
    {
        $locale = $locale ?: app()->getLocale();
        
        // Try the requested locale
        $translation = $this->translations
            ->where('locale', $locale)
            ->first();

        if ($translation) {
            return $translation;
        }

        // Fallback to English
        if ($locale !== 'en') {
            return $this->translations
                ->where('locale', 'en')
                ->first();
        }

        return null;
    }

    /**
     * Get translated field with fallback.
     */
    public function getTranslated(string $field, string $locale = null): ?string
    {
        $translation = $this->getTranslation($locale);
        
        return $translation?->$field ?? null;
    }
}
