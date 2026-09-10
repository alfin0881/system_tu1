<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom untuk menyimpan path file PDF hasil convert otomatis dari docx
     * (lihat App\Services\DocxToPdfConverter). Dipisah dari file_path (docx)
     * supaya file docx asli tetap ada untuk diedit/diarsipkan, sementara
     * file_pdf_path dipakai untuk pratinjau & cetak langsung dari browser.
     */
    public function up(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->string('file_pdf_path')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->dropColumn('file_pdf_path');
        });
    }
};
