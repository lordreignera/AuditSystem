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
            // Drop the old unique constraint that only includes audit_id + question_id
            $table->dropUnique(['audit_id', 'question_id']);
            
            // Add new unique constraint that includes attachment_id for proper isolation
            $table->unique(['audit_id', 'attachment_id', 'question_id', 'created_by'], 'responses_unique_per_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            // Drop the new constraint
            $table->dropUnique('responses_unique_per_location');
            
            // Restore the old constraint (though this might cause issues if data exists)
            $table->unique(['audit_id', 'question_id'], 'responses_audit_id_question_id_unique');
        });
    }
};
