<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('barang')) {
            Schema::create('barang', function (Blueprint $table) {
                $table->bigIncrements('id_barang');
                $table->string('nama_barang');
                $table->string('kategori')->nullable();
                $table->string('satuan')->nullable();
                $table->integer('stok_aktual')->default(0);
                $table->integer('stok_minimum')->default(5);
                $table->boolean('is_auto_approve')->default(false);
                $table->string('foto_barang')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('barang', function (Blueprint $table) {
                if (!Schema::hasColumn('barang', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
