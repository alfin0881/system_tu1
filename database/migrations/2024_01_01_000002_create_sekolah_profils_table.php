<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel sekolah_profils menyimpan data Kop Surat / identitas sekolah.
     * Didesain sebagai tabel "singleton" (idealnya hanya berisi 1 baris aktif)
     * yang dipakai sebagai sumber data dinamis kop surat di semua template cetak.
     */
    public function up(): void
    {
        Schema::create('sekolah_profils', function (Blueprint $table) {
            $table->id();
            $table->string('nama_yayasan')->nullable();
            $table->string('nama_sekolah');
            $table->string('npsn', 20)->nullable();
            $table->text('alamat');
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten_kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable(); // path storage
            $table->string('akreditasi', 5)->nullable();
            $table->string('nama_kepala_sekolah');
            $table->string('nip_kepala_sekolah')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sekolah_profils');
    }
};
