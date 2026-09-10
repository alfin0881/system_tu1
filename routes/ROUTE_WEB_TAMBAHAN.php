<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportImportController;

/*
|--------------------------------------------------------------------------
| ROUTES TAMBAHAN EXPORT IMPORT 
|--------------------------------------------------------------------------
| Buka file routes/web.php di project Anda.
| Salin seluruh kode di bawah ini dan letakkan di bagian paling bawah.
*/

Route::middleware(['auth'])->group(function () {
    
    // Halaman Dashboard Khusus Export Import
    Route::get('/export-import', [ExportImportController::class, 'index'])->name('export-import.index');

    Route::prefix('data')->name('data.')->group(function () {
        // Data Induk
        Route::get('/induk/export', [ExportImportController::class, 'exportDataInduk'])->name('induk.export');
        Route::post('/induk/import', [ExportImportController::class, 'importDataInduk'])->name('induk.import');

        // Guru Karyawan
        Route::get('/guru-karyawan/export', [ExportImportController::class, 'exportGuruKaryawan'])->name('guru-karyawan.export');
        Route::post('/guru-karyawan/import', [ExportImportController::class, 'importGuruKaryawan'])->name('guru-karyawan.import');

        // Kelas
        Route::get('/kelas/export', [ExportImportController::class, 'exportKelas'])->name('kelas.export');

        // Siswa
        Route::get('/siswa/export', [ExportImportController::class, 'exportSiswa'])->name('siswa.export');
    });
});
