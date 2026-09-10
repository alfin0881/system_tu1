@extends('layouts.app')

@section('title', $siswa->nama)
@section('subtitle', 'Detail data siswa')

@section('content')
@php $readonly = auth()->user()->role === 'kepsek'; @endphp

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('siswa.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">&larr; Kembali ke daftar siswa</a>
        @unless ($readonly)
            <div class="flex items-center gap-3">
                <a href="{{ route('siswa.edit', $siswa) }}"><x-button variant="secondary">Ubah Data</x-button></a>
                @if ($siswa->status === 'aktif')
                    <form method="POST" action="{{ route('siswa.destroy', $siswa) }}" onsubmit="return confirm('Hapus data siswa {{ $siswa->nama }}? Riwayat kelas dan mutasi terkait akan ikut terhapus.');">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger">Hapus</x-button>
                    </form>
                @else
                    <span class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-400 dark:bg-gray-700 dark:text-gray-500" title="Arsip Buku Induk, tidak dapat dihapus">Terarsip di Buku Induk</span>
                @endif
            </div>
        @endunless
    </div>

    <x-card class="p-6">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
            @if ($siswa->foto)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($siswa->foto) }}" class="h-20 w-20 shrink-0 rounded-full border border-gray-200 object-cover dark:border-gray-700" alt="">
            @else
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-blue-50 text-2xl font-semibold text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                    {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                </div>
            @endif
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $siswa->nama }}</h2>
                    <x-badge :color="['aktif' => 'green', 'alumni' => 'indigo', 'pindah' => 'amber', 'keluar' => 'red'][$siswa->status]">
                        {{ ucfirst($siswa->status) }}
                    </x-badge>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    NIS {{ $siswa->nis }} @if ($siswa->nisn) &middot; NISN {{ $siswa->nisn }} @endif
                    &middot; {{ $siswa->kelas->nama_kelas ?? 'Belum ada kelas' }}
                </p>
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card class="p-6">
            <p class="mb-4 text-sm font-semibold text-gray-900 dark:text-gray-100">Identitas</p>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">NIS Lokal</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->nis_lokal ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">NIK</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->nik ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Jenis Kelamin</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Tempat, Tanggal Lahir</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->tempat_lahir ?: '-' }}, {{ optional($siswa->tanggal_lahir)->translatedFormat('d F Y') ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Agama</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->agama ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Angkatan (Buku Induk)</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->tahunAjaranMasuk->nama ?? '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Tanggal Masuk (MPLS)</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ optional($siswa->tanggal_masuk)->translatedFormat('d F Y') ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Alamat</dt><dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $siswa->alamat ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Desa/Kel. &middot; Kec.</dt><dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $siswa->desa_kelurahan ?: '-' }} &middot; {{ $siswa->kecamatan ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Kab/Kota &middot; Provinsi</dt><dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $siswa->kabupaten_kota ?: '-' }} &middot; {{ $siswa->provinsi ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Kode Pos</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->kode_pos ?: '-' }}</dd></div>
            </dl>
        </x-card>

        <x-card class="p-6">
            <p class="mb-4 text-sm font-semibold text-gray-900 dark:text-gray-100">Data Keluarga</p>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Status dalam Keluarga</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->status_dalam_keluarga ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Anak Ke / Jml. Saudara</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->anak_ke ?: '-' }} / {{ $siswa->jumlah_saudara_kandung ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">No. Kartu Keluarga</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->no_kk ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Kepala Keluarga</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->kepala_keluarga ?: '-' }}</dd></div>
            </dl>
        </x-card>

        <x-card class="p-6">
            <p class="mb-4 text-sm font-semibold text-gray-900 dark:text-gray-100">Data Ayah</p>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Nama</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->nama_ayah ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Status</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->status_ayah ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">NIK</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->nik_ayah ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Tempat, Tanggal Lahir</dt><dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $siswa->tempat_lahir_ayah ?: '-' }}, {{ optional($siswa->tanggal_lahir_ayah)->translatedFormat('d F Y') ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Pendidikan</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->pendidikan_terakhir_ayah ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Pekerjaan</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->pekerjaan_ayah ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Penghasilan</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->penghasilan_ayah ?: '-' }}</dd></div>
            </dl>
        </x-card>

        <x-card class="p-6">
            <p class="mb-4 text-sm font-semibold text-gray-900 dark:text-gray-100">Data Ibu</p>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Nama</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->nama_ibu ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Status</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->status_ibu ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">NIK</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->nik_ibu ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Tempat, Tanggal Lahir</dt><dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $siswa->tempat_lahir_ibu ?: '-' }}, {{ optional($siswa->tanggal_lahir_ibu)->translatedFormat('d F Y') ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Pendidikan</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->pendidikan_terakhir_ibu ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Pekerjaan</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->pekerjaan_ibu ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Penghasilan</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->penghasilan_ibu ?: '-' }}</dd></div>
            </dl>
        </x-card>

        <x-card class="p-6">
            <p class="mb-4 text-sm font-semibold text-gray-900 dark:text-gray-100">Data Wali</p>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Nama</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->nama_wali ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Status</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->status_wali ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">NIK</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->nik_wali ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Tempat, Tanggal Lahir</dt><dd class="text-right font-medium text-gray-900 dark:text-gray-100">{{ $siswa->tempat_lahir_wali ?: '-' }}, {{ optional($siswa->tanggal_lahir_wali)->translatedFormat('d F Y') ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Pendidikan</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->pendidikan_terakhir_wali ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Pekerjaan</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->pekerjaan_wali ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Penghasilan</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->penghasilan_wali ?: '-' }}</dd></div>
            </dl>
        </x-card>

        <x-card class="p-6">
            <p class="mb-4 text-sm font-semibold text-gray-900 dark:text-gray-100">Kontak &amp; Sekolah Asal</p>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">No. HP Orang Tua/Wali</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->no_hp_ortu ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Jenis / Status Sekolah</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->jenis_sekolah ?: '-' }} / {{ $siswa->status_sekolah ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">NPSN/NSM</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->npsn_nsm ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Nama Sekolah</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->nama_sekolah ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">KIP</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->status_kepemilikan_kip ?: '-' }} @if ($siswa->no_kip) ({{ $siswa->no_kip }}) @endif</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Pondok Pesantren</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $siswa->pondok_pesantren ?: '-' }}</dd></div>
            </dl>
        </x-card>
    </div>

    <x-card class="overflow-hidden">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Riwayat Kenaikan Kelas & Kelulusan</p>
        </div>
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3 font-medium">Tahun Ajaran</th>
                    <th class="px-5 py-3 font-medium">Kelas</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($siswa->riwayatKelas as $r)
                    <tr>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $r->tahunAjaran->label }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $r->kelas->nama_kelas ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <x-badge :color="['naik' => 'green', 'tinggal' => 'amber', 'lulus' => 'indigo'][$r->status]">{{ ucfirst($r->status) }}</x-badge>
                        </td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $r->keterangan ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada riwayat kenaikan kelas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <x-card class="overflow-hidden">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Riwayat Mutasi</p>
        </div>
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3 font-medium">Tanggal</th>
                    <th class="px-5 py-3 font-medium">Jenis</th>
                    <th class="px-5 py-3 font-medium">Asal / Tujuan Sekolah</th>
                    <th class="px-5 py-3 font-medium">No. Surat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($siswa->mutasi as $m)
                    <tr>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $m->tanggal->translatedFormat('d F Y') }}</td>
                        <td class="px-5 py-3">
                            <x-badge :color="$m->jenis_mutasi === 'masuk' ? 'green' : 'amber'">{{ ucfirst($m->jenis_mutasi) }}</x-badge>
                        </td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $m->jenis_mutasi === 'masuk' ? ($m->asal_sekolah ?: '-') : ($m->tujuan_sekolah ?: '-') }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $m->no_surat ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada riwayat mutasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</div>
@endsection
