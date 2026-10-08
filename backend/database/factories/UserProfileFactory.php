<?php

namespace Database\Factories;

use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => fn () => \App\Models\User::factory(),
            'bio' => $this->faker->paragraph,
            'avatar' => null,
            'date_of_birth' => $this->faker->date,
            'academic_level_id' => null,
            'institution' => $this->faker->company,
            'field_of_study' => $this->faker->word,
            'city' => $this->faker->city,
            'area' => null,
            'locale' => 'en',
        ];
    }
}
