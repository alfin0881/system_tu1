<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisSuratSeeder extends Seeder
{
    /**
     * Seeder ini hanya mengisi data master (nama, kode, kategori, format
     * nomor). Setiap jenis surat WAJIB dilengkapi file template .docx lewat
     * menu Jenis Surat > Ubah setelah seeding, karena kop surat & isi surat
     * sekarang sepenuhnya ditentukan dari file docx yang diupload
     * (placeholder ${...} di dalamnya otomatis menjadi kolom isian form).
     * Nomor surat memakai format FIX '{nomor}/MTs.18/{kode}/L.PM/{bulan_romawi}/{tahun}'
     * (lihat SuratController::FORMAT_NOMOR_FIX) — kolom format_nomor di
     * bawah ini tidak dipakai lagi untuk generate nomor, hanya peninggalan
     * kolom lama.
     */
    public function run(): void
    {
        $now = now();

        DB::table('jenis_surats')->insert([
            [
                'nama'          => 'Surat Keterangan Aktif Siswa',
                'kode'          => 'SKET-AKTIF',
                'kategori'      => 'keterangan',
                                'deskripsi'     => 'Menerangkan status keaktifan seorang siswa.',
                'aktif'         => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'nama'          => 'Surat Keterangan Kelakuan Baik',
                'kode'          => 'SKET-SKB',
                'kategori'      => 'keterangan',
                                'deskripsi'     => 'Menerangkan perilaku/kelakuan baik seorang siswa.',
                'aktif'         => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'nama'          => 'Surat Keterangan Kelulusan',
                'kode'          => 'SKET-LULUS',
                'kategori'      => 'keterangan',
                                'deskripsi'     => 'Menerangkan kelulusan siswa (sementara sebelum ijazah terbit).',
                'aktif'         => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'nama'          => 'SK Pembagian Tugas Guru',
                'kode'          => 'SK-PTG',
                'kategori'      => 'keputusan',
                                'deskripsi'     => 'SK penetapan pembagian tugas mengajar/tugas tambahan guru per tahun ajaran.',
                'aktif'         => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'nama'          => 'SK Pengangkatan',
                'kode'          => 'SK-PENGANGKATAN',
                'kategori'      => 'keputusan',
                                'deskripsi'     => 'SK pengangkatan guru/karyawan pada suatu jabatan.',
                'aktif'         => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'nama'          => 'Surat Undangan Rapat Orang Tua',
                'kode'          => 'UND-ORTU',
                'kategori'      => 'undangan',
                                'deskripsi'     => 'Undangan rapat/pertemuan dengan orang tua/wali siswa.',
                'aktif'         => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'nama'          => 'Surat Undangan Rapat Guru',
                'kode'          => 'UND-GURU',
                'kategori'      => 'undangan',
                                'deskripsi'     => 'Undangan rapat internal dewan guru/karyawan.',
                'aktif'         => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'nama'          => 'Surat Tugas',
                'kode'          => 'ST',
                'kategori'      => 'tugas',
                                'deskripsi'     => 'Penugasan guru/karyawan untuk kegiatan tertentu, umumnya dipasangkan dengan SPPD jika ke luar kota.',
                'aktif'         => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'nama'          => 'SPPD (Surat Perintah Perjalanan Dinas)',
                'kode'          => 'SPPD',
                'kategori'      => 'sppd',
                                'deskripsi'     => 'Dokumen lembar 1 & 2 perjalanan dinas, berpasangan dengan Surat Tugas.',
                'aktif'         => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ]);
    }
}
