<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mengembalikan kolom semester (Ganjil/Genap) ke tahun_ajarans, atas
     * permintaan ulang TU. Kolom ini sebelumnya sengaja dihapus lewat
     * migration 2025_02_02_000001_simplify_tahun_ajaran_remove_semester
     * (satu baris = satu tahun ajaran, tanpa pembagian semester). Sekarang
     * dibalik lagi: satu baris tahun ajaran = satu pasangan (nama, semester),
     * sehingga "2025/2026" bisa punya dua baris: Ganjil & Genap.
     *
     * Data lama (satu baris per tahun ajaran) tidak menyimpan info semester
     * aslinya, jadi sebagai perkiraan terbaik semua baris yang sudah ada
     * diberi nilai default 'Ganjil'. Silakan cek/tambahkan baris 'Genap'
     * yang sesuai lewat menu Tahun Ajaran bila diperlukan.
     */
    public function up(): void
    {
        Schema::table('tahun_ajarans', function (Blueprint $table) {
            $table->enum('semester', ['Ganjil', 'Genap'])->default('Ganjil')->after('nama');
        });

        Schema::table('tahun_ajarans', function (Blueprint $table) {
            $table->dropUnique(['nama']);
            $table->unique(['nama', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::table('tahun_ajarans', function (Blueprint $table) {
            $table->dropUnique(['nama', 'semester']);
            $table->unique('nama');
            $table->dropColumn('semester');
        });
    }
};
