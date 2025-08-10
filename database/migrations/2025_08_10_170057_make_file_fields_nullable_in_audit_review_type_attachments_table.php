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
            // Make file fields nullable since this table is used for review type attachments, not just file attachments
            $table->string('file_name')->nullable()->change();
            $table->string('file_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_review_type_attachments', function (Blueprint $table) {
            // Revert back to required fields
            $table->string('file_name')->nullable(false)->change();
            $table->string('file_path')->nullable(false)->change();
        });
    }
};
