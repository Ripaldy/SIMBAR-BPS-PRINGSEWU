<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $timestamps = true;

    protected $fillable = [
        'nama_lengkap',
        'email',
        'password',
        'role',
        'nip',
        'nip_bps',
        'no_telepon',
        'jabatan',
        'divisi',
        'foto_profil',
        'is_verified',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    // Wajib untuk Laravel Auth dengan custom primary key
    public function getAuthIdentifierName(): string
    {
        return 'id_user';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->id_user;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPegawai(): bool
    {
        return $this->role === 'pegawai';
    }

    public function isPemimpin(): bool
    {
        return $this->role === 'pemimpin';
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto_profil
            ? asset('uploads/' . $this->foto_profil)
            : null;
    }

    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'id_user', 'id_user');
    }
}

