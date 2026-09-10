<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom no_kip sebelumnya varchar(20), tapi data sumber (import Excel
     * Buku Induk) kadang berisi format nomor KIP yang lebih panjang
     * (mis. dengan spasi/pemisah atau tercampur keterangan lain) sehingga
     * menyebabkan error SQLSTATE[22001] "Data too long for column".
     * Diperbesar menjadi 50 karakter agar lebih aman.
     */
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('no_kip', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('no_kip', 20)->nullable()->change();
        });
    }
};
