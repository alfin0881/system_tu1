<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportImportController;

/*
|--------------------------------------------------------------------------
| Routes Tambahan Untuk Export/Import
|--------------------------------------------------------------------------
| Tambahkan baris di bawah ini ke dalam file routes/web.php Anda
*/

Route::middleware(['auth'])->group(function () {
    Route::prefix('data')->name('data.')->group(function () {
        
        // 1. Data Induk (Export & Import)
        Route::get('/induk/export', [ExportImportController::class, 'exportDataInduk'])->name('induk.export');
        Route::post('/induk/import', [ExportImportController::class, 'importDataInduk'])->name('induk.import');

        // 2. Guru & Karyawan (Export & Import)
        Route::get('/guru-karyawan/export', [ExportImportController::class, 'exportGuruKaryawan'])->name('guru-karyawan.export');
        Route::post('/guru-karyawan/import', [ExportImportController::class, 'importGuruKaryawan'])->name('guru-karyawan.import');

        // 3. Kelas (Hanya Export)
        Route::get('/kelas/export', [ExportImportController::class, 'exportKelas'])->name('kelas.export');

        // 4. Siswa (Hanya Export)
        Route::get('/siswa/export', [ExportImportController::class, 'exportSiswa'])->name('siswa.export');

    });
});
