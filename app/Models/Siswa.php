<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $fillable = [
        'nisn',
        'nik',
        'nis',
        'nis_lokal',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'alamat',
        'status_dalam_keluarga',
        'anak_ke',
        'jumlah_saudara_kandung',
        'no_kk',
        'kepala_keluarga',
        'nama_ayah',
        'status_ayah',
        'nik_ayah',
        'tempat_lahir_ayah',
        'tanggal_lahir_ayah',
        'pendidikan_terakhir_ayah',
        'pekerjaan_ayah',
        'penghasilan_ayah',
        'nama_ibu',
        'status_ibu',
        'nik_ibu',
        'tempat_lahir_ibu',
        'tanggal_lahir_ibu',
        'pendidikan_terakhir_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
        'nama_wali',
        'status_wali',
        'nik_wali',
        'tempat_lahir_wali',
        'tanggal_lahir_wali',
        'jenis_kelamin_wali',
        'pendidikan_terakhir_wali',
        'pekerjaan_wali',
        'penghasilan_wali',
        'no_hp_ortu',
        'rt_rw_jl',
        'dukuh',
        'desa_kelurahan',
        'kecamatan',
        'kabupaten_kota',
        'provinsi',
        'kode_pos',
        'jenis_sekolah',
        'status_sekolah',
        'npsn_nsm',
        'nama_sekolah',
        'status_kepemilikan_kip',
        'no_kip',
        'pondok_pesantren',
        'kelas_id',
        'tahun_ajaran_masuk_id',
        'status',
        'tanggal_masuk',
        'foto',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_lahir_ayah' => 'date',
        'tanggal_lahir_ibu' => 'date',
        'tanggal_lahir_wali' => 'date',
        'tanggal_masuk' => 'date',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Angkatan siswa: tahun ajaran saat ia PERTAMA KALI didaftarkan lewat
     * Buku Induk. Permanen — beda dengan kelas()->tahunAjaran yang mengikuti
     * tahun ajaran kelasnya SAAT INI (berubah tiap kenaikan kelas).
     */
    public function tahunAjaranMasuk()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_masuk_id');
    }

    public function mutasi()
    {
        return $this->hasMany(MutasiSiswa::class);
    }

    public function riwayatKelas()
    {
        return $this->hasMany(RiwayatKelas::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeAlumni($query)
    {
        return $query->where('status', 'alumni');
    }
}
