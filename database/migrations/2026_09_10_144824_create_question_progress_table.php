<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks long-term mastery of a single question, independent of any one session.
     * This is what makes questions roll over to the next day until they are mastered.
     */
    public function up(): void
    {
        Schema::create('question_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();

            // Consecutive correct answers; the question is mastered once this reaches the threshold.
            $table->unsignedTinyInteger('correct_streak')->default(0);
            $table->unsignedInteger('times_correct')->default(0);
            $table->unsignedInteger('times_wrong')->default(0);

            // The day the question becomes eligible again. Null once mastered.
            $table->date('due_on')->nullable();
            $table->date('last_answered_on')->nullable();
            $table->timestamp('mastered_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'question_id']);
            $table->index(['user_id', 'mastered_at', 'due_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_progress');
    }
};
