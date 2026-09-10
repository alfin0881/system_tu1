<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel mutasi_siswas mencatat riwayat perpindahan siswa (masuk dari
     * sekolah lain, atau keluar/pindah ke sekolah lain).
     */
    public function up(): void
    {
        Schema::create('mutasi_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->enum('jenis_mutasi', ['masuk', 'keluar']);
            $table->date('tanggal');
            $table->string('asal_sekolah')->nullable();
            $table->string('tujuan_sekolah');
            $table->text('alasan')->nullable();
            $table->string('no_surat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_siswas');
    }
};
