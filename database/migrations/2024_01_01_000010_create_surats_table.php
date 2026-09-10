<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel surats adalah tabel inti Generator Surat Keluar.
     * - nomor_urut disimpan terpisah dari nomor_surat (string hasil format)
     *   agar auto-increment per jenis+tahun mudah dihitung ulang.
     * - siswa_id dipakai khusus surat yang merujuk 1 siswa (mis. Surat
     *   Keterangan Aktif). Untuk penerima jamak (SK Pembagian Tugas,
     *   Surat Tugas, Undangan) gunakan tabel surat_penerimas.
     * - penandatangan_id merujuk PTK yang menandatangani (biasanya
     *   Kepala Sekolah), jabatan_penandatangan disimpan sebagai snapshot
     *   teks agar histori surat lama tidak berubah jika jabatan PTK
     *   berubah di kemudian hari.
     */
    public function up(): void
    {
        Schema::create('surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_surat_id')
                ->constrained('jenis_surats')->restrictOnDelete();
            $table->string('nomor_surat')->unique();
            $table->unsignedInteger('nomor_urut');
            $table->date('tanggal_surat');
            $table->string('perihal');
            $table->string('sifat')->nullable(); // Penting/Biasa/Segera
            $table->string('lampiran')->nullable();
            $table->longText('isi_surat')->nullable(); // hasil render / data dinamis
            $table->text('dasar_surat')->nullable(); // khusus SK
            $table->foreignId('siswa_id')->nullable()
                ->constrained('siswas')->nullOnDelete();
            $table->foreignId('penandatangan_id')->nullable()
                ->constrained('guru_karyawans')->nullOnDelete();
            $table->string('jabatan_penandatangan')->nullable();
            $table->string('tempat_terbit')->nullable();
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surats');
    }
};
