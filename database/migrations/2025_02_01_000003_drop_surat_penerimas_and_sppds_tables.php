<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penerima jamak & data SPPD dulu ditangani lewat tabel relasi khusus
     * karena isi surat waktu itu memakai kategori tetap (keterangan/sppd/dst).
     * Sekarang semua data seperti itu cukup jadi placeholder bebas pada
     * template docx (mis. {{daftar_penerima}}, {{tempat_tujuan}}, dst) yang
     * disimpan sebagai teks di kolom surats.data_isian, jadi kedua tabel ini
     * sudah tidak dipakai lagi.
     */
    public function up(): void
    {
        Schema::dropIfExists('surat_sppds');
        Schema::dropIfExists('surat_penerimas');
    }

    public function down(): void
    {
        // Struktur lama sengaja tidak direstore; fitur ini sudah digantikan
        // sepenuhnya oleh mekanisme template docx generik.
    }
};
