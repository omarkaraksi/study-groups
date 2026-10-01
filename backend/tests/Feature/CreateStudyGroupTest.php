<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CreateStudyGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_a_study_group(): void
    {
        $this->postJson('/api/v1/study-groups', $this->validAttributes())
            ->assertUnauthorized();

        $this->assertDatabaseCount('study_groups', 0);
    }

    public function test_guest_api_request_without_json_accept_header_receives_unauthorized_response(): void
    {
        $this->post('/api/v1/study-groups', $this->validAttributes())
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');

        $this->assertDatabaseCount('study_groups', 0);
    }

    public function test_authenticated_user_can_create_a_group_and_becomes_its_owner(): void
    {
        $creator = User::factory()->create();
        $categoryId = $this->createCategory();
        $academicLevelId = $this->createAcademicLevel();
        $subjectIds = [
            $this->createSubject('Physics'),
            $this->createSubject('Mathematics'),
        ];

        $response = $this->withToken($creator->createToken('test')->plainTextToken)
            ->postJson('/api/v1/study-groups', [
                ...$this->validAttributes(),
                'category_id' => $categoryId,
                'academic_level_id' => $academicLevelId,
                'subject_ids' => $subjectIds,
                'max_members' => 25,
                'rules' => 'Be respectful and stay on topic.',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Physics Study Circle')
            ->assertJsonPath('data.slug', 'physics-study-circle')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.visibility', 'private')
            ->assertJsonPath('data.max_members', 25)
            ->assertJsonPath('data.owner.id', $creator->id)
            ->assertJsonPath('data.category.id', $categoryId)
            ->assertJsonPath('data.academic_level.id', $academicLevelId)
            ->assertJsonCount(2, 'data.subjects');

        $studyGroupId = $response->json('data.id');

        $this->assertDatabaseHas('study_groups', [
            'id' => $studyGroupId,
            'created_by' => $creator->id,
            'status' => 'active',
            'slug' => 'physics-study-circle',
        ]);
        $this->assertDatabaseHas('study_group_members', [
            'study_group_id' => $studyGroupId,
            'user_id' => $creator->id,
            'role' => 'owner',
            'status' => 'active',
        ]);
        $this->assertDatabaseCount('study_group_members', 1);
        $this->assertDatabaseCount('study_group_subjects', 2);
    }

    public function test_optional_values_default_to_null_and_subjects_can_be_omitted(): void
    {
        $creator = User::factory()->create();

        $response = $this->withToken($creator->createToken('test')->plainTextToken)
            ->postJson('/api/v1/study-groups', $this->validAttributes());

        $response->assertCreated()
            ->assertJsonPath('data.category', null)
            ->assertJsonPath('data.academic_level', null)
            ->assertJsonPath('data.max_members', null)
            ->assertJsonPath('data.rules', null)
            ->assertJsonCount(0, 'data.subjects');

        $this->assertDatabaseCount('study_group_subjects', 0);
        $this->assertDatabaseHas('study_group_members', [
            'study_group_id' => $response->json('data.id'),
            'user_id' => $creator->id,
            'role' => 'owner',
            'status' => 'active',
        ]);
    }

    public function test_validation_rejects_invalid_group_attributes_and_lookup_ids(): void
    {
        $creator = User::factory()->create();
        $inactiveCategoryId = $this->createCategory(isActive: false);
        $inactiveAcademicLevelId = $this->createAcademicLevel(isActive: false);
        $deletedSubjectId = $this->createSubject('Deleted Subject', deleted: true);
        $token = $creator->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/study-groups', [
                'name' => '',
                'description' => '',
                'visibility' => 'friends',
                'category_id' => $inactiveCategoryId,
                'academic_level_id' => $inactiveAcademicLevelId,
                'subject_ids' => [$deletedSubjectId, $deletedSubjectId],
                'max_members' => 0,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'description',
                'visibility',
                'category_id',
                'academic_level_id',
                'subject_ids.0',
                'subject_ids.1',
                'max_members',
            ]);

        $this->assertDatabaseCount('study_groups', 0);
        $this->assertDatabaseCount('study_group_members', 0);
    }

    public function test_client_cannot_set_server_managed_fields_or_upload_cover_image_yet(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->withToken($creator->createToken('test')->plainTextToken)
            ->postJson('/api/v1/study-groups', [
                ...$this->validAttributes(),
                'created_by' => $otherUser->id,
                'slug' => 'client-chosen-slug',
                'status' => 'draft',
                'cover_image' => 'not-supported-yet.jpg',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['created_by', 'slug', 'status', 'cover_image']);

        $this->assertDatabaseCount('study_groups', 0);
    }

    public function test_duplicate_group_names_receive_unique_slugs(): void
    {
        $creator = User::factory()->create();
        $token = $creator->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/study-groups', $this->validAttributes())
            ->assertCreated()
            ->assertJsonPath('data.slug', 'physics-study-circle');

        $this->withToken($token)
            ->postJson('/api/v1/study-groups', $this->validAttributes())
            ->assertCreated()
            ->assertJsonPath('data.slug', 'physics-study-circle-2');

        $this->assertDatabaseCount('study_groups', 2);
    }

    /**
     * @return array<string, mixed>
     */
    private function validAttributes(): array
    {
        return [
            'name' => 'Physics Study Circle',
            'description' => 'A group for discussing physics topics.',
            'visibility' => 'private',
        ];
    }

    private function createCategory(bool $isActive = true): int
    {
        return DB::table('categories')->insertGetId([
            'name' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'is_active' => $isActive,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAcademicLevel(bool $isActive = true): int
    {
        return DB::table('academic_levels')->insertGetId([
            'name' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'is_active' => $isActive,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createSubject(string $name, bool $deleted = false): int
    {
        return DB::table('subjects')->insertGetId([
            'name' => $name,
            'slug' => fake()->unique()->slug(),
            'deleted_at' => $deleted ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
