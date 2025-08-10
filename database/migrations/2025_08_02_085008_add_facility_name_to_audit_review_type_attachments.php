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
        Schema::table('audit_review_type_attachments', function (Blueprint $table) {
            // Make file fields nullable to support template attachments
            $table->string('file_name')->nullable()->change();
            $table->string('file_path')->nullable()->change();
            
            // Add template relationship for non-file attachments
            $table->foreignId('template_id')->nullable()->after('review_type_id')->constrained()->onDelete('cascade');
            $table->enum('attachment_type', ['file', 'template'])->default('file')->after('template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_review_type_attachments', function (Blueprint $table) {
            // Remove template relationship
            if (Schema::hasColumn('audit_review_type_attachments', 'template_id')) {
                $table->dropForeign(['template_id']);
                $table->dropColumn(['template_id', 'attachment_type']);
            }
            
            // Make file fields required again
            $table->string('file_name')->nullable(false)->change();
            $table->string('file_path')->nullable(false)->change();
        });
    }
};
