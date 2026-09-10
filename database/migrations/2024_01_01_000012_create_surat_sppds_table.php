<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel surat_sppds menyimpan data khusus SPPD (lembar 1 & 2) dan
     * ditautkan ke surat_tugas_id (juga baris di tabel surats, kategori
     * "tugas") sehingga satu perjalanan dinas punya sepasang dokumen:
     * Surat Tugas + SPPD yang saling berelasi.
     */
    public function up(): void
    {
        Schema::create('surat_sppds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->unique()
                ->constrained('surats')->cascadeOnDelete(); // surat SPPD itu sendiri
            $table->foreignId('surat_tugas_id')->nullable()
                ->constrained('surats')->nullOnDelete(); // pasangan Surat Tugas
            $table->string('tempat_berangkat')->nullable();
            $table->string('tempat_tujuan')->nullable();
            $table->date('tanggal_berangkat')->nullable();
            $table->date('tanggal_kembali')->nullable();
            $table->string('alat_transportasi')->nullable();
            $table->text('pengikut')->nullable();
            $table->text('maksud_perjalanan')->nullable();
            $table->text('biaya_keterangan')->nullable();
            $table->foreignId('pejabat_pemberi_perintah_id')->nullable()
                ->constrained('guru_karyawans')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_sppds');
    }
};
