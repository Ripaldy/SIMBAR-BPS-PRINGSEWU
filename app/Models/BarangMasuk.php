<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuk';
    protected $primaryKey = 'id_barang_masuk';

    protected $fillable = [
        'id_barang',
        'jumlah_masuk',
        'waktu_masuk',
        'id_user'
    ];

    protected $casts = [
        'waktu_masuk' => 'datetime',
        'jumlah_masuk' => 'integer',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
