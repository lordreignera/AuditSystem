<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Add parent columns to templates (skip is_default if it exists)
    Schema::table('templates', function (Blueprint $table) {
        if (!Schema::hasColumn('templates', 'parent_template_id')) {
            $table->foreignId('parent_template_id')->nullable()->constrained('templates')->onDelete('cascade');
        }
        if (!Schema::hasColumn('templates', 'is_default')) {
            $table->boolean('is_default')->default(true);
        }
    });

    // Add parent columns to sections
    Schema::table('sections', function (Blueprint $table) {
        if (!Schema::hasColumn('sections', 'parent_section_id')) {
            $table->foreignId('parent_section_id')->nullable()->constrained('sections')->onDelete('cascade');
        }
    });

    // Add parent columns to questions
    Schema::table('questions', function (Blueprint $table) {
        if (!Schema::hasColumn('questions', 'parent_question_id')) {
            $table->foreignId('parent_question_id')->nullable()->constrained('questions')->onDelete('cascade');
        }
    });

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
