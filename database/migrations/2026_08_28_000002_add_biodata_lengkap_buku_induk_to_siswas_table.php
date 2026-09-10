<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Melengkapi tabel siswas dengan seluruh kolom Buku Induk Siswa sesuai
     * format standar (NIS Lokal, NIK, data keluarga, data lengkap
     * Ayah/Ibu/Wali, alamat administratif, dan data sekolah asal/KIP).
     * Semua kolom nullable karena data ini sering diisi bertahap.
     */
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('nis_lokal', 30)->nullable()->after('nis');
            $table->string('nik', 20)->nullable()->after('nisn');

            // Data keluarga
            $table->string('status_dalam_keluarga')->nullable()->after('alamat');
            $table->unsignedTinyInteger('anak_ke')->nullable()->after('status_dalam_keluarga');
            $table->unsignedTinyInteger('jumlah_saudara_kandung')->nullable()->after('anak_ke');
            $table->string('no_kk', 20)->nullable()->after('jumlah_saudara_kandung');
            $table->string('kepala_keluarga')->nullable()->after('no_kk');

            // Data Ayah
            $table->string('status_ayah')->nullable()->after('nama_ayah');
            $table->string('nik_ayah', 20)->nullable()->after('status_ayah');
            $table->string('tempat_lahir_ayah')->nullable()->after('nik_ayah');
            $table->date('tanggal_lahir_ayah')->nullable()->after('tempat_lahir_ayah');
            $table->string('pendidikan_terakhir_ayah')->nullable()->after('tanggal_lahir_ayah');
            $table->string('pekerjaan_ayah')->nullable()->after('pendidikan_terakhir_ayah');
            $table->string('penghasilan_ayah')->nullable()->after('pekerjaan_ayah');

            // Data Ibu
            $table->string('status_ibu')->nullable()->after('nama_ibu');
            $table->string('nik_ibu', 20)->nullable()->after('status_ibu');
            $table->string('tempat_lahir_ibu')->nullable()->after('nik_ibu');
            $table->date('tanggal_lahir_ibu')->nullable()->after('tempat_lahir_ibu');
            $table->string('pendidikan_terakhir_ibu')->nullable()->after('tanggal_lahir_ibu');
            $table->string('pekerjaan_ibu')->nullable()->after('pendidikan_terakhir_ibu');
            $table->string('penghasilan_ibu')->nullable()->after('pekerjaan_ibu');

            // Data Wali
            $table->string('status_wali')->nullable()->after('nama_wali');
            $table->string('nik_wali', 20)->nullable()->after('status_wali');
            $table->string('tempat_lahir_wali')->nullable()->after('nik_wali');
            $table->date('tanggal_lahir_wali')->nullable()->after('tempat_lahir_wali');
            $table->string('pendidikan_terakhir_wali')->nullable()->after('tanggal_lahir_wali');
            $table->string('pekerjaan_wali')->nullable()->after('pendidikan_terakhir_wali');
            $table->string('penghasilan_wali')->nullable()->after('pekerjaan_wali');

            // Alamat administratif lengkap
            $table->string('desa_kelurahan')->nullable()->after('penghasilan_wali');
            $table->string('kecamatan')->nullable()->after('desa_kelurahan');
            $table->string('kabupaten_kota')->nullable()->after('kecamatan');
            $table->string('provinsi')->nullable()->after('kabupaten_kota');
            $table->string('kode_pos', 10)->nullable()->after('provinsi');

            // Data sekolah asal & KIP
            $table->string('jenis_sekolah')->nullable()->after('kode_pos');
            $table->string('status_sekolah')->nullable()->after('jenis_sekolah');
            $table->string('npsn_nsm', 20)->nullable()->after('status_sekolah');
            $table->string('nama_sekolah')->nullable()->after('npsn_nsm');
            $table->string('status_kepemilikan_kip')->nullable()->after('nama_sekolah');
            $table->string('no_kip', 20)->nullable()->after('status_kepemilikan_kip');
            $table->string('pondok_pesantren')->nullable()->after('no_kip');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn([
                'nis_lokal', 'nik',
                'status_dalam_keluarga', 'anak_ke', 'jumlah_saudara_kandung', 'no_kk', 'kepala_keluarga',
                'status_ayah', 'nik_ayah', 'tempat_lahir_ayah', 'tanggal_lahir_ayah',
                'pendidikan_terakhir_ayah', 'pekerjaan_ayah', 'penghasilan_ayah',
                'status_ibu', 'nik_ibu', 'tempat_lahir_ibu', 'tanggal_lahir_ibu',
                'pendidikan_terakhir_ibu', 'pekerjaan_ibu', 'penghasilan_ibu',
                'status_wali', 'nik_wali', 'tempat_lahir_wali', 'tanggal_lahir_wali',
                'pendidikan_terakhir_wali', 'pekerjaan_wali', 'penghasilan_wali',
                'desa_kelurahan', 'kecamatan', 'kabupaten_kota', 'provinsi', 'kode_pos',
                'jenis_sekolah', 'status_sekolah', 'npsn_nsm', 'nama_sekolah',
                'status_kepemilikan_kip', 'no_kip', 'pondok_pesantren',
            ]);
        });
    }
};
