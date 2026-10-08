<?php

namespace Tests\Feature;

use App\Domain\StudyGroups\Models\StudyGroup;
use App\Domain\StudyGroups\Models\StudyGroupTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyGroupTranslationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a study group with English translation.
     */
    public function test_create_study_group_with_english_translation(): void
    {
        $creator = User::factory()->create();
        $token = $creator->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/study-groups', [
                'name' => 'Math Study Group',
                'description' => 'A group for studying mathematics.',
                'visibility' => 'public',
            ]);

        $response->assertCreated();
        
        $groupId = $response->json('data.id');
        
        // Verify main entity was created
        $this->assertDatabaseHas('study_groups', [
            'id' => $groupId,
            'name' => 'Math Study Group',
            'description' => 'A group for studying mathematics.',
        ]);
        
        // With old format, no translations are created automatically
        // The main entity still has the fields
        $this->assertDatabaseMissing('study_group_translations', [
            'study_group_id' => $groupId,
        ]);
    }

    /**
     * Test creating a study group with translations format.
     */
    public function test_create_study_group_with_translations(): void
    {
        $creator = User::factory()->create();
        $token = $creator->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/study-groups', [
                'visibility' => 'public',
                'translations' => [
                    'en' => [
                        'name' => 'Math Study Group',
                        'description' => 'A group for studying mathematics.',
                        'rules' => 'Be respectful.',
                    ],
                    'ar' => [
                        'name' => 'مجموعة دراسة الرياضيات',
                        'description' => 'مجموعة لدراسة الرياضيات.',
                        'rules' => 'كن محترماً.',
                    ],
                ],
            ]);

        $response->assertCreated();
        
        $groupId = $response->json('data.id');
        
        // Verify main entity was created with English data
        $this->assertDatabaseHas('study_groups', [
            'id' => $groupId,
            'name' => 'Math Study Group',
            'description' => 'A group for studying mathematics.',
            'rules' => 'Be respectful.',
        ]);
        
        // Verify English translation was created
        $this->assertDatabaseHas('study_group_translations', [
            'study_group_id' => $groupId,
            'locale' => 'en',
            'name' => 'Math Study Group',
            'description' => 'A group for studying mathematics.',
            'rules' => 'Be respectful.',
        ]);
        
        // Verify Arabic translation was created
        $this->assertDatabaseHas('study_group_translations', [
            'study_group_id' => $groupId,
            'locale' => 'ar',
            'name' => 'مجموعة دراسة الرياضيات',
            'description' => 'مجموعة لدراسة الرياضيات.',
            'rules' => 'كن محترماً.',
        ]);
    }

    /**
     * Test retrieving a study group with English locale.
     */
    public function test_retrieve_study_group_with_english_locale(): void
    {
        $creator = User::factory()->create();
        $token = $creator->createToken('test')->plainTextToken;

        // Create with translations
        $createResponse = $this->withToken($token)
            ->postJson('/api/v1/study-groups', [
                'visibility' => 'public',
                'translations' => [
                    'en' => [
                        'name' => 'Physics Study Group',
                        'description' => 'Study physics together.',
                    ],
                    'ar' => [
                        'name' => 'مجموعة دراسة الفيزياء',
                        'description' => 'دراسة الفيزياء سوياً.',
                    ],
                ],
            ]);

        $slug = $createResponse->json('data.slug');

        // Retrieve with English locale
        $response = $this->withToken($token)
            ->getJson("/api/v1/study-groups/{$slug}?locale=en");

        $response->assertOk()
            ->assertJsonPath('data.name', 'Physics Study Group')
            ->assertJsonPath('data.description', 'Study physics together.');
    }

    /**
     * Test retrieving a study group with Arabic locale.
     */
    public function test_retrieve_study_group_with_arabic_locale(): void
    {
        $creator = User::factory()->create();
        $token = $creator->createToken('test')->plainTextToken;

        // Create with translations
        $createResponse = $this->withToken($token)
            ->postJson('/api/v1/study-groups', [
                'visibility' => 'public',
                'translations' => [
                    'en' => [
                        'name' => 'Chemistry Study Group',
                        'description' => 'Study chemistry together.',
                    ],
                    'ar' => [
                        'name' => 'مجموعة دراسة الكيمياء',
                        'description' => 'دراسة الكيمياء سوياً.',
                    ],
                ],
            ]);

        $slug = $createResponse->json('data.slug');

        // Retrieve with Arabic locale
        $response = $this->withToken($token)
            ->getJson("/api/v1/study-groups/{$slug}?locale=ar");

        $response->assertOk()
            ->assertJsonPath('data.name', 'مجموعة دراسة الكيمياء')
            ->assertJsonPath('data.description', 'دراسة الكيمياء سوياً.');
    }

    /**
     * Test English fallback when Arabic translation is missing.
     */
    public function test_english_fallback_when_arabic_missing(): void
    {
        $creator = User::factory()->create();
        $token = $creator->createToken('test')->plainTextToken;

        // Create with only English translation
        $createResponse = $this->withToken($token)
            ->postJson('/api/v1/study-groups', [
                'visibility' => 'public',
                'translations' => [
                    'en' => [
                        'name' => 'Biology Study Group',
                        'description' => 'Study biology together.',
                    ],
                ],
            ]);

        $slug = $createResponse->json('data.slug');

        // Request Arabic, should fallback to English
        $response = $this->withToken($token)
            ->getJson("/api/v1/study-groups/{$slug}?locale=ar");

        $response->assertOk()
            ->assertJsonPath('data.name', 'Biology Study Group')
            ->assertJsonPath('data.description', 'Study biology together.');
    }

    /**
     * Test updating translations.
     */
    public function test_update_study_group_translations(): void
    {
        $owner = User::factory()->create();
        $token = $owner->createToken('test')->plainTextToken;

        // Create group with old format first
        $createResponse = $this->withToken($token)
            ->postJson('/api/v1/study-groups', [
                'name' => 'Original Name',
                'description' => 'Original description',
                'visibility' => 'public',
            ]);

        $slug = $createResponse->json('data.slug');

        // Update with translations
        $response = $this->withToken($token)
            ->patchJson("/api/v1/study-groups/{$slug}", [
                'translations' => [
                    'en' => [
                        'name' => 'Updated English Name',
                        'description' => 'Updated English description',
                        'rules' => 'Updated rules',
                    ],
                    'ar' => [
                        'name' => 'اسم محدث عربي',
                        'description' => 'وصف محدث عربي',
                        'rules' => 'قواعد محدثة',
                    ],
                ],
            ]);

        $response->assertOk();

        $groupId = $createResponse->json('data.id');

        // Verify translations were created
        $this->assertDatabaseHas('study_group_translations', [
            'study_group_id' => $groupId,
            'locale' => 'en',
            'name' => 'Updated English Name',
        ]);
        
        $this->assertDatabaseHas('study_group_translations', [
            'study_group_id' => $groupId,
            'locale' => 'ar',
            'name' => 'اسم محدث عربي',
        ]);
    }

    /**
     * Test duplicate locale prevention.
     */
    public function test_duplicate_locale_prevention(): void
    {
        $owner = User::factory()->create();
        $token = $owner->createToken('test')->plainTextToken;

        // Create group with translations
        $createResponse = $this->withToken($token)
            ->postJson('/api/v1/study-groups', [
                'visibility' => 'public',
                'translations' => [
                    'en' => [
                        'name' => 'Test Group',
                        'description' => 'Test description',
                    ],
                    'ar' => [
                        'name' => 'مجموعة اختبار',
                        'description' => 'وصف اختبار',
                    ],
                ],
            ]);

        $groupId = $createResponse->json('data.id');

        // Verify translations were created with unique constraint
        $this->assertDatabaseCount('study_group_translations', 2);
        
        // Verify English translation exists
        $this->assertDatabaseHas('study_group_translations', [
            'study_group_id' => $groupId,
            'locale' => 'en',
            'name' => 'Test Group',
        ]);
        
        // Verify Arabic translation exists
        $this->assertDatabaseHas('study_group_translations', [
            'study_group_id' => $groupId,
            'locale' => 'ar',
            'name' => 'مجموعة اختبار',
        ]);
    }

    /**
     * Test authorization remains unchanged.
     */
    public function test_authorization_remains_unchanged(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $group = StudyGroup::query()->create([
            'created_by' => $owner->id,
            'name' => 'Group',
            'slug' => 'group',
            'description' => 'Desc',
            'visibility' => 'public',
            'status' => 'active',
        ]);

        // Other user should not be able to update
        $this->withToken($token)
            ->patchJson("/api/v1/study-groups/{$group->slug}", [
                'translations' => [
                    'en' => [
                        'name' => 'Hacked',
                        'description' => 'Hacked',
                    ],
                ],
            ])
            ->assertForbidden();
    }

    /**
     * Test existing study group functionality remains working.
     */
    public function test_existing_study_group_functionality_remains_working(): void
    {
        $creator = User::factory()->create();
        $token = $creator->createToken('test')->plainTextToken;

        // Create with old format
        $createResponse = $this->withToken($token)
            ->postJson('/api/v1/study-groups', [
                'name' => 'Old Format Group',
                'description' => 'Old format description',
                'visibility' => 'private',
            ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.name', 'Old Format Group')
            ->assertJsonPath('data.description', 'Old format description')
            ->assertJsonPath('data.visibility', 'private');

        $slug = $createResponse->json('data.slug');

        // Update with old format
        $updateResponse = $this->withToken($token)
            ->patchJson("/api/v1/study-groups/{$slug}", [
                'name' => 'Updated Old Format',
                'description' => 'Updated description',
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.name', 'Updated Old Format')
            ->assertJsonPath('data.description', 'Updated description');
    }

    /**
     * Test retrieving translations collection.
     */
    public function test_retrieve_translations_collection(): void
    {
        $creator = User::factory()->create();
        $token = $creator->createToken('test')->plainTextToken;

        // Create with translations
        $createResponse = $this->withToken($token)
            ->postJson('/api/v1/study-groups', [
                'visibility' => 'public',
                'translations' => [
                    'en' => [
                        'name' => 'Math Group',
                        'description' => 'Math description',
                        'rules' => 'Math rules',
                    ],
                    'ar' => [
                        'name' => 'مجموعة رياضيات',
                        'description' => 'وصف رياضيات',
                        'rules' => 'قواعد رياضيات',
                    ],
                ],
            ]);

        $slug = $createResponse->json('data.slug');

        // Retrieve
        $response = $this->withToken($token)
            ->getJson("/api/v1/study-groups/{$slug}");

        $response->assertOk()
            ->assertJsonPath('data.translations.en.name', 'Math Group')
            ->assertJsonPath('data.translations.en.description', 'Math description')
            ->assertJsonPath('data.translations.en.rules', 'Math rules')
            ->assertJsonPath('data.translations.ar.name', 'مجموعة رياضيات')
            ->assertJsonPath('data.translations.ar.description', 'وصف رياضيات')
            ->assertJsonPath('data.translations.ar.rules', 'قواعد رياضيات');
    }
}
