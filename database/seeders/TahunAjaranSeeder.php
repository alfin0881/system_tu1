<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TahunAjaranSeeder extends Seeder
{
    /** 3 tahun ajaran: dua sudah lewat (nonaktif, jadi angkatan Buku Induk lama), satu berjalan (aktif). */
    public function run(): void
    {
        DB::table('tahun_ajarans')->insert([
            [
                'nama'       => '2024/2025',
                'semester'   => 'Genap',
                'status'     => 'nonaktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama'       => '2025/2026',
                'semester'   => 'Genap',
                'status'     => 'nonaktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama'       => '2026/2027',
                'semester'   => 'Ganjil',
                'status'     => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
