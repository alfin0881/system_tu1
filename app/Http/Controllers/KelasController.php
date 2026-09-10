<?php

namespace App\Http\Controllers;

use App\Models\GuruKaryawan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Exports\KelasExport;
use Maatwebsite\Excel\Facades\Excel;


class KelasController extends Controller
{
    /**
     * "Data Kelas Siswa" — satu halaman berisi Rekap (jumlah siswa L/P/L+P
     * per tingkat-rombel) SEKALIGUS daftar siswa tiap kelas (ditampilkan
     * sebagai tab di view), supaya tidak perlu banyak menu/halaman terpisah.
     */
    public function index(Request $request): View
    {
        $tahunAjaranAktif = TahunAjaran::aktif()->first();
        $tahunAjaranId = $request->integer('tahun_ajaran_id') ?: $tahunAjaranAktif?->id;

        $kelas = Kelas::with(['waliKelas', 'tahunAjaran'])
            ->with(['siswaAktif' => fn ($q) => $q->orderBy('nama')])
            ->withCount([
                'siswaAktif',
                'siswaAktif as siswa_l_count' => fn ($q) => $q->where('jenis_kelamin', 'L'),
                'siswaAktif as siswa_p_count' => fn ($q) => $q->where('jenis_kelamin', 'P'),
            ])
            ->when($tahunAjaranId, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $tahunAjarans = TahunAjaran::orderByDesc('nama')->get();

        return view('kelas.index', compact('kelas', 'tahunAjarans', 'tahunAjaranId'));
    }

    /**
     * Halaman form terpisah untuk menambah siswa ke sebuah kelas (bukan lagi
     * ditumpuk di bawah tabel data siswa pada halaman index). Menampilkan
     * checklist siswa aktif di Buku Induk yang belum punya kelas, supaya TU
     * bisa memilih beberapa siswa sekaligus alih-alih satu per satu.
     */
    public function tambahSiswaForm(Kelas $kelas): View
    {
        $siswaTanpaKelas = Siswa::aktif()->whereNull('kelas_id')->orderBy('nama')->get();

        return view('kelas.tambah-siswa', compact('kelas', 'siswaTanpaKelas'));
    }

    /**
     * Tambah siswa ke kelas dengan memilih dari Buku Induk (siswa aktif yang
     * belum punya kelas), bukan input ulang. Mendukung banyak siswa sekaligus
     * lewat checklist. Untuk siswa yang belum tercatat sama sekali di Buku
     * Induk, TU diarahkan ke fitur input manual (buku-induk.create) dari view.
     */
    public function tambahSiswa(Request $request, Kelas $kelas): RedirectResponse
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'array', 'min:1'],
            'siswa_id.*' => ['exists:siswas,id'],
        ], [
            'siswa_id.required' => 'Pilih minimal satu siswa untuk ditambahkan.',
        ]);

        $jumlah = Siswa::whereIn('id', $data['siswa_id'])->update(['kelas_id' => $kelas->id]);

        return redirect()->route('kelas.index', array_filter(['tahun_ajaran_id' => $kelas->tahun_ajaran_id]))
            ->with('success', "{$jumlah} siswa berhasil ditambahkan ke kelas {$kelas->tingkat} - {$kelas->nama_kelas}.");
    }

    public function create(): View
    {
        $guru = GuruKaryawan::aktif()->orderBy('nama')->get();
        $tahunAjarans = TahunAjaran::orderByDesc('nama')->get();

        return view('kelas.create', compact('guru', 'tahunAjarans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Kelas::create($data);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas): View
    {
        $guru = GuruKaryawan::aktif()->orderBy('nama')->get();
        $tahunAjarans = TahunAjaran::orderByDesc('nama')->get();

        return view('kelas.edit', compact('kelas', 'guru', 'tahunAjarans'));
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $data = $this->validated($request);

        $kelas->update($data);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        $jumlahSiswa = $kelas->siswa()->count();
        $nama = $kelas->nama_kelas;
        $kelas->delete();

        return redirect()->route('kelas.index')->with(
            'success',
            $jumlahSiswa > 0
                ? "Kelas {$nama} dihapus. {$jumlahSiswa} siswa terkait kini tanpa kelas — pindahkan mereka ke kelas lain."
                : "Kelas {$nama} berhasil dihapus."
        );
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama_kelas' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'string', 'max:10'],
            'wali_kelas_id' => ['nullable', 'exists:guru_karyawans,id'],
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id'],
        ]);
    }

    public function export()
    {
        return Excel::download(new KelasExport, 'data_kelas.xlsx');
    }

}