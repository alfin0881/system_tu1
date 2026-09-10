<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    // ditulis eksplisit karena pluralizer Laravel bisa salah menebak kata "Kelas"
    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'wali_kelas_id',
        'tahun_ajaran_id',
    ];

    public function waliKelas()
    {
        return $this->belongsTo(GuruKaryawan::class, 'wali_kelas_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

    public function siswaAktif()
    {
        return $this->hasMany(Siswa::class)->where('status', 'aktif');
    }
}
