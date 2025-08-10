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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique(); // Country code (ISO 3166-1 alpha-3)
            $table->string('iso_code', 2)->unique(); // ISO 3166-1 alpha-2 code
            $table->string('phone_code', 10)->nullable(); // Country calling code
            $table->string('currency', 3)->nullable(); // Currency code
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
