<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->string('nip_bps')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
