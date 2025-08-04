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
        Schema::table('responses', function (Blueprint $table) {
            // Add attachment_id to track which duplicate/master this response belongs to
            $table->foreignId('attachment_id')->nullable()->after('audit_id')
                  ->constrained('audit_review_type_attachments')->onDelete('cascade');
            
            // Add index for better performance
            $table->index(['audit_id', 'attachment_id', 'question_id'], 'resp_audit_attach_quest_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            $table->dropForeign(['attachment_id']);
            $table->dropIndex('resp_audit_attach_quest_idx');
            $table->dropColumn('attachment_id');
        });
    }
};
