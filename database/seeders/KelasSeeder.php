<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $tahunAjaranId = DB::table('tahun_ajarans')->where('status', 'aktif')->value('id');

        $waliX   = DB::table('guru_karyawans')->where('nip_niy', '198002152005012009')->value('id'); // Siti Nurhaliza
        $waliXI  = DB::table('guru_karyawans')->where('nip_niy', '202001234')->value('id');           // Budi Santoso
        $waliXII = DB::table('guru_karyawans')->where('nip_niy', '198512102010012011')->value('id');  // Rina Marlina

        DB::table('kelas')->insert([
            [
                'nama_kelas'      => 'X IPA 1',
                'tingkat'         => 'X',
                'wali_kelas_id'   => $waliX,
                'tahun_ajaran_id' => $tahunAjaranId,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nama_kelas'      => 'XI IPA 1',
                'tingkat'         => 'XI',
                'wali_kelas_id'   => $waliXI,
                'tahun_ajaran_id' => $tahunAjaranId,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nama_kelas'      => 'XII IPA 1',
                'tingkat'         => 'XII',
                'wali_kelas_id'   => $waliXII,
                'tahun_ajaran_id' => $tahunAjaranId,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);
    }
}
