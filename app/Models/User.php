<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // admin | tu | kepsek
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTu(): bool
    {
        return $this->role === 'tu';
    }

    public function isKepsek(): bool
    {
        return $this->role === 'kepsek';
    }

    /** Surat-surat yang dibuat/di-entry oleh user ini */
    public function surat()
    {
        return $this->hasMany(Surat::class, 'created_by');
    }
}
