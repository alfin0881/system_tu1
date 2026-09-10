<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TahunAjaranController extends Controller
{
    public function index(): View
    {
        $tahunAjarans = TahunAjaran::withCount(['kelas', 'siswaMasuk'])
            ->orderByDesc('nama')
            ->get();

        return view('tahun-ajaran.index', compact('tahunAjarans'));
    }

    public function create(): View
    {
        return view('tahun-ajaran.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data['status'] === 'aktif') {
            TahunAjaran::query()->update(['status' => 'nonaktif']);
        }

        TahunAjaran::create($data);

        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(TahunAjaran $tahunAjaran): View
    {
        return view('tahun-ajaran.edit', compact('tahunAjaran'));
    }

    public function update(Request $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $data = $this->validated($request, $tahunAjaran->id);

        if ($data['status'] === 'aktif' && $tahunAjaran->status !== 'aktif') {
            TahunAjaran::query()->where('id', '!=', $tahunAjaran->id)->update(['status' => 'nonaktif']);
        }

        $tahunAjaran->update($data);

        return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAjaran $tahunAjaran): RedirectResponse
    {
        if ($tahunAjaran->status === 'aktif') {
            return back()->with('error', 'Tahun ajaran yang sedang aktif tidak bisa dihapus. Aktifkan tahun ajaran lain terlebih dahulu.');
        }

        // Revisi TU #9: tahun_ajaran_masuk_id siswa (angkatan Buku Induk)
        // bersifat permanen. Kalau tahun ajaran ini masih menjadi angkatan
        // bagi siswa manapun, jangan sampai terhapus (nullOnDelete di DB
        // akan diam-diam menghilangkan angkatan mereka) — arsip Buku Induk
        // wajib tetap utuh, sama seperti siswa alumni/pindah/keluar yang
        // tidak boleh dihapus di SiswaController::destroy().
        $jumlahAngkatan = $tahunAjaran->siswaMasuk()->count();
        if ($jumlahAngkatan > 0) {
            return back()->with('error', "Tahun ajaran {$tahunAjaran->nama} tidak bisa dihapus karena menjadi angkatan Buku Induk bagi {$jumlahAngkatan} siswa. Data Buku Induk bersifat permanen dan tidak boleh kehilangan angkatannya.");
        }

        $jumlahKelas = $tahunAjaran->kelas()->count();
        $nama = $tahunAjaran->nama;
        $tahunAjaran->delete();

        return redirect()->route('tahun-ajaran.index')->with(
            'success',
            $jumlahKelas > 0
                ? "Tahun ajaran {$nama} dihapus, beserta {$jumlahKelas} kelas yang terkait."
                : "Tahun ajaran {$nama} berhasil dihapus."
        );
    }

    /** Jadikan tahun ajaran ini satu-satunya yang berstatus aktif. */
    public function aktifkan(TahunAjaran $tahunAjaran): RedirectResponse
    {
        TahunAjaran::query()->where('id', '!=', $tahunAjaran->id)->update(['status' => 'nonaktif']);
        $tahunAjaran->update(['status' => 'aktif']);

        return back()->with('success', "Tahun ajaran {$tahunAjaran->nama} sekarang aktif.");
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nama' => [
                'required', 'string', 'max:20', 'regex:/^\d{4}\/\d{4}$/',
                Rule::unique('tahun_ajarans', 'nama')
                    ->where(fn ($query) => $query->where('semester', $request->input('semester')))
                    ->ignore($ignoreId),
            ],
            'semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'nama.regex' => 'Format tahun ajaran harus seperti 2025/2026.',
            'nama.unique' => 'Tahun ajaran dengan semester yang sama sudah ada.',
        ]);
    }
}
