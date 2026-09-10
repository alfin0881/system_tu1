<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel siswas adalah data master siswa. kelas_id nullable + nullOnDelete
     * agar siswa alumni/pindah tetap tersimpan meski kelasnya dihapus.
     * Kolom status menjadi kunci utama fitur Kenaikan Kelas & Kelulusan.
     */
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nisn', 15)->unique()->nullable();
            $table->string('nis', 20)->unique();
            $table->string('nama');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('agama')->nullable();
            $table->text('alamat')->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('nama_wali')->nullable();
            $table->string('no_hp_ortu', 20)->nullable();
            $table->foreignId('kelas_id')->nullable()
                ->constrained('kelas')->nullOnDelete();
            $table->enum('status', ['aktif', 'alumni', 'pindah', 'keluar'])->default('aktif');
            $table->string('tahun_masuk', 9)->nullable(); // 2025/2026
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
