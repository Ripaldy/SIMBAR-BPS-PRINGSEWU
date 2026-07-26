<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            if (!Schema::hasColumn('barang', 'harga_satuan')) {
                $table->decimal('harga_satuan', 15, 0)->nullable()->default(0)->after('satuan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            if (Schema::hasColumn('barang', 'harga_satuan')) {
                $table->dropColumn('harga_satuan');
            }
        });
    }
};
