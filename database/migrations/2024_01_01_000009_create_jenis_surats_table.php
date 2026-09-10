<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel jenis_surats adalah master jenis surat (Surat Keterangan, SK,
     * Undangan, Surat Tugas, SPPD, dst). format_nomor menyimpan pola nomor
     * surat dinamis, contoh: "{nomor}/{kode}/{bulan_romawi}/{tahun}".
     * template_isi menyimpan draft isi surat default berisi placeholder
     * yang nanti di-render Controller Generator Surat (fase berikutnya).
     */
    public function up(): void
    {
        Schema::create('jenis_surats', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Surat Keterangan Aktif Siswa
            $table->string('kode', 30)->unique(); // SKET-AKTIF, SK-PTG, UND-ORTU, ST, SPPD
            $table->enum('kategori', ['keterangan', 'keputusan', 'undangan', 'tugas', 'sppd']);
            $table->string('format_nomor')->default('{nomor}/MTs.18/{kode}/L.PM/{bulan_romawi}/{tahun}');
            $table->text('template_isi')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_surats');
    }
};
