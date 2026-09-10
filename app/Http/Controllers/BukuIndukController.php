<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Exports\BukuIndukExport;
use App\Imports\BukuIndukImport;
use Maatwebsite\Excel\Facades\Excel;


class BukuIndukController extends Controller
{
    /**
     * Revisi TU #9: Buku Induk Siswa adalah arsip permanen PER ANGKATAN.
     * "Angkatan" = tahun ajaran saat siswa PERTAMA KALI didaftarkan
     * (siswa.tahun_ajaran_masuk_id), BUKAN tahun ajaran kelasnya sekarang.
     * Sekali seorang siswa dicatat pada suatu angkatan (mis. Budi masuk
     * 2024/2025), ia akan SELAMANYA muncul di angkatan 2024/2025 itu saja —
     * tidak ikut "pindah" ke angkatan lain walau kelasnya naik/ganti tahun
     * ajaran setiap tahun (lihat KenaikanKelasController: proses kenaikan
     * kelas hanya mengubah kelas_id + mencatat riwayat_kelas, tidak pernah
     * menyentuh tahun_ajaran_masuk_id).
     *
     * Halaman ini juga WAJIB tetap menampilkan siswa yang sudah
     * lulus/pindah/keluar pada angkatan terkait — datanya tidak pernah
     * dihapus dari sistem (lihat SiswaController::destroy).
     */
    public function index(Request $request): View
    {
        $tahunAjaranAktif = TahunAjaran::getAktif();
        $tahunAjaranId = $request->integer('tahun_ajaran_id') ?: $tahunAjaranAktif?->id;
        $status = $request->string('status')->toString() ?: null;

        $siswa = Siswa::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(fn ($q2) => $q2->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%"));
            })
            // Angkatan: tetap di tahun ajaran masuknya, tidak dicampur dengan angkatan lain.
            ->when($tahunAjaranId, fn ($q) => $q->where('tahun_ajaran_masuk_id', $tahunAjaranId))
            // Filter tambahan: status siswa saat ini. "Mutasi Keluar" adalah
            // pilihan gabungan di UI yang mencakup status pindah & keluar
            // sekaligus (keduanya sama-sama berarti siswa sudah tidak lagi
            // bersekolah di sini karena mutasi/pindah), sementara di kolom
            // status database sendiri nilainya tetap terpisah pindah/keluar.
            ->when($status === 'mutasi_keluar', fn ($q) => $q->whereIn('status', ['pindah', 'keluar']))
            ->when($status && $status !== 'mutasi_keluar', fn ($q) => $q->where('status', $status))
            ->with(['kelas.tahunAjaran', 'tahunAjaranMasuk'])
            ->orderBy('nama')
            ->paginate(25)
            ->withQueryString();

        $tahunAjarans = TahunAjaran::orderByDesc('nama')->get();

        // Pilihan filter Status siswa: aktif/alumni tetap terpisah, sedangkan
        // pindah & keluar digabung jadi satu opsi "Mutasi Keluar" di UI.
        $statusOptions = [
            'aktif' => 'Aktif',
            'alumni' => 'Alumni',
            'mutasi_keluar' => 'Mutasi Keluar',
        ];

        return view('buku-induk.index', compact(
            'siswa', 'tahunAjarans', 'statusOptions', 'tahunAjaranId', 'status', 'tahunAjaranAktif'
        ));
    }

    /**
     * Revisi TU #9: titik masuk (entry point) untuk mendaftarkan siswa baru
     * kini SELALU dari Buku Induk — bukan lagi menu Siswa terpisah. Alurnya:
     * pilih angkatan (tahun ajaran) -> isi biodata -> tercatat di Buku Induk.
     * Penempatan ke Kelas TIDAK lagi dilakukan di sini — siswa baru belum
     * memiliki kelas dan akan ditempatkan kemudian lewat menu Siswa/Kenaikan
     * Kelas.
     */
    public function create(Request $request): View
    {
        $tahunAjarans = TahunAjaran::orderByDesc('nama')->get();
        $tahunAjaranId = $request->integer('tahun_ajaran_id')
            ?: TahunAjaran::getAktif()?->id
            ?: $tahunAjarans->first()?->id;

        return view('buku-induk.create', compact('tahunAjarans', 'tahunAjaranId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tahunAjaranId = $request->integer('tahun_ajaran_id');

        $data = $request->validate([
            'nisn' => ['nullable', 'string', 'max:15', 'unique:siswas,nisn'],
            'nik' => ['nullable', 'string', 'max:50'],
            'nis' => ['required', 'string', 'max:20', 'unique:siswas,nis'],
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
            'jenis_kelamin_wali' => ['nullable', Rule::in(['L', 'P'])],
            'pendidikan_terakhir_wali' => ['nullable', 'string', 'max:255'],
            'pekerjaan_wali' => ['nullable', 'string', 'max:255'],
            'penghasilan_wali' => ['nullable', 'string', 'max:255'],

            'no_hp_ortu' => ['nullable', 'string', 'max:50'],
            'rt_rw_jl' => ['nullable', 'string', 'max:255'],
            'dukuh' => ['nullable', 'string', 'max:255'],
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

            'tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id'],
            'tanggal_masuk' => ['nullable', 'date'],
            'foto' => ['nullable', 'image', 'max:1024'],
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('foto-siswa', 'public');
        }

        // Angkatan tercatat permanen di sini, terpisah dari kelas_id yang nanti
        // boleh berubah tiap tahun lewat proses Kenaikan Kelas.
        $data['tahun_ajaran_masuk_id'] = $data['tahun_ajaran_id'];
        unset($data['tahun_ajaran_id']);
        $data['status'] = 'aktif';

        $siswa = Siswa::create($data);

        return redirect()->route('buku-induk.index', ['tahun_ajaran_id' => $siswa->tahun_ajaran_masuk_id])
            ->with('success', "Siswa {$siswa->nama} berhasil dicatat di Buku Induk angkatan ini.");
    }

    public function export()
    {
        return Excel::download(new BukuIndukExport, 'data_buku_induk.xlsx');
    }

    public function import(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            // Wajib pilih angkatan tujuan supaya siswa hasil import tercatat
            // pada tahun_ajaran_masuk_id yang benar dan langsung muncul di
            // daftar Buku Induk (lihat catatan di method index()).
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id'],
        ]);

        Excel::import(new BukuIndukImport($request->integer('tahun_ajaran_id')), $request->file('file'));

        return redirect()->route('buku-induk.index', ['tahun_ajaran_id' => $request->integer('tahun_ajaran_id')])
            ->with('success', 'Data Buku Induk Berhasil Diimport!');
    }

}