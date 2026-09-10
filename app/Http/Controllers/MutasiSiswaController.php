<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\MutasiSiswa;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MutasiSiswaController extends Controller
{
    public function index(Request $request): View
    {
        $mutasi = MutasiSiswa::with('siswa')
            ->when($request->filled('jenis_mutasi'), fn ($q) => $q->where('jenis_mutasi', $request->string('jenis_mutasi')))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('mutasi-siswa.index', compact('mutasi'));
    }

    /** Form siswa pindahan MASUK dari sekolah lain — sekaligus membuat data siswa baru. */
    public function createMasuk(): View
    {
        $kelasGrouped = $this->kelasGrouped();

        return view('mutasi-siswa.masuk', compact('kelasGrouped'));
    }

    public function storeMasuk(Request $request): RedirectResponse
    {
        $siswaData = $request->validate([
            'nisn' => ['nullable', 'string', 'max:15', 'unique:siswas,nisn'],
            'nis' => ['required', 'string', 'max:20', 'unique:siswas,nis'],
            'nama' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'agama' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'nama_wali' => ['nullable', 'string', 'max:255'],
            'no_hp_ortu' => ['nullable', 'string', 'max:20'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tanggal_masuk' => ['nullable', 'date'],
            'foto' => ['nullable', 'image', 'max:1024'],
        ], [
            'kelas_id.required' => 'Kelas tujuan wajib dipilih untuk siswa pindahan masuk.',
        ]);

        $mutasiData = $request->validate([
            'tanggal' => ['required', 'date'],
            'asal_sekolah' => ['required', 'string', 'max:255'],
            'alasan' => ['nullable', 'string'],
            'no_surat' => ['nullable', 'string', 'max:100'],
        ]);

        if ($request->hasFile('foto')) {
            $siswaData['foto'] = $request->file('foto')->store('foto-siswa', 'public');
        }

        $siswaData['status'] = 'aktif';
        $siswaData['tanggal_masuk'] = $siswaData['tanggal_masuk'] ?? $mutasiData['tanggal'];
        // Siswa pindahan tetap butuh angkatan di Buku Induk. Dipakai tahun
        // ajaran kelas tujuannya, sesuai tahun ia bergabung ke sekolah ini.
        $siswaData['tahun_ajaran_masuk_id'] = Kelas::find($siswaData['kelas_id'])?->tahun_ajaran_id;

        DB::transaction(function () use ($siswaData, $mutasiData) {
            $siswa = Siswa::create($siswaData);

            MutasiSiswa::create([
                'siswa_id' => $siswa->id,
                'jenis_mutasi' => 'masuk',
                'tanggal' => $mutasiData['tanggal'],
                'asal_sekolah' => $mutasiData['asal_sekolah'],
                'tujuan_sekolah' => null,
                'alasan' => $mutasiData['alasan'] ?? null,
                'no_surat' => $mutasiData['no_surat'] ?? null,
            ]);
        });

        return redirect()->route('mutasi-siswa.index')->with('success', 'Siswa pindahan berhasil dicatat masuk dan ditambahkan ke data master siswa.');
    }

    /** Form siswa aktif yang pindah/KELUAR ke sekolah lain. */
    public function createKeluar(): View
    {
        $siswaAktif = Siswa::aktif()->orderBy('nama')->get();

        return view('mutasi-siswa.keluar', compact('siswaAktif'));
    }

    public function storeKeluar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'siswa_id' => ['required', 'exists:siswas,id'],
            'tanggal' => ['required', 'date'],
            'tujuan_sekolah' => ['required', 'string', 'max:255'],
            'alasan' => ['nullable', 'string'],
            'no_surat' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($data) {
            $siswa = Siswa::findOrFail($data['siswa_id']);
            $siswa->update(['status' => 'pindah', 'kelas_id' => null]);

            MutasiSiswa::create([
                'siswa_id' => $siswa->id,
                'jenis_mutasi' => 'keluar',
                'tanggal' => $data['tanggal'],
                'asal_sekolah' => null,
                'tujuan_sekolah' => $data['tujuan_sekolah'],
                'alasan' => $data['alasan'] ?? null,
                'no_surat' => $data['no_surat'] ?? null,
            ]);
        });

        return redirect()->route('mutasi-siswa.index')->with('success', 'Siswa berhasil dicatat pindah/keluar. Status siswa otomatis berubah menjadi "Pindah".');
    }

    public function destroy(MutasiSiswa $mutasiSiswa): RedirectResponse
    {
        $mutasiSiswa->delete();

        return redirect()->route('mutasi-siswa.index')->with('success', 'Catatan mutasi berhasil dihapus. Status/kelas siswa tidak otomatis dikembalikan — sesuaikan manual di menu Siswa bila perlu.');
    }

    private function kelasGrouped()
    {
        return Kelas::with('tahunAjaran')
            ->orderByDesc('tahun_ajaran_id')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get()
            ->groupBy(fn ($k) => $k->tahunAjaran->label.($k->tahunAjaran->status === 'aktif' ? ' (Aktif)' : ''));
    }
}
