<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pengajuan')) {
            Schema::create('pengajuan', function (Blueprint $table) {
                $table->bigIncrements('id_pengajuan');
                $table->unsignedBigInteger('id_user');
                $table->unsignedBigInteger('id_barang');
                $table->integer('jumlah_diminta');
                $table->integer('jumlah_disetujui')->nullable();
                $table->string('status_pengajuan')->default('pending'); // pending, approved, rejected
                $table->text('alasan')->nullable();
                $table->text('catatan_admin')->nullable();
                $table->timestamp('waktu_pengajuan')->useCurrent();
                $table->timestamp('waktu_diproses')->nullable();
                $table->unsignedBigInteger('diproses_oleh')->nullable();
                $table->timestamps();

                $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
                $table->foreign('id_barang')->references('id_barang')->on('barang')->onDelete('cascade');
            });
        } else {
            Schema::table('pengajuan', function (Blueprint $table) {
                if (!Schema::hasColumn('pengajuan', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan');
    }
};
