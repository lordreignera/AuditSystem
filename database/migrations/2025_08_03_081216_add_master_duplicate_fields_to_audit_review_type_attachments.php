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
            // Master-duplicate relationship
            $table->foreignId('master_attachment_id')->nullable()->after('review_type_id')
                  ->constrained('audit_review_type_attachments')->onDelete('cascade');
            
            // Duplicate numbering (1 = master, 2,3,4... = duplicates)
            $table->integer('duplicate_number')->default(1)->after('master_attachment_id');
            
            // Rename facility_name to location_name for better context
            $table->renameColumn('facility_name', 'location_name');
            
            // Add index for better performance with custom name
            $table->index(['audit_id', 'review_type_id', 'master_attachment_id'], 'arta_audit_review_master_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_review_type_attachments', function (Blueprint $table) {
            $table->dropForeign(['master_attachment_id']);
            $table->dropColumn(['master_attachment_id', 'duplicate_number']);
            $table->dropIndex('arta_audit_review_master_idx');
            $table->renameColumn('location_name', 'facility_name');
        });
    }
};
