<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemberitahuan extends Model
{
    protected $table = 'pemberitahuan';
    protected $primaryKey = 'id_pemberitahuan';
    
    protected $fillable = [
        'id_user',
        'nama_barang',
        'deskripsi',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
