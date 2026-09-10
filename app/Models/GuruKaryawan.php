<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruKaryawan extends Model
{
    protected $fillable = [
        'nip_niy',
        'no_ktp',
        'nama',
        'gelar_akademik',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'jabatan',
        'mengajar',
        'golongan_pangkat',
        'alamat',
        'kelurahan',
        'kecamatan',
        'pendidikan_terakhir',
        'nama_ibu_kandung',
        'no_hp',
        'email',
        'status',
        'status_kepegawaian',
        'tmt_madrasah',
        'tmt_pns',
        'tmt_golongan',
        'foto',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tmt_madrasah' => 'date',
        'tmt_pns' => 'date',
        'tmt_golongan' => 'date',
    ];

    public function kelasWali()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    /**
     * Nama dan gelar akademik kini disimpan di kolom terpisah (nama,
     * gelar_akademik). Accessor ini menggabungkan keduanya supaya template
     * surat yang lama (memakai ->nama_lengkap) tetap berfungsi tanpa perlu
     * diubah satu per satu.
     */
    public function getNamaLengkapAttribute(): string
    {
        return trim($this->nama.($this->gelar_akademik ? ', '.$this->gelar_akademik : ''));
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
