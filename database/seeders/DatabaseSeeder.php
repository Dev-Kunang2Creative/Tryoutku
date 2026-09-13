<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Kata sandi di bawah hanya untuk pengembangan di komputer sendiri.
     * Kata sandi sungguhan di server produksi ditetapkan terpisah dan sengaja
     * tidak pernah ditulis di repositori ini.
     */
    public function run(): void
    {
        // Pengelola soal (orang tua / guru): punya akses panel admin.
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Pengelola Soal',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // Satu-satunya pelajar yang progresnya dilacak aplikasi ini.
        User::updateOrCreate(
            ['username' => 'malika'],
            [
                'name' => 'Malikha Zhafira Firdaus',
                'password' => Hash::make('password123'),
                'role' => 'user',
            ]
        );

        $this->call([
            IpaQuestionSeeder::class,
            EnglishQuestionSeeder::class,
        ]);
    }
}
