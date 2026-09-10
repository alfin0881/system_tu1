<?php

namespace App\Http\Controllers;

use App\Models\SekolahProfil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SekolahProfilController extends Controller
{
    public function edit(): View
    {
        $sekolah = SekolahProfil::aktif() ?? new SekolahProfil();

        return view('sekolah.edit', compact('sekolah'));
    }

    /**
     * Revisi: kop surat memakai layout BAKU (fixed) mengikuti contoh resmi
     * yang diberikan — logo + identitas sekolah bertingkat (Lembaga Induk /
     * Yayasan / Dasar Hukum / Nama Sekolah / Akreditasi / Nomor Izin /
     * Alamat & Telepon / Email & Website).
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_sekolah' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:1024'],
            'lembaga_induk' => ['nullable', 'string', 'max:255'],
            'nama_yayasan' => ['nullable', 'string', 'max:255'],
            'dasar_hukum' => ['nullable', 'string', 'max:255'],
            'akreditasi' => ['nullable', 'string', 'max:10'],
            'nomor_izin' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
        ]);

        $sekolah = SekolahProfil::aktif();

        if ($request->hasFile('logo')) {
            if ($sekolah?->logo) {
                Storage::disk('public')->delete($sekolah->logo);
            }
            $data['logo'] = $request->file('logo')->store('logo-sekolah', 'public');
        }

        if ($sekolah) {
            $sekolah->update($data);
        } else {
            SekolahProfil::create($data);
        }

        return redirect()->route('sekolah.edit')->with('success', 'Kop surat berhasil disimpan.');
    }
}
