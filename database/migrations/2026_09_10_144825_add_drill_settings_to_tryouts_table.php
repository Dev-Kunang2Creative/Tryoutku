<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * How many due questions are pulled into one daily session for this package.
     */
    public function up(): void
    {
        Schema::table('tryouts', function (Blueprint $table) {
            $table->unsignedSmallInteger('questions_per_session')->default(15)->after('duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('tryouts', function (Blueprint $table) {
            $table->dropColumn('questions_per_session');
        });
    }
};
