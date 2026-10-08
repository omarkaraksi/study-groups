<?php

namespace Database\Factories;

use App\Domain\StudyGroups\Models\StudyGroup;
use App\Domain\StudyGroups\Models\StudyGroupTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudyGroupTranslationFactory extends Factory
{
    protected $model = StudyGroupTranslation::class;

    public function definition(): array
    {
        return [
            'study_group_id' => fn () => StudyGroup::factory(),
            'locale' => 'en',
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'rules' => null,
        ];
    }

    public function arabic(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'locale' => 'ar',
                'name' => 'مجموعة دراسة ' . $this->faker->word,
                'description' => $this->faker->paragraph,
            ];
        });
    }
}
