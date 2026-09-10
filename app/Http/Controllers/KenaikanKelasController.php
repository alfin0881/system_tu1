<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KenaikanKelasController extends Controller
{
    /**
     * Halaman proses kenaikan kelas/kelulusan bekerja per satu "kelas asal":
     * TU memilih kelas yang mau diproses, lalu untuk tiap siswa aktif di
     * kelas tersebut menentukan aksi (naik / tinggal / lulus) beserta kelas
     * tujuannya (kecuali lulus). Tidak ada perubahan data sampai tombol
     * "Proses" ditekan.
     */
    public function index(Request $request): View
    {
        $kelasAsalId = $request->integer('kelas_asal_id') ?: null;

        $kelasAsal = $kelasAsalId ? Kelas::with('tahunAjaran')->find($kelasAsalId) : null;

        $siswaKelasAsal = $kelasAsal
            ? Siswa::aktif()->where('kelas_id', $kelasAsal->id)->orderBy('nama')->get()
            : collect();

        $kelasOptions = Kelas::with('tahunAjaran')
            ->withCount('siswaAktif')
            ->orderByDesc('tahun_ajaran_id')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get()
            ->groupBy(fn ($k) => $k->tahunAjaran->label.($k->tahunAjaran->status === 'aktif' ? ' (Aktif)' : ''));

        $tahunAjarans = TahunAjaran::orderByDesc('nama')->get();

        return view('kenaikan-kelas.index', compact('kelasAsal', 'siswaKelasAsal', 'kelasOptions', 'tahunAjarans'));
    }

    public function proses(Request $request): RedirectResponse
    {
        $request->validate([
            'kelas_asal_id' => ['required', 'exists:kelas,id'],
            'tahun_ajaran_tujuan_id' => ['required', 'exists:tahun_ajarans,id'],
            'pilih' => ['required', 'array', 'min:1'],
            'pilih.*' => ['exists:siswas,id'],
        ], [
            'pilih.required' => 'Pilih minimal satu siswa untuk diproses.',
        ]);

        $tahunAjaranTujuanId = $request->integer('tahun_ajaran_tujuan_id');
        $kelasAsalId = $request->integer('kelas_asal_id');
        $kelasAsal = Kelas::find($kelasAsalId);

        // Revisi TU #7: kelulusan hanya berlaku untuk kelas tingkat tertinggi
        // (IX). Dicek ulang di server supaya tidak bisa dimanipulasi lewat
        // request langsung meskipun opsi "Lulus" sudah disembunyikan di UI
        // untuk kelas lain.
        $bolehLulus = $kelasAsal && strtoupper(trim($kelasAsal->tingkat)) === 'IX';

        $naik = 0;
        $tinggal = 0;
        $lulus = 0;
        $dilewati = 0;

        DB::transaction(function () use ($request, $tahunAjaranTujuanId, $bolehLulus, &$naik, &$tinggal, &$lulus, &$dilewati) {
            foreach ($request->input('pilih', []) as $siswaId) {
                $siswa = Siswa::find($siswaId);

                if (! $siswa) {
                    continue;
                }

                $aksi = $request->input("aksi.{$siswaId}", 'naik');
                $kelasTujuanId = $request->input("kelas_tujuan.{$siswaId}");

                if ($aksi === 'lulus') {
                    if (! $bolehLulus) {
                        // Bukan kelas IX -> abaikan aksi lulus, jangan ubah data.
                        $dilewati++;

                        continue;
                    }

                    $siswa->update(['status' => 'alumni', 'kelas_id' => null]);

                    RiwayatKelas::create([
                        'siswa_id' => $siswa->id,
                        'kelas_id' => null,
                        'tahun_ajaran_id' => $tahunAjaranTujuanId,
                        'status' => 'lulus',
                    ]);

                    $lulus++;

                    continue;
                }

                if (in_array($aksi, ['naik', 'tinggal'], true) && $kelasTujuanId) {
                    $siswa->update(['kelas_id' => $kelasTujuanId]);

                    RiwayatKelas::create([
                        'siswa_id' => $siswa->id,
                        'kelas_id' => $kelasTujuanId,
                        'tahun_ajaran_id' => $tahunAjaranTujuanId,
                        'status' => $aksi,
                    ]);

                    $aksi === 'naik' ? $naik++ : $tinggal++;

                    continue;
                }

                // Aksi naik/tinggal tanpa kelas tujuan dipilih -> lewati, jangan ubah data.
                $dilewati++;
            }
        });

        $ringkasan = collect([
            $naik ? "{$naik} naik kelas" : null,
            $tinggal ? "{$tinggal} tinggal kelas" : null,
            $lulus ? "{$lulus} lulus" : null,
        ])->filter()->implode(', ');

        if (! $ringkasan) {
            return redirect()->route('kenaikan-kelas.index', ['kelas_asal_id' => $kelasAsalId])
                ->with('error', 'Tidak ada siswa yang diproses. Pastikan kelas tujuan sudah dipilih untuk siswa yang naik/tinggal kelas.');
        }

        $pesan = "Berhasil diproses: {$ringkasan}.";
        if ($dilewati) {
            $pesan .= " {$dilewati} siswa dilewati karena kelas tujuan belum dipilih.";
        }

        return redirect()->route('kenaikan-kelas.index', ['kelas_asal_id' => $kelasAsalId])
            ->with('success', $pesan);
    }
}
