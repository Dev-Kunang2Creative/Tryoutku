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
        Schema::create('tryouts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('subject')->default('Olimpiade Sains Nasional'); // Bidang OSN
            $table->string('session_type')->default('Simulasi Latihan'); // e.g. Latihan Mandiri, Simulasi Waktu Penuh
            $table->integer('duration_minutes')->default(30);
            $table->decimal('passing_score', 5, 2)->default(70.00);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tryouts');
    }
};
