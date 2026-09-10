<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel tahun_ajarans menyimpan periode ajaran (mis. 2025/2026 Ganjil).
     * Kolom status menandai satu-satunya tahun ajaran yang sedang berjalan
     * (logika "hanya satu yang aktif" ditegakkan di level aplikasi/Model).
     */
    public function up(): void
    {
        Schema::create('tahun_ajarans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 20); // contoh: 2025/2026
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->enum('status', ['aktif', 'nonaktif'])->default('nonaktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_ajarans');
    }
};
