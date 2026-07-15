<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_masuk', function (Blueprint $table) {
            $table->id('id_barang_masuk');
            $table->unsignedBigInteger('id_barang');
            $table->integer('jumlah_masuk');
            $table->timestamp('waktu_masuk')->useCurrent();
            $table->unsignedBigInteger('id_user')->nullable(); // admin yg menginput
            $table->timestamps();

            $table->foreign('id_barang')->references('id_barang')->on('barang')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_masuk');
    }
};
