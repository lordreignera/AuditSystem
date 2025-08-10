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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->onDelete('cascade');

            $table->text('question_text');

            $table->enum('response_type', [
                'text',
                'textarea',
                'yes_no',
                'select',
                'number',
                'date',
                'table'
            ])->default('text');

            $table->json('options')->nullable(); // For select/yes_no options
            $table->json('table_structure')->nullable(); // For table response_type

            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
