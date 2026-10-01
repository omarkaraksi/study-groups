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
        Schema::create('study_group_subjects', function (Blueprint $table): void {
            $table->foreignId('study_group_id')->constrained('study_groups');
            $table->foreignId('subject_id')->constrained('subjects');

            $table->primary(['study_group_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_group_subjects');
    }
};
