<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. Tambah kolom baru ke barang DULU ───
        Schema::table('barang', function (Blueprint $table) {
            if (!Schema::hasColumn('barang', 'kode_kategori')) {
                $table->string('kode_kategori')->nullable()->after('kode_barang');
            }
            if (!Schema::hasColumn('barang', 'nama_kategori')) {
                $table->string('nama_kategori')->nullable()->after('kode_kategori');
            }
        });

        // ─── 2. Salin data dari kategoris ke barang ───
        if (Schema::hasTable('kategoris') && Schema::hasColumn('barang', 'kategori_id')) {
            DB::statement("
                UPDATE barang
                SET kode_kategori = k.kode_kategori,
                    nama_kategori  = k.nama_kategori
                FROM kategoris k
                WHERE barang.kategori_id = k.id_kategori
            ");
        }

        // ─── 3. Hapus kolom lama & foreign key ───
        Schema::table('barang', function (Blueprint $table) {
            if (Schema::hasColumn('barang', 'kategori_id')) {
                $table->dropForeign(['kategori_id']);
                $table->dropColumn('kategori_id');
            }
            if (Schema::hasColumn('barang', 'kategori')) {
                $table->dropColumn('kategori');
            }
        });

        // ─── 4. Drop tabel kategoris ───
        Schema::dropIfExists('kategoris');
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            if (!Schema::hasColumn('barang', 'kategori')) {
                $table->string('kategori')->nullable();
            }
            if (Schema::hasColumn('barang', 'kode_kategori')) {
                $table->dropColumn('kode_kategori');
            }
            if (Schema::hasColumn('barang', 'nama_kategori')) {
                $table->dropColumn('nama_kategori');
            }
        });
    }
};
