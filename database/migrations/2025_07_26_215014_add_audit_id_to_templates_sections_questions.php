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
        // Add audit_id to templates
        if (!Schema::hasColumn('templates', 'audit_id')) {
            Schema::table('templates', function (Blueprint $table) {
                $table->foreignId('audit_id')->nullable()->after('review_type_id')->constrained('audits')->onDelete('cascade');
            });
        }
        
        // Add audit_id to sections
        if (!Schema::hasColumn('sections', 'audit_id')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->foreignId('audit_id')->nullable()->after('template_id')->constrained('audits')->onDelete('cascade');
            });
        }
        
        // Add audit_id to questions
        if (!Schema::hasColumn('questions', 'audit_id')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->foreignId('audit_id')->nullable()->after('section_id')->constrained('audits')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove audit_id from templates
        if (Schema::hasColumn('templates', 'audit_id')) {
            Schema::table('templates', function (Blueprint $table) {
                $table->dropForeign(['audit_id']);
                $table->dropColumn('audit_id');
            });
        }
        
        // Remove audit_id from sections
        if (Schema::hasColumn('sections', 'audit_id')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->dropForeign(['audit_id']);
                $table->dropColumn('audit_id');
            });
        }
        
        // Remove audit_id from questions
        if (Schema::hasColumn('questions', 'audit_id')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropForeign(['audit_id']);
                $table->dropColumn('audit_id');
            });
        }
    }
};
