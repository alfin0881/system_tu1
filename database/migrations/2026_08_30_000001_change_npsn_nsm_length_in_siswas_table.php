<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom npsn_nsm sebelumnya varchar(20), ternyata beberapa data sumber
     * (import Excel Buku Induk) menyimpan NPSN dan NSM sekaligus dalam satu
     * sel (mis. "20123456 / 131235070001") sehingga melebihi 20 karakter dan
     * menyebabkan error SQLSTATE[22001] "Data too long for column".
     * Diperbesar menjadi 50 karakter agar aman menampung kombinasi tersebut.
     */
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('npsn_nsm', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('npsn_nsm', 20)->nullable()->change();
        });
    }
};
