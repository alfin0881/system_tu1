<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;


class SiswaController extends Controller
{
    /**
     * Revisi TU #3: data siswa kini disaring per Tahun Ajaran lalu per Kelas
     * (dua langkah, bukan satu dropdown kelas gabungan seperti sebelumnya)
     * supaya daftar tetap ringkas & mudah dipaginasi walau jumlah siswa
     * bertahun-tahun sudah menumpuk (siswa lama tidak pernah dihapus).
     */
    public function index(Request $request): View
    {
        $tahunAjaranAktif = TahunAjaran::getAktif();

        // Default: tahun ajaran aktif. Pengguna bisa memilih "Semua Tahun Ajaran"
        // secara eksplisit lewat ?tahun_ajaran_id=semua.
        $tahunAjaranParam = $request->query('tahun_ajaran_id');
        $tahunAjaranId = $tahunAjaranParam === 'semua'
            ? null
            : ($tahunAjaranParam !== null ? (int) $tahunAjaranParam : $tahunAjaranAktif?->id);

        $siswa = Siswa::with(['kelas.tahunAjaran'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(fn ($q2) => $q2->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%"));
            })
            ->when($tahunAjaranId, fn ($q) => $q->whereHas('kelas', fn ($k) => $k->where('tahun_ajaran_id', $tahunAjaranId)))
            ->when($request->filled('kelas_id'), fn ($q) => $q->where('kelas_id', $request->integer('kelas_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        $tahunAjarans = TahunAjaran::orderByDesc('nama')->get();
        $kelasOptions = Kelas::when($tahunAjaranId, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $kelasGrouped = $this->kelasGrouped();

        return view('siswa.index', compact(
            'siswa', 'kelasGrouped', 'tahunAjarans', 'kelasOptions', 'tahunAjaranId', 'tahunAjaranAktif'
        ));
    }

    public function show(Siswa $siswa): View
    {
        $siswa->load([
            'kelas.tahunAjaran',
            'tahunAjaranMasuk',
            'riwayatKelas' => fn ($q) => $q->with(['kelas', 'tahunAjaran'])->latest('id'),
            'mutasi' => fn ($q) => $q->latest('tanggal'),
        ]);

        return view('siswa.show', compact('siswa'));
    }

    public function edit(Siswa $siswa): View
    {
        $kelasGrouped = $this->kelasGrouped();

        return view('siswa.edit', compact('siswa', 'kelasGrouped'));
    }

    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        $data = $this->validated($request, $siswa->id);

        if ($request->hasFile('foto')) {
            if ($siswa->foto) {
                Storage::disk('public')->delete($siswa->foto);
            }
            $data['foto'] = $request->file('foto')->store('foto-siswa', 'public');
        }

        $siswa->update($data);

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Revisi TU #1: siswa yang statusnya sudah bukan "aktif" (alumni, pindah,
     * keluar) adalah arsip Buku Induk permanen dan TIDAK BOLEH dihapus, apa
     * pun alasannya — hanya siswa yang masih aktif (mis. salah input) yang
     * boleh dihapus dari data master.
     */
    public function destroy(Siswa $siswa): RedirectResponse
    {
        if ($siswa->status !== 'aktif') {
            return back()->with('error', "Data siswa {$siswa->nama} berstatus \"".ucfirst($siswa->status)."\" dan sudah tercatat permanen di Buku Induk, sehingga tidak dapat dihapus.");
        }

        $nama = $siswa->nama;

        if ($siswa->foto) {
            Storage::disk('public')->delete($siswa->foto);
        }

        $siswa->delete();

        return redirect()->route('siswa.index')->with('success', "Data siswa {$nama} berhasil dihapus, beserta riwayat kelas dan mutasinya.");
    }

    /** Daftar kelas dikelompokkan per Tahun Ajaran, dipakai di dropdown form. */
    private function kelasGrouped(): Collection
    {
        return Kelas::with('tahunAjaran')
            ->orderByDesc('tahun_ajaran_id')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get()
            ->groupBy(fn ($k) => $k->tahunAjaran->label.($k->tahunAjaran->status === 'aktif' ? ' (Aktif)' : ''));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nisn' => ['nullable', 'string', 'max:15', Rule::unique('siswas')->ignore($ignoreId)],
            'nik' => ['nullable', 'string', 'max:50'],
            'nis' => ['required', 'string', 'max:20', Rule::unique('siswas')->ignore($ignoreId)],
            'nis_lokal' => ['nullable', 'string', 'max:30'],
            'nama' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'agama' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],

            'status_dalam_keluarga' => ['nullable', 'string', 'max:255'],
            'anak_ke' => ['nullable', 'integer', 'min:1'],
            'jumlah_saudara_kandung' => ['nullable', 'integer', 'min:0'],
            'no_kk' => ['nullable', 'string', 'max:50'],
            'kepala_keluarga' => ['nullable', 'string', 'max:255'],

            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'status_ayah' => ['nullable', 'string', 'max:255'],
            'nik_ayah' => ['nullable', 'string', 'max:50'],
            'tempat_lahir_ayah' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir_ayah' => ['nullable', 'date'],
            'pendidikan_terakhir_ayah' => ['nullable', 'string', 'max:255'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:255'],
            'penghasilan_ayah' => ['nullable', 'string', 'max:255'],

            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'status_ibu' => ['nullable', 'string', 'max:255'],
            'nik_ibu' => ['nullable', 'string', 'max:50'],
            'tempat_lahir_ibu' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir_ibu' => ['nullable', 'date'],
            'pendidikan_terakhir_ibu' => ['nullable', 'string', 'max:255'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:255'],
            'penghasilan_ibu' => ['nullable', 'string', 'max:255'],

            'nama_wali' => ['nullable', 'string', 'max:255'],
            'status_wali' => ['nullable', 'string', 'max:255'],
            'nik_wali' => ['nullable', 'string', 'max:50'],
            'tempat_lahir_wali' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir_wali' => ['nullable', 'date'],
            'pendidikan_terakhir_wali' => ['nullable', 'string', 'max:255'],
            'pekerjaan_wali' => ['nullable', 'string', 'max:255'],
            'penghasilan_wali' => ['nullable', 'string', 'max:255'],

            'no_hp_ortu' => ['nullable', 'string', 'max:50'],
            'desa_kelurahan' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kabupaten_kota' => ['nullable', 'string', 'max:255'],
            'provinsi' => ['nullable', 'string', 'max:255'],
            'kode_pos' => ['nullable', 'string', 'max:10'],

            'jenis_sekolah' => ['nullable', 'string', 'max:255'],
            'status_sekolah' => ['nullable', 'string', 'max:255'],
            'npsn_nsm' => ['nullable', 'string', 'max:50'],
            'nama_sekolah' => ['nullable', 'string', 'max:255'],
            'status_kepemilikan_kip' => ['nullable', 'string', 'max:255'],
            'no_kip' => ['nullable', 'string', 'max:50'],
            'pondok_pesantren' => ['nullable', 'string', 'max:255'],

            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'status' => ['required', Rule::in(['aktif', 'alumni', 'pindah', 'keluar'])],
            'tanggal_masuk' => ['nullable', 'date'],
            'foto' => ['nullable', 'image', 'max:1024'],
        ]);
    }

}