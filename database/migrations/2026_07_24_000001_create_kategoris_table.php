<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kategoris')) {
            Schema::create('kategoris', function (Blueprint $table) {
                $table->bigIncrements('id_kategori');
                $table->string('kode_kategori')->unique(); // contoh: 1010301001
                $table->string('nama_kategori');           // contoh: Alat Tulis
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kategoris');
    }
};
