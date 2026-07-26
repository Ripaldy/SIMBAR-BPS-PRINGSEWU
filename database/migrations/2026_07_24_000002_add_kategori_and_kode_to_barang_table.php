<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            if (!Schema::hasColumn('barang', 'kode_barang')) {
                // Kode barang BPS, contoh: 000001
                $table->string('kode_barang')->nullable()->after('id_barang');
            }
            if (!Schema::hasColumn('barang', 'kategori_id')) {
                // Foreign key ke tabel kategoris
                $table->unsignedBigInteger('kategori_id')->nullable()->after('kode_barang');
                $table->foreign('kategori_id')->references('id_kategori')->on('kategoris')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            if (Schema::hasColumn('barang', 'kategori_id')) {
                $table->dropForeign(['kategori_id']);
                $table->dropColumn('kategori_id');
            }
            if (Schema::hasColumn('barang', 'kode_barang')) {
                $table->dropColumn('kode_barang');
            }
        });
    }
};
