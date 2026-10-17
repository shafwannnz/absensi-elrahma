<?php

namespace Database\Seeders;

use App\Models\CampusSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Pengaturan kampus default (koordinat & jam dari config/absensi.php)
        CampusSetting::current();

        // Akun admin awal -> WAJIB ganti password setelah login pertama.
        User::updateOrCreate(
            ['email' => 'admin@elrahma.ac.id'],
            ['name' => 'Administrator', 'password' => 'Admin#12345', 'role' => User::ROLE_ADMIN]
        );

        // Data contoh mahasiswa (password: "password"). Wajah didaftarkan lewat kamera.
        $students = [
            ['2024001', 'Ahmad Fauzi',    'ahmad@student.elrahma.ac.id', 'Teknik Informatika'],
            ['2024002', 'Siti Nurhaliza', 'siti@student.elrahma.ac.id',  'Sistem Informasi'],
            ['2024003', 'Budi Santoso',   'budi@student.elrahma.ac.id',  'Teknik Informatika'],
        ];

        foreach ($students as [$nim, $name, $email, $prodi]) {
            User::updateOrCreate(
                ['nim' => $nim],
                ['name' => $name, 'email' => $email, 'study_program' => $prodi,
                 'password' => 'password', 'role' => User::ROLE_STUDENT]
            );
        }
    }
}
