<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revisi lanjutan: kanvas kop surat bebas (Word-style) DIHAPUS sesuai
     * permintaan, diganti kembali dengan field-field TETAP yang mengikuti
     * satu layout baku (logo di kiri, teks identitas sekolah di tengah,
     * garis ganda di bawah) — sesuai contoh kop surat resmi yang diberikan:
     * Lembaga Induk / Yayasan / Dasar Hukum / Nama Sekolah / Akreditasi /
     * Nomor Izin / Alamat & Telepon / Email & Website.
     */
    public function up(): void
    {
        Schema::table('sekolah_profils', function (Blueprint $table) {
            $table->dropColumn('kop_surat_html');
        });

        Schema::table('sekolah_profils', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('nama_sekolah');
            $table->string('lembaga_induk')->nullable()->after('logo');
            $table->string('nama_yayasan')->nullable()->after('lembaga_induk');
            $table->string('dasar_hukum')->nullable()->after('nama_yayasan');
            $table->string('akreditasi', 10)->nullable()->after('dasar_hukum');
            $table->string('nomor_izin')->nullable()->after('akreditasi');
            $table->text('alamat')->nullable()->after('nomor_izin');
            $table->string('telepon')->nullable()->after('alamat');
            $table->string('email')->nullable()->after('telepon');
            $table->string('website')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('sekolah_profils', function (Blueprint $table) {
            $table->dropColumn([
                'logo', 'lembaga_induk', 'nama_yayasan', 'dasar_hukum',
                'akreditasi', 'nomor_izin', 'alamat', 'telepon', 'email', 'website',
            ]);
        });

        Schema::table('sekolah_profils', function (Blueprint $table) {
            $table->longText('kop_surat_html')->nullable()->after('nama_sekolah');
        });
    }
};
