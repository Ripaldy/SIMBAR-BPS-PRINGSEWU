<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'id_barang';

    protected $fillable = [
        'nama_barang',
        'satuan',
        'stok_aktual',
        'stok_minimum',
        'is_auto_approve',
        'foto_barang',
    ];

    protected $casts = [
        'is_auto_approve' => 'boolean',
        'stok_aktual'     => 'integer',
        'stok_minimum'    => 'integer',
    ];

    public function isKritis(): bool
    {
        return $this->stok_aktual <= $this->stok_minimum;
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto_barang
            ? asset('uploads/' . $this->foto_barang)
            : null;
    }

    public function getKodeAttribute(): string
    {
        return 'BRG-' . str_pad($this->id_barang, 3, '0', STR_PAD_LEFT);
    }

    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'id_barang');
    }
}
