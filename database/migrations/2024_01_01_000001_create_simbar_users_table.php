<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buat tabel users jika belum ada (sesuaikan dengan struktur lama)
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->bigIncrements('id_user');
                $table->string('nama_lengkap');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role')->default('pegawai'); // admin, pegawai, pemimpin
                $table->string('nip')->nullable();
                $table->string('no_telepon')->nullable();
                $table->string('jabatan')->nullable();
                $table->string('divisi')->nullable();
                $table->string('foto_profil')->nullable();
                $table->boolean('is_verified')->default(true);
                $table->rememberToken();
                $table->timestamps();
            });
        } else {
            // Tambahkan kolom yang mungkin belum ada
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'remember_token')) {
                    $table->rememberToken();
                }
                if (!Schema::hasColumn('users', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
