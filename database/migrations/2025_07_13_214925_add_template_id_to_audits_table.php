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
        Schema::table('audits', function (Blueprint $table) {
            $table->foreignId('template_id')->nullable()->after('review_code')->constrained()->onDelete('set null');
            $table->foreignId('created_by')->after('template_id')->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['template_id', 'created_by']);
        });
    }
};
