<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $fillable = [
        'nama',
        'semester',
        'status',
    ];

    /** Label tampilan gabungan, mis. "2025/2026 Ganjil". */
    public function getLabelAttribute(): string
    {
        return trim("{$this->nama} {$this->semester}");
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

    /** Siswa yang angkatannya (Buku Induk) adalah tahun ajaran ini. */
    public function siswaMasuk()
    {
        return $this->hasMany(Siswa::class, 'tahun_ajaran_masuk_id');
    }

    public function riwayatKelas()
    {
        return $this->hasMany(RiwayatKelas::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public static function getAktif(): ?self
    {
        return static::aktif()->first();
    }
}
