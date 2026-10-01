<?php

namespace Tests\Feature;

use App\Domain\StudyGroups\Models\StudyGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateStudyGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_own_group(): void
    {
        $owner = User::factory()->create();
        $group = StudyGroup::query()->create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'description' => 'Desc',
            'visibility' => 'public',
            'created_by' => $owner->id,
            'status' => 'active',
        ]);

        $response = $this->withToken($owner->createToken('test')->plainTextToken)
            ->patchJson("/api/v1/study-groups/{$group->slug}", [
                'name' => 'Updated Name',
                'visibility' => 'private',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.visibility', 'private');

        $this->assertDatabaseHas('study_groups', [
            'id' => $group->id,
            'name' => 'Updated Name',
            'visibility' => 'private',
        ]);
    }

    public function test_non_owner_cannot_update_group(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $group = StudyGroup::query()->create([
            'created_by' => $owner->id,
            'name' => 'Group',
            'slug' => 'group',
            'description' => 'Desc',
            'visibility' => 'public',
            'status' => 'active',
        ]);

        $this->withToken($other->createToken('test')->plainTextToken)
            ->patchJson("/api/v1/study-groups/{$group->slug}", [
                'name' => 'Hacked',
            ])
            ->assertForbidden();
    }

    public function test_guest_cannot_update_group(): void
    {
        $group = StudyGroup::query()->create([
            'name' => 'Group',
            'slug' => 'group',
            'description' => 'Desc',
            'visibility' => 'public',
            'created_by' => User::factory()->create()->id,
            'status' => 'active',
        ]);

        $this->patchJson("/api/v1/study-groups/{$group->slug}", [
            'name' => 'Hacked',
        ])->assertUnauthorized();
    }

    public function test_validation_rejects_invalid_updates(): void
    {
        $owner = User::factory()->create();
        $group = StudyGroup::query()->create([
            'created_by' => $owner->id,
            'name' => 'Group',
            'slug' => 'group',
            'description' => 'Desc',
            'visibility' => 'public',
            'status' => 'active',
        ]);

        $this->withToken($owner->createToken('test')->plainTextToken)
            ->patchJson("/api/v1/study-groups/{$group->slug}", [
                'visibility' => 'invalid',
                'max_members' => 0,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['visibility', 'max_members']);
    }

    public function test_server_managed_fields_are_prohibited(): void
    {
        $owner = User::factory()->create();
        $group = StudyGroup::query()->create([
            'created_by' => $owner->id,
            'name' => 'Group',
            'slug' => 'group',
            'description' => 'Desc',
            'visibility' => 'public',
            'status' => 'active',
        ]);

        $this->withToken($owner->createToken('test')->plainTextToken)
            ->patchJson("/api/v1/study-groups/{$group->slug}", [
                'created_by' => $owner->id + 1,
                'slug' => 'client-slug',
                'status' => 'draft',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['created_by', 'slug', 'status']);
    }
}
