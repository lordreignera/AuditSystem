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
        // Add columns to templates table if they do not exist
        Schema::table('templates', function (Blueprint $table) {
            if (!Schema::hasColumn('templates', 'parent_template_id')) {
                $table->foreignId('parent_template_id')->nullable()->constrained('templates')->onDelete('cascade');
            }
            if (!Schema::hasColumn('templates', 'is_default')) {
                $table->boolean('is_default')->default(true);
            }
        });

        // Add columns to sections table if they do not exist
        Schema::table('sections', function (Blueprint $table) {
            if (!Schema::hasColumn('sections', 'parent_section_id')) {
                $table->foreignId('parent_section_id')->nullable()->constrained('sections')->onDelete('cascade');
            }
        });

        // Add columns to questions table if they do not exist
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'parent_question_id')) {
                $table->foreignId('parent_question_id')->nullable()->constrained('questions')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            if (Schema::hasColumn('templates', 'parent_template_id')) {
                $table->dropForeign(['parent_template_id']);
                $table->dropColumn('parent_template_id');
            }
            if (Schema::hasColumn('templates', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });

        Schema::table('sections', function (Blueprint $table) {
            if (Schema::hasColumn('sections', 'parent_section_id')) {
                $table->dropForeign(['parent_section_id']);
                $table->dropColumn('parent_section_id');
            }
        });

        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'parent_question_id')) {
                $table->dropForeign(['parent_question_id']);
                $table->dropColumn('parent_question_id');
            }
        });
    }
};