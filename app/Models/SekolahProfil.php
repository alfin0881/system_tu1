<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SekolahProfil extends Model
{
    protected $fillable = [
        'nama_sekolah',
        'logo',
        'lembaga_induk',
        'nama_yayasan',
        'dasar_hukum',
        'akreditasi',
        'nomor_izin',
        'alamat',
        'telepon',
        'email',
        'website',
    ];

    /**
     * Data sekolah didesain sebagai singleton (idealnya 1 baris).
     * Dipakai di semua Blade kop surat: {{ SekolahProfil::aktif()->nama_sekolah }}
     */
    public static function aktif(): ?self
    {
        return static::first();
    }

    /** Baris alamat + telepon digabung, dipakai di kop surat & preview. */
    public function getAlamatLengkapAttribute(): string
    {
        $alamat = trim((string) $this->alamat);

        return $this->telepon ? trim("{$alamat} Telp. {$this->telepon}") : $alamat;
    }
}
