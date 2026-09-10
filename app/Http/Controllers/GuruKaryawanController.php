<?php

namespace App\Http\Controllers;

use App\Models\GuruKaryawan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Exports\GuruKaryawanExport;
use App\Imports\GuruKaryawanImport;
use Maatwebsite\Excel\Facades\Excel;


class GuruKaryawanController extends Controller
{
    public function index(Request $request): View
    {
        $guru = GuruKaryawan::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(fn ($q2) => $q2->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip_niy', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('guru.index', compact('guru'));
    }

    public function create(): View
    {
        return view('guru.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('foto-guru', 'public');
        }

        GuruKaryawan::create($data);

        return redirect()->route('guru.index')->with('success', 'Data guru/karyawan berhasil ditambahkan.');
    }

    public function edit(GuruKaryawan $guru): View
    {
        return view('guru.edit', compact('guru'));
    }

    public function update(Request $request, GuruKaryawan $guru): RedirectResponse
    {
        $data = $this->validated($request, $guru->id);

        if ($request->hasFile('foto')) {
            if ($guru->foto) {
                Storage::disk('public')->delete($guru->foto);
            }
            $data['foto'] = $request->file('foto')->store('foto-guru', 'public');
        }

        $guru->update($data);

        return redirect()->route('guru.index')->with('success', 'Data guru/karyawan berhasil diperbarui.');
    }

    public function destroy(GuruKaryawan $guru): RedirectResponse
    {
        $jumlahWali = $guru->kelasWali()->count();
        $nama = $guru->nama;

        if ($guru->foto) {
            Storage::disk('public')->delete($guru->foto);
        }

        $guru->delete();

        return redirect()->route('guru.index')->with(
            'success',
            $jumlahWali > 0
                ? "Data {$nama} dihapus. {$jumlahWali} kelas yang diwalikan kini tanpa wali kelas."
                : "Data {$nama} berhasil dihapus."
        );
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nip_niy' => ['nullable', 'string', 'max:30', Rule::unique('guru_karyawans')->ignore($ignoreId)],
            'no_ktp' => ['nullable', 'string', 'max:20'],
            'nama' => ['required', 'string', 'max:255'],
            'gelar_akademik' => ['nullable', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jabatan' => ['required', 'string', 'max:255'],
            'mengajar' => ['nullable', 'string', 'max:255'],
            'golongan_pangkat' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string'],
            'kelurahan' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:255'],
            'nama_ibu_kandung' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(['aktif', 'pensiun', 'pindah'])],
            'status_kepegawaian' => ['required', Rule::in(['PNS', 'Non PNS'])],
            'tmt_madrasah' => ['nullable', 'date'],
            'tmt_pns' => ['nullable', 'date'],
            'tmt_golongan' => ['nullable', 'date'],
            'foto' => ['nullable', 'image', 'max:1024'],
        ]);
    }

    public function export()
    {
        return Excel::download(new GuruKaryawanExport, 'data_guru_karyawan.xlsx');
    }

    public function import(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);
        Excel::import(new GuruKaryawanImport, $request->file('file'));
        return redirect()->route('guru.index')->with('success', 'Data Guru & Karyawan Berhasil Diimport!');
    }

}