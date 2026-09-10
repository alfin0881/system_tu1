<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sebelumnya satu tahun ajaran (mis. "2025/2026") bisa punya 2 baris
     * terpisah (Ganjil & Genap), yang bikin dropdown/pengisian jadi dobel
     * dan membingungkan padahal kelas & angkatan siswa tidak pernah benar2
     * dibedakan per semester di sistem ini. Sekarang disederhanakan jadi
     * SATU baris per tahun ajaran saja (kolom semester dihapus).
     *
     * Kalau kebetulan ada baris Ganjil & Genap dengan nama yang sama, kita
     * gabungkan dulu ke satu baris "kanonik" (prioritas: yang aktif, lalu id
     * terkecil) sebelum kolomnya dihapus, supaya kelas/siswa/riwayat kelas
     * yang sudah terlanjur nempel ke baris duplikat tidak kehilangan relasi.
     */
    public function up(): void
    {
        $rows = DB::table('tahun_ajarans')->orderBy('id')->get();
        $groups = $rows->groupBy('nama');

        foreach ($groups as $nama => $group) {
            if ($group->count() <= 1) {
                continue;
            }

            $kanonik = $group->firstWhere('status', 'aktif') ?? $group->first();

            foreach ($group as $row) {
                if ($row->id === $kanonik->id) {
                    continue;
                }

                DB::table('kelas')->where('tahun_ajaran_id', $row->id)->update(['tahun_ajaran_id' => $kanonik->id]);
                DB::table('siswas')->where('tahun_ajaran_masuk_id', $row->id)->update(['tahun_ajaran_masuk_id' => $kanonik->id]);
                DB::table('riwayat_kelas')->where('tahun_ajaran_id', $row->id)->update(['tahun_ajaran_id' => $kanonik->id]);
                DB::table('tahun_ajarans')->where('id', $row->id)->delete();
            }

            // Pastikan baris yang bertahan berstatus aktif kalau salah satu
            // baris yang digabungkan tadinya aktif.
            if ($group->contains('status', 'aktif')) {
                DB::table('tahun_ajarans')->where('id', $kanonik->id)->update(['status' => 'aktif']);
            }
        }

        Schema::table('tahun_ajarans', function (Blueprint $table) {
            $table->dropColumn('semester');
            $table->unique('nama');
        });
    }

    public function down(): void
    {
        Schema::table('tahun_ajarans', function (Blueprint $table) {
            $table->dropUnique(['nama']);
            $table->enum('semester', ['Ganjil', 'Genap'])->default('Ganjil');
        });
    }
};
