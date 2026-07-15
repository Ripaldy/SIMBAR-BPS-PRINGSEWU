<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin default
        User::firstOrCreate(
            ['email' => 'admin@simbar.id'],
            [
                'nama_lengkap' => 'Administrator',
                'password'     => Hash::make('admin123'),
                'role'         => 'admin',
                'jabatan'      => 'Kepala Bagian Umum',
                'divisi'       => 'Administrasi',
                'is_verified'  => true,
            ]
        );

        // Pemimpin default
        User::firstOrCreate(
            ['email' => 'pimpinan@simbar.id'],
            [
                'nama_lengkap' => 'Kepala BPS',
                'password'     => Hash::make('pimpin123'),
                'role'         => 'pemimpin',
                'jabatan'      => 'Kepala BPS',
                'divisi'       => 'Pimpinan',
                'is_verified'  => true,
            ]
        );

        // Pegawai default
        User::firstOrCreate(
            ['email' => 'pegawai@simbar.id'],
            [
                'nama_lengkap' => 'Pegawai Contoh',
                'password'     => Hash::make('pegawai123'),
                'role'         => 'pegawai',
                'jabatan'      => 'Staf',
                'divisi'       => 'Statistik Sosial',
                'is_verified'  => true,
            ]
        );
    }
}
