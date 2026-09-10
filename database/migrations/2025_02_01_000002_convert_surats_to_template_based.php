<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom-kolom khusus kategori lama (siswa_id, penandatangan_id, sppd,
     * dasar_surat, dst.) dihapus karena sekarang seluruh isian surat bersifat
     * generik mengikuti placeholder pada template docx yang diupload
     * (disimpan di kolom data_isian). File hasil generate disimpan di
     * file_path (docx) sehingga tetap bisa diunduh & dicetak ulang kapan pun.
     */
    public function up(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->dropForeign(['siswa_id']);
            $table->dropForeign(['penandatangan_id']);
        });

        Schema::table('surats', function (Blueprint $table) {
            $table->dropColumn([
                'sifat', 'lampiran', 'isi_surat', 'dasar_surat',
                'siswa_id', 'penandatangan_id', 'jabatan_penandatangan', 'tempat_terbit',
            ]);
            $table->json('data_isian')->nullable()->after('perihal');
            $table->string('file_path')->nullable()->after('data_isian');
        });
    }

    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->dropColumn(['data_isian', 'file_path']);
            $table->string('sifat')->nullable();
            $table->string('lampiran')->nullable();
            $table->longText('isi_surat')->nullable();
            $table->text('dasar_surat')->nullable();
            $table->foreignId('siswa_id')->nullable()->constrained('siswas')->nullOnDelete();
            $table->foreignId('penandatangan_id')->nullable()->constrained('guru_karyawans')->nullOnDelete();
            $table->string('jabatan_penandatangan')->nullable();
            $table->string('tempat_terbit')->nullable();
        });
    }
};
