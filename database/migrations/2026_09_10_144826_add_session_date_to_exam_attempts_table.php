<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The calendar day a session belongs to, used to enforce one session
     * per package per day.
     */
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->date('session_date')->nullable()->after('tryout_id');

            $table->index(['user_id', 'tryout_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'tryout_id', 'session_date']);
            $table->dropColumn('session_date');
        });
    }
};
