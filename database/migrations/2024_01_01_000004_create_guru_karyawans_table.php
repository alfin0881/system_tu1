<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel guru_karyawans menyimpan seluruh Pendidik & Tenaga Kependidikan (PTK):
     * guru, kepala sekolah, staff TU, dst. Dipakai sebagai referensi wali kelas,
     * penandatangan surat, dan penerima surat tugas/SPPD.
     */
    public function up(): void
    {
        Schema::create('guru_karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nip_niy')->unique()->nullable(); // NIP (PNS) atau NIY (non-PNS)
            $table->string('nama');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jabatan'); // Kepala Sekolah, Guru Mapel, Staff TU, dst
            $table->string('golongan_pangkat')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['aktif', 'pensiun', 'pindah'])->default('aktif');
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_karyawans');
    }
};
