<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiSiswa extends Model
{
    protected $table = 'mutasi_siswas';

    protected $fillable = [
        'siswa_id',
        'jenis_mutasi',
        'tanggal',
        'asal_sekolah',
        'tujuan_sekolah',
        'alasan',
        'no_surat',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
