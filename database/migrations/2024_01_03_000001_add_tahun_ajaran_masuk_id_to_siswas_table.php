<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revisi TU #9: Buku Induk harus disusun PER ANGKATAN — yaitu tahun
     * ajaran saat siswa PERTAMA KALI didaftarkan (bukan tahun ajaran kelas
     * yang sedang berjalan). Kolom ini diisi sekali saat siswa dibuat lewat
     * menu Buku Induk dan bersifat permanen: tidak pernah ikut berubah saat
     * siswa naik kelas / kelasnya pindah ke tahun ajaran berikutnya (lihat
     * KenaikanKelasController yang hanya mengubah kelas_id + riwayat_kelas).
     * Contoh: Budi didaftarkan pada tahun ajaran 2024/2025 -> selamanya
     * tercatat di Buku Induk angkatan 2024/2025, walau tahun depan kelasnya
     * sudah masuk tahun ajaran 2025/2026.
     */
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->foreignId('tahun_ajaran_masuk_id')->nullable()->after('kelas_id')
                ->constrained('tahun_ajarans')->nullOnDelete();
        });

        // Backfill data lama: pakai tahun ajaran dari riwayat_kelas PALING
        // AWAL milik siswa tsb (paling mendekati saat ia pertama terdaftar),
        // fallback ke tahun ajaran kelasnya saat ini bila belum ada riwayat
        // sama sekali. Supaya siswa lama tidak perlu diinput ulang manual.
        DB::table('siswas')->orderBy('id')->select('id', 'kelas_id')->each(function ($siswa) {
            $tahunAjaranId = DB::table('riwayat_kelas')
                ->where('siswa_id', $siswa->id)
                ->orderBy('tahun_ajaran_id')
                ->value('tahun_ajaran_id');

            if (! $tahunAjaranId && $siswa->kelas_id) {
                $tahunAjaranId = DB::table('kelas')->where('id', $siswa->kelas_id)->value('tahun_ajaran_id');
            }

            if ($tahunAjaranId) {
                DB::table('siswas')->where('id', $siswa->id)->update([
                    'tahun_ajaran_masuk_id' => $tahunAjaranId,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tahun_ajaran_masuk_id');
        });
    }
};
