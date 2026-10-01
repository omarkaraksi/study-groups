<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudyGroupDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $academicLevels = [
            ['name' => 'High School', 'slug' => 'high-school'],
            ['name' => 'Undergraduate', 'slug' => 'undergraduate'],
            ['name' => 'Postgraduate', 'slug' => 'postgraduate'],
        ];

        foreach ($academicLevels as $level) {
            DB::table('academic_levels')->updateOrInsert(
                ['slug' => $level['slug']],
                [
                    'name' => $level['name'],
                    'description' => fake()->sentence(),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $categories = [
            ['name' => 'Science', 'slug' => 'science'],
            ['name' => 'Technology', 'slug' => 'technology'],
            ['name' => 'Humanities', 'slug' => 'humanities'],
            ['name' => 'Mathematics', 'slug' => 'mathematics'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $category['slug']],
                [
                    'parent_id' => null,
                    'name' => $category['name'],
                    'description' => fake()->sentence(),
                    'image' => null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $subjects = [
            'Biology',
            'Chemistry',
            'Computer Science',
            'History',
            'Literature',
            'Mathematics',
            'Physics',
            'Psychology',
        ];

        foreach ($subjects as $name) {
            DB::table('subjects')->updateOrInsert(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => fake()->sentence(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $users = collect(range(1, 10))
            ->map(fn (int $number): User => User::query()->firstOrCreate(
                ['email' => sprintf('demo-user-%02d@example.test', $number)],
                [
                    'name' => fake()->name(),
                    'password' => Str::random(40),
                ],
            ));

        $categoryIds = DB::table('categories')->pluck('id', 'slug');
        $academicLevelIds = DB::table('academic_levels')->pluck('id', 'slug');
        $subjectIds = DB::table('subjects')->pluck('id', 'slug');

        $groupDefinitions = [
            ['category' => 'science', 'level' => 'high-school'],
            ['category' => 'technology', 'level' => 'undergraduate'],
            ['category' => 'humanities', 'level' => 'undergraduate'],
            ['category' => 'mathematics', 'level' => 'high-school'],
            ['category' => 'science', 'level' => 'postgraduate'],
            ['category' => 'technology', 'level' => 'postgraduate'],
        ];

        foreach ($groupDefinitions as $index => $definition) {
            $owner = $users[$index];
            $slug = sprintf('demo-study-group-%02d', $index + 1);
            $groupName = fake()->words(3, true);

            DB::table('study_groups')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => Str::title($groupName),
                    'description' => fake()->paragraph(),
                    'cover_image' => null,
                    'created_by' => $owner->id,
                    'category_id' => $categoryIds[$definition['category']],
                    'academic_level_id' => $academicLevelIds[$definition['level']],
                    'status' => 'active',
                    'visibility' => $index % 2 === 0 ? 'public' : 'private',
                    'max_members' => 30,
                    'rules' => fake()->sentence(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );

            $groupId = DB::table('study_groups')->where('slug', $slug)->value('id');

            DB::table('study_group_members')->updateOrInsert(
                ['study_group_id' => $groupId, 'user_id' => $owner->id],
                [
                    'role' => 'owner',
                    'status' => 'active',
                    'joined_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );

            foreach ([1, 2, 3] as $offset) {
                $member = $users[($index + $offset) % $users->count()];

                DB::table('study_group_members')->updateOrInsert(
                    ['study_group_id' => $groupId, 'user_id' => $member->id],
                    [
                        'role' => $offset === 1 ? 'moderator' : 'member',
                        'status' => 'active',
                        'joined_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }

            $groupSubjects = array_slice($subjects, $index, 3);

            foreach ($groupSubjects as $subjectName) {
                DB::table('study_group_subjects')->insertOrIgnore([
                    'study_group_id' => $groupId,
                    'subject_id' => $subjectIds[Str::slug($subjectName)],
                ]);
            }
        }
    }
}
