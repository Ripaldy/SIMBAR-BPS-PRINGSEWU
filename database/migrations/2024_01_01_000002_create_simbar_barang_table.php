<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->bigIncrements('id_barang');
            $table->string('kode_barang')->nullable();
            $table->string('kode_kategori')->nullable();
            $table->string('nama_kategori')->nullable();
            $table->string('nama_barang');
            $table->string('satuan')->nullable();
            $table->decimal('harga_satuan', 15, 0)->nullable()->default(0);
            $table->integer('stok_aktual')->default(0);
            $table->integer('stok_minimum')->default(5);
            $table->boolean('is_auto_approve')->default(false);
            $table->string('foto_barang')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
