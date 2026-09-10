<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiswaSeeder extends Seeder
{
    /**
     * Dummy secukupnya (8 siswa) untuk menunjukkan konsep Buku Induk per
     * angkatan: tahun_ajaran_masuk_id (angkatan, PERMANEN) dipisah dari
     * kelas_id (kelas yang ia duduki SEKARANG, ikut berubah tiap kenaikan
     * kelas). Sengaja disertakan variasi status (aktif/pindah/alumni/keluar)
     * supaya halaman Buku Induk langsung kelihatan cara kerja updatenya:
     * siswa tetap tercatat di angkatan masuknya walau statusnya berubah dan
     * kelas_id-nya sudah kosong (lulus/pindah/keluar).
     */
    public function run(): void
    {
        $taMasuk = DB::table('tahun_ajarans')->pluck('id', 'nama');
        $kelasX   = DB::table('kelas')->where('nama_kelas', 'X IPA 1')->value('id');
        $kelasXI  = DB::table('kelas')->where('nama_kelas', 'XI IPA 1')->value('id');
        $kelasXII = DB::table('kelas')->where('nama_kelas', 'XII IPA 1')->value('id');

        $siswa = [
            // Angkatan 2025/2026 (masuk tahun ini), duduk di kelas X IPA 1 sekarang — semua masih aktif.
            ['nisn' => '0081234561', 'nis' => '25010001', 'nama' => 'Ahmad Rizki Pratama', 'jk' => 'L', 'ttl' => ['Bogor', '2010-03-14'], 'agama' => 'Islam', 'alamat' => 'Jl. Cempaka No. 1, Cibinong, Bogor', 'ayah' => 'Slamet Pratama', 'ibu' => 'Yuli Astuti', 'kelas' => $kelasX, 'masuk' => '2025/2026', 'status' => 'aktif'],
            ['nisn' => '0081234562', 'nis' => '25010002', 'nama' => 'Bella Safira Putri', 'jk' => 'P', 'ttl' => ['Depok', '2010-06-22'], 'agama' => 'Islam', 'alamat' => 'Jl. Flamboyan No. 2, Cibinong, Bogor', 'ayah' => 'Hendra Wijaya', 'ibu' => 'Rina Kusuma', 'kelas' => $kelasX, 'masuk' => '2025/2026', 'status' => 'aktif'],
            ['nisn' => '0081234563', 'nis' => '25010003', 'nama' => 'Candra Wijaya', 'jk' => 'L', 'ttl' => ['Bekasi', '2010-01-09'], 'agama' => 'Kristen', 'alamat' => 'Jl. Teratai No. 3, Cibinong, Bogor', 'ayah' => 'Johan Wijaya', 'ibu' => 'Maria Ulfa', 'kelas' => $kelasX, 'masuk' => '2025/2026', 'status' => 'aktif'],

            // Angkatan 2024/2025, sekarang naik ke kelas XI IPA 1 — satu aktif, satu pindah sekolah (kelas_id dikosongkan, angkatan tetap 2024/2025).
            ['nisn' => '0081234566', 'nis' => '24010006', 'nama' => 'Gita Permata Sari', 'jk' => 'P', 'ttl' => ['Bogor', '2009-02-11'], 'agama' => 'Islam', 'alamat' => 'Jl. Mawar No. 6, Cibinong, Bogor', 'ayah' => 'Herman Sari', 'ibu' => 'Dewi Permata', 'kelas' => $kelasXI, 'masuk' => '2024/2025', 'status' => 'aktif'],
            ['nisn' => '0081234567', 'nis' => '24010007', 'nama' => 'Hendra Kurniawan', 'jk' => 'L', 'ttl' => ['Sukabumi', '2009-05-25'], 'agama' => 'Islam', 'alamat' => 'Jl. Melur No. 7, Cibinong, Bogor', 'ayah' => 'Kurniawan Saleh', 'ibu' => 'Endang Sulastri', 'kelas' => null, 'masuk' => '2024/2025', 'status' => 'pindah'],

            // Angkatan 2023/2024, sekarang di kelas XII IPA 1 — satu aktif, satu sudah lulus (alumni), satu keluar. Ketiganya tetap tercatat permanen di angkatan 2023/2024.
            ['nisn' => '0081234571', 'nis' => '23010011', 'nama' => 'Muhammad Iqbal Ramadhan', 'jk' => 'L', 'ttl' => ['Bogor', '2008-01-28'], 'agama' => 'Islam', 'alamat' => 'Jl. Anggrek No. 11, Cibinong, Bogor', 'ayah' => 'Ramadhan Syah', 'ibu' => 'Fitriani', 'kelas' => $kelasXII, 'masuk' => '2023/2024', 'status' => 'aktif'],
            ['nisn' => '0081234572', 'nis' => '23010012', 'nama' => 'Nadia Anggraini', 'jk' => 'P', 'ttl' => ['Jakarta', '2008-03-16'], 'agama' => 'Islam', 'alamat' => 'Jl. Cempaka No. 12, Cibinong, Bogor', 'ayah' => 'Anggara Wibowo', 'ibu' => 'Nurul Hidayah', 'kelas' => null, 'masuk' => '2023/2024', 'status' => 'alumni'],
            ['nisn' => '0081234573', 'nis' => '23010013', 'nama' => 'Oscar Firmansyah', 'jk' => 'L', 'ttl' => ['Bekasi', '2008-07-02'], 'agama' => 'Kristen', 'alamat' => 'Jl. Flamboyan No. 13, Cibinong, Bogor', 'ayah' => 'Firmansyah Halim', 'ibu' => 'Christin Oktavia', 'kelas' => null, 'masuk' => '2023/2024', 'status' => 'keluar'],
        ];

        $rows = array_map(function ($s) use ($taMasuk) {
            return [
                'nisn'                  => $s['nisn'],
                'nis'                   => $s['nis'],
                'nama'                  => $s['nama'],
                'tempat_lahir'          => $s['ttl'][0],
                'tanggal_lahir'         => $s['ttl'][1],
                'jenis_kelamin'         => $s['jk'],
                'agama'                 => $s['agama'],
                'alamat'                => $s['alamat'],
                'nama_ayah'             => $s['ayah'],
                'nama_ibu'              => $s['ibu'],
                'nama_wali'             => null,
                'no_hp_ortu'            => '0812'.rand(30000000, 39999999),
                'kelas_id'              => $s['kelas'],
                'tahun_ajaran_masuk_id' => $taMasuk[$s['masuk']] ?? null,
                'status'                => $s['status'],
                'tanggal_masuk'         => substr($s['masuk'], 0, 4).'-07-15',
                'foto'                  => null,
                'created_at'            => now(),
                'updated_at'            => now(),
            ];
        }, $siswa);

        DB::table('siswas')->insert($rows);
    }
}
