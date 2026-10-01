<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('study_group_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('study_group_id')->constrained('study_groups');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('role', ['owner', 'moderator', 'member']);
            $table->enum('status', ['active', 'banned', 'left']);
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->unique(['study_group_id', 'user_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_group_members');
    }
};
