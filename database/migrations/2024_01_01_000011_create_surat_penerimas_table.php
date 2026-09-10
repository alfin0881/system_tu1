<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel surat_penerimas menampung penerima majemuk dalam satu surat,
     * misalnya daftar guru dalam SK Pembagian Tugas, daftar staff dalam
     * Surat Tugas, atau daftar undangan rapat. guru_id/siswa_id nullable
     * karena penerima juga bisa pihak luar (diisi via nama_custom).
     */
    public function up(): void
    {
        Schema::create('surat_penerimas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surats')->cascadeOnDelete();
            $table->foreignId('guru_id')->nullable()
                ->constrained('guru_karyawans')->nullOnDelete();
            $table->foreignId('siswa_id')->nullable()
                ->constrained('siswas')->nullOnDelete();
            $table->string('nama_custom')->nullable(); // penerima di luar data master
            $table->string('jabatan_custom')->nullable();
            $table->text('keterangan')->nullable(); // mis. tugas spesifik tiap penerima
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_penerimas');
    }
};
