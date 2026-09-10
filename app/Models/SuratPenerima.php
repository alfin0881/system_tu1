<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratPenerima extends Model
{
    protected $fillable = [
        'surat_id',
        'guru_id',
        'siswa_id',
        'nama_custom',
        'jabatan_custom',
        'keterangan',
    ];

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    public function guru()
    {
        return $this->belongsTo(GuruKaryawan::class, 'guru_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /** Nama penerima otomatis: dari data guru/siswa terkait, atau nama_custom jika pihak luar */
    public function getNamaPenerimaAttribute(): string
    {
        return $this->guru?->nama_lengkap
            ?? $this->siswa?->nama
            ?? $this->nama_custom
            ?? '-';
    }
}
