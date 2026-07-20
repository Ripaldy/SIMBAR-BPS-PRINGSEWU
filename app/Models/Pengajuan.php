<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengajuan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengajuan';
    protected $primaryKey = 'id_pengajuan';

    protected $fillable = [
        'id_user',
        'id_barang',
        'jumlah_diminta',
        'jumlah_disetujui',
        'status_pengajuan',
        'alasan',
        'catatan_admin',
        'waktu_pengajuan',
        'waktu_diproses',
        'diproses_oleh',
    ];

    protected $casts = [
        'waktu_pengajuan' => 'datetime',
        'waktu_diproses'  => 'datetime',
        'jumlah_diminta'  => 'integer',
        'jumlah_disetujui' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    public function prosesOleh()
    {
        return $this->belongsTo(User::class, 'diproses_oleh', 'id_user');
    }
}
