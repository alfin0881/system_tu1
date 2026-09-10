<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel kelas terikat pada satu tahun_ajaran tertentu, sehingga histori
     * kelas per tahun ajaran tetap terjaga (kelas "X IPA 1" tahun 2025/2026
     * adalah baris berbeda dengan "X IPA 1" tahun 2026/2027).
     */
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas'); // X IPA 1
            $table->string('tingkat', 10); // X, XI, XII
            $table->foreignId('wali_kelas_id')->nullable()
                ->constrained('guru_karyawans')->nullOnDelete();
            $table->foreignId('tahun_ajaran_id')
                ->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
