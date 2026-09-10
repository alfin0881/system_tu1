<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratSppd extends Model
{
    protected $table = 'surat_sppds';

    protected $fillable = [
        'surat_id',
        'surat_tugas_id',
        'tempat_berangkat',
        'tempat_tujuan',
        'tanggal_berangkat',
        'tanggal_kembali',
        'alat_transportasi',
        'pengikut',
        'maksud_perjalanan',
        'biaya_keterangan',
        'pejabat_pemberi_perintah_id',
    ];

    protected $casts = [
        'tanggal_berangkat' => 'date',
        'tanggal_kembali' => 'date',
    ];

    /** Surat SPPD itu sendiri (baris di tabel surats, kategori sppd) */
    public function surat()
    {
        return $this->belongsTo(Surat::class, 'surat_id');
    }

    /** Surat Tugas pasangannya (baris lain di tabel surats, kategori tugas) */
    public function suratTugas()
    {
        return $this->belongsTo(Surat::class, 'surat_tugas_id');
    }

    public function pejabatPemberiPerintah()
    {
        return $this->belongsTo(GuruKaryawan::class, 'pejabat_pemberi_perintah_id');
    }
}
