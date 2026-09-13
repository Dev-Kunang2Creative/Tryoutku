<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Login memakai username, bukan email. Aplikasi ini tidak pernah mengirim
     * surel, jadi kolom email dilonggarkan menjadi nullable dan hanya
     * disimpan sebagai catatan.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });

        $this->backfillUsernamesFromEmail();

        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->change();
            $table->string('email')->nullable()->change();
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }

    /**
     * Akun yang sudah ada memperoleh username dari bagian depan alamat
     * surelnya, sehingga tidak ada akun yang terkunci setelah migrasi.
     */
    private function backfillUsernamesFromEmail(): void
    {
        $taken = [];

        foreach (DB::table('users')->orderBy('id')->get(['id', 'email']) as $user) {
            $candidate = Str::of((string) $user->email)->before('@')->slug('_')->value();

            if ($candidate === '' || in_array($candidate, $taken, true)) {
                $candidate = 'user'.$user->id;
            }

            $taken[] = $candidate;

            DB::table('users')->where('id', $user->id)->update(['username' => $candidate]);
        }
    }
};
