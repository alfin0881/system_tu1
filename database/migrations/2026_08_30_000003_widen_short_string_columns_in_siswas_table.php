<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom-kolom ini sebelumnya varchar(20), pas-pasan dengan panjang data
     * standarnya (NIK 16 digit, No. HP 12-13 digit). Beberapa data sumber
     * (import Excel Buku Induk) kadang menggabungkan lebih dari satu nilai
     * dalam satu sel (mis. dua nomor HP dipisah "/"), sehingga berisiko
     * memicu error SQLSTATE[22001] "Data too long for column" seperti yang
     * sudah terjadi pada npsn_nsm dan no_kip. Diperbesar untuk jaga-jaga.
     */
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('nik', 50)->nullable()->change();
            $table->string('no_kk', 50)->nullable()->change();
            $table->string('nik_ayah', 50)->nullable()->change();
            $table->string('nik_ibu', 50)->nullable()->change();
            $table->string('nik_wali', 50)->nullable()->change();
            $table->string('no_hp_ortu', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('nik', 20)->nullable()->change();
            $table->string('no_kk', 20)->nullable()->change();
            $table->string('nik_ayah', 20)->nullable()->change();
            $table->string('nik_ibu', 20)->nullable()->change();
            $table->string('nik_wali', 20)->nullable()->change();
            $table->string('no_hp_ortu', 20)->nullable()->change();
        });
    }
};
