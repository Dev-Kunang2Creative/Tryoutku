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
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tryout_id')->constrained('tryouts')->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('deadline_at');
            $table->dateTime('completed_at')->nullable();
            $table->string('status')->default('in_progress'); // 'in_progress', 'completed', 'expired'
            $table->decimal('total_score', 7, 2)->default(0.00);
            $table->decimal('max_score', 7, 2)->default(0.00);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->boolean('is_passed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
