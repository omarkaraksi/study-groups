<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('study_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('cover_image')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->foreignId('academic_level_id')->nullable()->constrained('academic_levels');
            $table->enum('status', ['draft', 'preview', 'active', 'suspended', 'archived']);
            $table->enum('visibility', ['public', 'private']);
            $table->unsignedInteger('max_members')->nullable();
            $table->text('rules')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_by');
            $table->index('category_id');
            $table->index('academic_level_id');
            $table->index('status');
            $table->index('visibility');
        });

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement(
                'ALTER TABLE study_groups ADD CONSTRAINT study_groups_max_members_positive CHECK (max_members IS NULL OR max_members > 0)'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_groups');
    }
};
