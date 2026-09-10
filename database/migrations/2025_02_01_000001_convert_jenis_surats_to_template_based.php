<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 6 — Surat Menyurat berbasis Template Upload.
     * Fitur kop surat custom & template_isi (teks placeholder manual) dihapus.
     * Sekarang setiap jenis surat punya file template .docx yang diupload
     * TU/Admin sendiri (kop surat sudah menyatu di dalam file tersebut).
     * template_variables menyimpan daftar placeholder ${...} yang otomatis
     * terdeteksi dari file docx tersebut, dipakai untuk membangun form
     * pengisian surat secara dinamis.
     */
    public function up(): void
    {
        Schema::table('jenis_surats', function (Blueprint $table) {
            $table->string('template_path')->nullable()->after('format_nomor');
            $table->string('template_nama_asli')->nullable()->after('template_path');
            $table->json('template_variables')->nullable()->after('template_nama_asli');
            $table->dropColumn('template_isi');
        });
    }

    public function down(): void
    {
        Schema::table('jenis_surats', function (Blueprint $table) {
            $table->text('template_isi')->nullable();
            $table->dropColumn(['template_path', 'template_nama_asli', 'template_variables']);
        });
    }
};
