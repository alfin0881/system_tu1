<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revisi TU #4: "Tahun Masuk" (string tahun ajaran, mis. 2025/2026) diganti
     * menjadi "Tanggal Masuk" (tanggal MPLS/Masa Pengenalan Lingkungan Sekolah)
     * yang lebih presisi. Data lama dikonversi otomatis ke 1 Juli tahun awal
     * tahun ajaran tersebut supaya tidak ada data yang hilang begitu saja.
     */
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->date('tanggal_masuk')->nullable()->after('tahun_masuk');
        });

        // Migrasi data lama "2025/2026" -> tanggal_masuk 2025-07-01 (perkiraan awal MPLS).
        DB::table('siswas')->whereNotNull('tahun_masuk')->select('id', 'tahun_masuk')->orderBy('id')
            ->each(function ($row) {
                if (preg_match('/^(\d{4})\/\d{4}$/', (string) $row->tahun_masuk, $m)) {
                    DB::table('siswas')->where('id', $row->id)->update([
                        'tanggal_masuk' => "{$m[1]}-07-01",
                    ]);
                }
            });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('tahun_masuk');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('tahun_masuk', 9)->nullable()->after('tanggal_masuk');
        });

        DB::table('siswas')->whereNotNull('tanggal_masuk')->select('id', 'tanggal_masuk')->orderBy('id')
            ->each(function ($row) {
                $tahunAwal = (int) substr($row->tanggal_masuk, 0, 4);
                DB::table('siswas')->where('id', $row->id)->update([
                    'tahun_masuk' => "{$tahunAwal}/".($tahunAwal + 1),
                ]);
            });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('tanggal_masuk');
        });
    }
};
