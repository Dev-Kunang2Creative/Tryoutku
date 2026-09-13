<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sejak login berpindah ke username, alamat surel tidak lagi dipakai untuk
     * apa pun: aplikasi ini tidak pernah mengirim surel dan tidak punya alur
     * pemulihan kata sandi. Kolomnya dibuang agar tidak menyesatkan.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->dropColumn(['email', 'email_verified_at']);
        });
    }

    /**
     * Struktur kolom dikembalikan, tetapi alamat surel yang pernah tersimpan
     * tidak bisa dipulihkan oleh migrasi ini.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('username');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });
    }
};
