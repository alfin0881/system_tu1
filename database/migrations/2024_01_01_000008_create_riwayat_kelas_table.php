<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel riwayat_kelas menjadi jejak audit tiap kali proses bulk
     * "Kenaikan Kelas / Kelulusan" dijalankan, per siswa per tahun ajaran.
     */
    public function up(): void
    {
        Schema::create('riwayat_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('kelas_id')->nullable()
                ->constrained('kelas')->nullOnDelete();
            $table->foreignId('tahun_ajaran_id')
                ->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->enum('status', ['naik', 'tinggal', 'lulus'])->default('naik');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kelas');
    }
};
