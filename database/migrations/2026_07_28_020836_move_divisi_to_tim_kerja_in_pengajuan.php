<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->string('tim_kerja')->nullable()->after('status_pengajuan');
        });

        // Populate tim_kerja with data from users table
        $pengajuans = DB::table('pengajuan')
            ->join('users', 'pengajuan.id_user', '=', 'users.id_user')
            ->select('pengajuan.id_pengajuan', 'users.divisi')
            ->get();
            
        foreach ($pengajuans as $p) {
            if ($p->divisi) {
                DB::table('pengajuan')
                    ->where('id_pengajuan', $p->id_pengajuan)
                    ->update(['tim_kerja' => $p->divisi]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('divisi');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('divisi')->nullable();
        });

        $users = DB::table('users')
            ->join('pengajuan', 'users.id_user', '=', 'pengajuan.id_user')
            ->select('users.id_user', 'pengajuan.tim_kerja')
            ->whereNotNull('pengajuan.tim_kerja')
            ->get();
            
        foreach ($users as $u) {
            DB::table('users')
                ->where('id_user', $u->id_user)
                ->update(['divisi' => $u->tim_kerja]);
        }

        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropColumn('tim_kerja');
        });
    }
};
