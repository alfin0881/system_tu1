<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BukuIndukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportImportController;
use App\Http\Controllers\GuruKaryawanController;
use App\Http\Controllers\JenisSuratController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KenaikanKelasController;
use App\Http\Controllers\MutasiSiswaController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\TahunAjaranController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
| Arahkan ke dashboard jika sudah login, atau ke halaman login jika belum.
*/
Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});

/*
|--------------------------------------------------------------------------
| Guest (belum login)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated (sudah login) — semua modul TU berada di sini
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ------------------------------------------------------------------
    // Data Master — Fase 3
    // Semua modul di bawah bisa DILIHAT oleh admin/tu/kepsek, tapi aksi
    // tulis (create/update/delete) dibatasi middleware 'role:admin,tu'
    // sehingga Kepala Sekolah otomatis mode view-only.
    // ------------------------------------------------------------------

    // Tahun Ajaran — index boleh dilihat semua role, sisanya khusus admin/tu
    Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index'])->name('tahun-ajaran.index');
    Route::middleware('role:admin,tu')->group(function () {
        Route::get('/tahun-ajaran/create', [TahunAjaranController::class, 'create'])->name('tahun-ajaran.create');
        Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->name('tahun-ajaran.store');
        Route::get('/tahun-ajaran/{tahunAjaran}/edit', [TahunAjaranController::class, 'edit'])->name('tahun-ajaran.edit');
        Route::put('/tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'update'])->name('tahun-ajaran.update');
        Route::delete('/tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'destroy'])->name('tahun-ajaran.destroy');
        Route::patch('/tahun-ajaran/{tahunAjaran}/aktifkan', [TahunAjaranController::class, 'aktifkan'])->name('tahun-ajaran.aktifkan');
    });

    // Guru & Karyawan (PTK)
    Route::get('/guru', [GuruKaryawanController::class, 'index'])->name('guru.index');
    Route::middleware('role:admin,tu')->group(function () {
        Route::get('/guru/create', [GuruKaryawanController::class, 'create'])->name('guru.create');
        Route::post('/guru', [GuruKaryawanController::class, 'store'])->name('guru.store');
        Route::get('/guru/{guru}/edit', [GuruKaryawanController::class, 'edit'])->name('guru.edit');
        Route::put('/guru/{guru}', [GuruKaryawanController::class, 'update'])->name('guru.update');
        Route::delete('/guru/{guru}', [GuruKaryawanController::class, 'destroy'])->name('guru.destroy');
    });

    // Kelas — "Data Kelas Siswa": satu halaman berisi Rekap + tiap Kelas
    // (7-A, 7-B, dst) sebagai tab, supaya tidak perlu banyak menu/route
    // terpisah. Fitur Siswa (lihat & tambah siswa per kelas) digabung ke
    // sini, menggantikan halaman kelas.show yang sebelumnya terpisah.
    Route::get('/kelas', [KelasController::class, 'index'])->name('kelas.index');

    // Export Kelas — didaftarkan SEBELUM route {kelas} (edit/update/destroy)
    // supaya '/kelas/export' tidak pernah ketangkap sebagai '/kelas/{kelas}'.
    Route::get('/kelas/export', [KelasController::class, 'export'])->name('kelas.export');

    Route::middleware('role:admin,tu')->group(function () {
        Route::get('/kelas/create', [KelasController::class, 'create'])->name('kelas.create');
        Route::post('/kelas', [KelasController::class, 'store'])->name('kelas.store');
        Route::get('/kelas/{kelas}/edit', [KelasController::class, 'edit'])->name('kelas.edit');
        Route::put('/kelas/{kelas}', [KelasController::class, 'update'])->name('kelas.update');
        Route::delete('/kelas/{kelas}', [KelasController::class, 'destroy'])->name('kelas.destroy');

        // Tambah siswa ke kelas — halaman form tersendiri (bukan ditumpuk di
        // bawah tabel data siswa) dengan checklist, supaya bisa pilih dari
        // Buku Induk (siswa aktif yang belum punya kelas) beberapa sekaligus.
        // Input siswa baru manual tetap lewat Buku Induk.
        Route::get('/kelas/{kelas}/tambah-siswa', [KelasController::class, 'tambahSiswaForm'])->name('kelas.tambah-siswa.create');
        Route::post('/kelas/{kelas}/tambah-siswa', [KelasController::class, 'tambahSiswa'])->name('kelas.tambah-siswa');
    });

    // ------------------------------------------------------------------
    // Fase 4 — Siswa & Akademik
    // Pola akses sama seperti Data Master: index/show bisa dilihat semua
    // role, sisanya (edit/update/destroy/proses) khusus 'role:admin,tu'.
    // Pendaftaran siswa BARU (create/store) dipindah seluruhnya ke Buku
    // Induk (lihat blok di bawah) supaya hanya ada SATU alur input siswa.
    // ------------------------------------------------------------------

    // Siswa (Data Master). Pendaftaran siswa BARU tidak lagi lewat sini —
    // lihat blok "Buku Induk Siswa" di bawah — menu ini hanya untuk
    // melihat/mengubah data siswa yang sudah tercatat di Buku Induk.
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');

    Route::middleware('role:admin,tu')->group(function () {
        Route::get('/siswa/{siswa}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
        Route::put('/siswa/{siswa}', [SiswaController::class, 'update'])->name('siswa.update');
        Route::delete('/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
    });

    Route::get('/siswa/{siswa}', [SiswaController::class, 'show'])->name('siswa.show');

    // Buku Induk Siswa — arsip permanen PER ANGKATAN (tahun ajaran saat
    // siswa pertama didaftarkan). Ini titik masuk resmi untuk mendaftarkan
    // siswa baru sekaligus pembagian kelasnya (create/store); index tetap
    // bisa dilihat semua role, create/store khusus 'role:admin,tu'.
    Route::get('/buku-induk', [BukuIndukController::class, 'index'])->name('buku-induk.index');
    Route::middleware('role:admin,tu')->group(function () {
        Route::get('/buku-induk/create', [BukuIndukController::class, 'create'])->name('buku-induk.create');
        Route::post('/buku-induk', [BukuIndukController::class, 'store'])->name('buku-induk.store');
    });

    // ------------------------------------------------------------------
    // Export & Import Excel — Buku Induk, Guru/Karyawan, Kelas, Siswa
    // Export (baca data) boleh diakses semua role yang sudah login, sama
    // seperti index masing-masing modul. Import (tulis data) dibatasi
    // 'role:admin,tu'. Kelas & Siswa hanya Export (tidak ada alur Import).
    // Catatan: kelas.export SUDAH dipindah ke atas, di blok modulnya, agar
    // tidak bentrok dengan route {kelas}. siswa.export (tombol Export Excel
    // di halaman Data Siswa) sudah dihapus — export siswa kini hanya lewat
    // menu Export & Import (data.siswa.export).
    // ------------------------------------------------------------------
    Route::get('/buku-induk/export', [BukuIndukController::class, 'export'])->name('buku-induk.export');
    Route::get('/guru-karyawan/export', [GuruKaryawanController::class, 'export'])->name('guru.export');

    Route::middleware('role:admin,tu')->group(function () {
        Route::post('/buku-induk/import', [BukuIndukController::class, 'import'])->name('buku-induk.import');
        Route::post('/guru-karyawan/import', [GuruKaryawanController::class, 'import'])->name('guru.import');
    });

    // Kenaikan Kelas & Kelulusan (bulk, per kelas asal)
    Route::get('/kenaikan-kelas', [KenaikanKelasController::class, 'index'])->name('kenaikan-kelas.index');
    Route::middleware('role:admin,tu')->group(function () {
        Route::post('/kenaikan-kelas/proses', [KenaikanKelasController::class, 'proses'])->name('kenaikan-kelas.proses');
    });

    // Mutasi Siswa (masuk dari sekolah lain / keluar-pindah ke sekolah lain)
    Route::get('/mutasi-siswa', [MutasiSiswaController::class, 'index'])->name('mutasi-siswa.index');
    Route::middleware('role:admin,tu')->group(function () {
        Route::get('/mutasi-siswa/masuk/create', [MutasiSiswaController::class, 'createMasuk'])->name('mutasi-siswa.masuk.create');
        Route::post('/mutasi-siswa/masuk', [MutasiSiswaController::class, 'storeMasuk'])->name('mutasi-siswa.masuk.store');
        Route::get('/mutasi-siswa/keluar/create', [MutasiSiswaController::class, 'createKeluar'])->name('mutasi-siswa.keluar.create');
        Route::post('/mutasi-siswa/keluar', [MutasiSiswaController::class, 'storeKeluar'])->name('mutasi-siswa.keluar.store');
        Route::delete('/mutasi-siswa/{mutasiSiswa}', [MutasiSiswaController::class, 'destroy'])->name('mutasi-siswa.destroy');
    });

    // ------------------------------------------------------------------
    // Fase 5 — Surat Menyurat (Master Jenis Surat + Generator Surat)
    // Sama seperti modul lain: index/show bisa dilihat semua role,
    // create/store/edit/update/destroy/finalisasi khusus 'role:admin,tu'.
    // Rute /surat/create didaftarkan SEBELUM /surat/{surat} (show) dengan
    // alasan yang sama seperti /kelas/create vs /kelas/{kelas}.
    // ------------------------------------------------------------------

    // Jenis Surat (Master)
    Route::get('/jenis-surat', [JenisSuratController::class, 'index'])->name('jenis-surat.index');
    Route::middleware('role:admin,tu')->group(function () {
        Route::get('/jenis-surat/create', [JenisSuratController::class, 'create'])->name('jenis-surat.create');
        Route::post('/jenis-surat', [JenisSuratController::class, 'store'])->name('jenis-surat.store');
        Route::get('/jenis-surat/{jenisSurat}/edit', [JenisSuratController::class, 'edit'])->name('jenis-surat.edit');
        Route::put('/jenis-surat/{jenisSurat}', [JenisSuratController::class, 'update'])->name('jenis-surat.update');
        Route::delete('/jenis-surat/{jenisSurat}', [JenisSuratController::class, 'destroy'])->name('jenis-surat.destroy');
    });

    // Surat (Generator)
    Route::get('/surat', [SuratController::class, 'index'])->name('surat.index');
    Route::middleware('role:admin,tu')->group(function () {
        Route::get('/surat/create', [SuratController::class, 'create'])->name('surat.create');
        Route::post('/surat', [SuratController::class, 'store'])->name('surat.store');
        Route::get('/surat/{surat}/edit', [SuratController::class, 'edit'])->name('surat.edit');
        Route::put('/surat/{surat}', [SuratController::class, 'update'])->name('surat.update');
        Route::delete('/surat/{surat}', [SuratController::class, 'destroy'])->name('surat.destroy');
        Route::post('/surat/{surat}/finalisasi', [SuratController::class, 'finalisasi'])->name('surat.finalisasi');
    });

    Route::get('/surat/{surat}', [SuratController::class, 'show'])->name('surat.show');
    Route::get('/surat/{surat}/cetak', [SuratController::class, 'cetak'])->name('surat.cetak');

    // Cetak langsung dari browser (render docx sebagai HTML lewat PHPWord,
    // lalu dicetak client-side pakai Print.js) — tanpa Microsoft Word,
    // tanpa LibreOffice, tanpa proses eksternal apa pun di server.
    Route::get('/surat/{surat}/print', [SuratController::class, 'cetakLangsung'])->name('surat.print');

    // ------------------------------------------------------------------
    // Semua modul pada roadmap awal (Fase 1-6) sudah lengkap terdaftar.
    // ------------------------------------------------------------------
});

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