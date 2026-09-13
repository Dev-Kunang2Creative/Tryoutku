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
            $table->foreignId('tryout_id')->constrained('tryouts')->cascadeOnDelete();
            $table->longText('question_text');
            $table->string('question_image')->nullable();
            $table->longText('explanation')->nullable(); // Pembahasan ilmiah step-by-step
            $table->decimal('score_weight', 5, 2)->default(10.00);
            $table->integer('order')->default(0);
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
