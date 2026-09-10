<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Melengkapi tabel guru_karyawans dengan kolom-kolom biodata PTK
     * (Pendidik & Tenaga Kependidikan) sesuai format tabel Guru dan
     * Karyawan yang dipakai madrasah: gelar akademik, No. KTP, status
     * kepegawaian (PNS/Non PNS), TMT (Terhitung Mulai Tanggal) di
     * madrasah/PNS/golongan, mata pelajaran yang diampu, alamat
     * (kelurahan/kecamatan), pendidikan terakhir, dan nama ibu kandung.
     */
    public function up(): void
    {
        Schema::table('guru_karyawans', function (Blueprint $table) {
            $table->string('gelar_akademik')->nullable()->after('nama');
            $table->string('no_ktp', 20)->nullable()->after('nip_niy');
            $table->enum('status_kepegawaian', ['PNS', 'Non PNS'])->default('Non PNS')->after('status');
            $table->date('tmt_madrasah')->nullable()->after('status_kepegawaian');
            $table->date('tmt_pns')->nullable()->after('tmt_madrasah');
            $table->date('tmt_golongan')->nullable()->after('tmt_pns');
            $table->string('mengajar')->nullable()->after('jabatan');
            $table->string('kelurahan')->nullable()->after('alamat');
            $table->string('kecamatan')->nullable()->after('kelurahan');
            $table->string('pendidikan_terakhir')->nullable()->after('kecamatan');
            $table->string('nama_ibu_kandung')->nullable()->after('pendidikan_terakhir');
        });
    }

    public function down(): void
    {
        Schema::table('guru_karyawans', function (Blueprint $table) {
            $table->dropColumn([
                'gelar_akademik',
                'no_ktp',
                'status_kepegawaian',
                'tmt_madrasah',
                'tmt_pns',
                'tmt_golongan',
                'mengajar',
                'kelurahan',
                'kecamatan',
                'pendidikan_terakhir',
                'nama_ibu_kandung',
            ]);
        });
    }
};
