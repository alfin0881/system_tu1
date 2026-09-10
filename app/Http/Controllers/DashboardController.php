<?php

namespace App\Http\Controllers;

use App\Models\GuruKaryawan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Surat;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tahunAjaranAktif = TahunAjaran::aktif()->first();

        $stats = [
            'siswa_aktif' => Siswa::aktif()->count(),
            'siswa_alumni' => Siswa::alumni()->count(),
            'guru_aktif' => GuruKaryawan::aktif()->count(),
            'total_kelas' => $tahunAjaranAktif
                ? Kelas::where('tahun_ajaran_id', $tahunAjaranAktif->id)->count()
                : 0,
            'surat_bulan_ini' => Surat::whereMonth('tanggal_surat', Date::now()->month)
                ->whereYear('tanggal_surat', Date::now()->year)
                ->count(),
        ];

        $suratTerbaru = Surat::with(['jenisSurat'])
            ->latest('tanggal_surat')
            ->latest('id')
            ->take(5)
            ->get();

        $kelasRingkas = Kelas::with('waliKelas')
            ->withCount('siswaAktif')
            ->when($tahunAjaranAktif, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranAktif->id))
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('dashboard', compact('stats', 'tahunAjaranAktif', 'suratTerbaru', 'kelasRingkas'));
    }
}
