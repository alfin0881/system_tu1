<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Melengkapi kolom yang dibutuhkan format tabel Buku Induk terbaru:
     * Jenis Kelamin Wali, serta alamat rinci RT/RW/Jl dan Dukuh
     * (sebelumnya hanya ada Desa/Kelurahan, Kecamatan, dst).
     */
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->enum('jenis_kelamin_wali', ['L', 'P'])->nullable()->after('tanggal_lahir_wali');

            $table->string('rt_rw_jl')->nullable()->after('alamat');
            $table->string('dukuh')->nullable()->after('rt_rw_jl');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn(['jenis_kelamin_wali', 'rt_rw_jl', 'dukuh']);
        });
    }
};
