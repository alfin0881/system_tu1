<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sebelum perbaikan ini, import Buku Induk Excel tidak mengisi kolom
     * tahun_ajaran_masuk_id, sehingga siswa hasil import "hilang" dari
     * tampilan (tersimpan di database tapi tidak cocok dengan filter
     * angkatan manapun karena nilainya NULL). Migration ini mengisi siswa
     * yang masih NULL tersebut dengan tahun ajaran AKTIF saat ini sebagai
     * perkiraan terbaik, karena data aslinya tidak tercatat.
     *
     * PENTING: setelah migrate, mohon cek ulang di menu Buku Induk apakah
     * siswa-siswa ini sudah berada di angkatan yang benar. Jika tidak,
     * silakan edit satu per satu lewat menu Siswa/Buku Induk.
     */
    public function up(): void
    {
        $tahunAjaranAktifId = DB::table('tahun_ajarans')->where('status', 'aktif')->value('id');

        if ($tahunAjaranAktifId) {
            DB::table('siswas')
                ->whereNull('tahun_ajaran_masuk_id')
                ->update(['tahun_ajaran_masuk_id' => $tahunAjaranAktifId]);
        }
    }

    public function down(): void
    {
        // Tidak dikembalikan ke NULL karena tidak bisa dibedakan lagi mana
        // yang aslinya NULL vs yang diisi migration ini.
    }
};
