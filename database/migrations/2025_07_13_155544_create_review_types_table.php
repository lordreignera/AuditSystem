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
        Schema::create('review_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // The name of the review type (e.g., Nation, Province, District)
            $table->text('description')->nullable(); // A brief description of the review type
            $table->boolean('is_active')->default(true); // Whether this review type is active
            $table->timestamps();
            
            // Add unique constraint for name
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_types');
    }
};
