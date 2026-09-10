<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Urutan pemanggilan WAJIB seperti ini karena ada dependensi foreign key:
     * Kelas butuh TahunAjaran + GuruKaryawan (wali kelas) lebih dulu ada,
     * Siswa butuh Kelas lebih dulu ada.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            // TahunAjaranSeeder::class,
            // GuruKaryawanSeeder::class,
            // KelasSeeder::class,
            // SiswaSeeder::class,
            // JenisSuratSeeder::class, // butuh upload template docx manual dulu lewat menu Jenis Surat, jadi tidak di-seed otomatis.
        ]);
    }
}
