@extends('layouts.app')

@section('title', 'Buku Induk Siswa')
@section('subtitle', 'Arsip permanen data siswa per angkatan — siswa yang sudah lulus/pindah/keluar tetap tercatat')

@section('content')

<div class="space-y-6">
    <div class="flex gap-2 mb-4">
    <a href="{{ route('buku-induk.export') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">
        Export Excel
    </a>
    <form action="{{ route('buku-induk.import') }}" method="POST" enctype="multipart/form-data" class="flex gap-2 m-0 items-center">
        @csrf
        <select
            name="tahun_ajaran_id"
            required
            class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
        >
            <option value="" disabled selected>Pilih angkatan tujuan...</option>
            @foreach ($tahunAjarans as $ta)
                <option value="{{ $ta->id }}" @selected($tahunAjaranId == $ta->id)>
                    {{ $ta->label }}{{ $ta->status === 'aktif' ? ' (Aktif)' : '' }}
                </option>
            @endforeach
        </select>
        <input type="file" name="file" required accept=".xlsx,.xls,.csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-md dark:text-gray-400 dark:file:bg-blue-500/10 dark:file:text-blue-400 dark:hover:file:bg-blue-500/20 dark:border-gray-600">
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
            Import Data
        </button>
    </form>
</div>
<div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <x-card class="flex-1 p-5">
            <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-end">
                <div class="flex-1 sm:min-w-[220px]">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun Ajaran (Angkatan)</label>
                    <select
                        name="tahun_ajaran_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                    >
                        @foreach ($tahunAjarans as $ta)
                            <option value="{{ $ta->id }}" @selected($tahunAjaranId == $ta->id)>
                                {{ $ta->label }}{{ $ta->status === 'aktif' ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 sm:min-w-[200px]">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                    >
                        <option value="">Semua Status</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 sm:min-w-[220px]">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Cari</label>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Nama, NIS, atau NISN..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:text-gray-100 dark:placeholder:text-gray-500"
                    >
                </div>
                <button type="submit" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">
                    Terapkan
                </button>
            </form>
        </x-card>

                    <a href="{{ route('buku-induk.create', array_filter(['tahun_ajaran_id' => $tahunAjaranId])) }}" class="shrink-0">
                <x-button>+ Siswa</x-button>
            </a>
            </div>

    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-500/10 dark:text-amber-400">
        Buku Induk disusun per <strong>angkatan</strong> (tahun ajaran saat siswa pertama kali didaftarkan) dan
        bersifat permanen. Siswa yang berstatus
        Alumni, Pindah, atau Keluar tetap tercatat di sini dan tidak pernah dihapus dari sistem.
    </div>

    <x-card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium">NIS Lokal</th>
                        <th class="px-5 py-3 font-medium">NIS</th>
                        <th class="px-5 py-3 font-medium">Nama Lengkap</th>
                        <th class="px-5 py-3 font-medium">NIK</th>
                        <th class="px-5 py-3 font-medium">NISN</th>
                        <th class="px-5 py-3 font-medium">Tempat Lahir</th>
                        <th class="px-5 py-3 font-medium">Tanggal Lahir</th>
                        <th class="px-5 py-3 font-medium">Jenis Kelamin</th>
                        <th class="px-5 py-3 font-medium">Status Anak</th>
                        <th class="px-5 py-3 font-medium">Anak Ke</th>
                        <th class="px-5 py-3 font-medium">Jumlah Saudara</th>
                        <th class="px-5 py-3 font-medium">No HP/WA</th>
                        <th class="px-5 py-3 font-medium">No Kartu Keluarga</th>
                        <th class="px-5 py-3 font-medium">Kepala Keluarga</th>
                        <th class="px-5 py-3 font-medium">Status Ayah</th>
                        <th class="px-5 py-3 font-medium">Nama Ayah</th>
                        <th class="px-5 py-3 font-medium">NIK Ayah</th>
                        <th class="px-5 py-3 font-medium">Tempat Lahir Ayah</th>
                        <th class="px-5 py-3 font-medium">Tanggal Lahir Ayah</th>
                        <th class="px-5 py-3 font-medium">Pekerjaan Ayah</th>
                        <th class="px-5 py-3 font-medium">Penghasilan Ayah</th>
                        <th class="px-5 py-3 font-medium">Status Ibu</th>
                        <th class="px-5 py-3 font-medium">Nama Ibu</th>
                        <th class="px-5 py-3 font-medium">NIK Ibu</th>
                        <th class="px-5 py-3 font-medium">Tempat Lahir Ibu</th>
                        <th class="px-5 py-3 font-medium">Tanggal Lahir Ibu</th>
                        <th class="px-5 py-3 font-medium">Pekerjaan Ibu</th>
                        <th class="px-5 py-3 font-medium">Penghasilan Ibu</th>
                        <th class="px-5 py-3 font-medium">Status Wali</th>
                        <th class="px-5 py-3 font-medium">Nama Wali</th>
                        <th class="px-5 py-3 font-medium">NIK Wali</th>
                        <th class="px-5 py-3 font-medium">Tempat Lahir Wali</th>
                        <th class="px-5 py-3 font-medium">Tanggal Lahir Wali</th>
                        <th class="px-5 py-3 font-medium">Jenis Kelamin Wali</th>
                        <th class="px-5 py-3 font-medium">Pekerjaan Wali</th>
                        <th class="px-5 py-3 font-medium">Penghasilan Wali</th>
                        <th class="px-5 py-3 font-medium">Asal Sekolah</th>
                        <th class="px-5 py-3 font-medium">Status Sekolah</th>
                        <th class="px-5 py-3 font-medium">Nama Sekolah</th>
                        <th class="px-5 py-3 font-medium">NSM / NPSN</th>
                        <th class="px-5 py-3 font-medium">RT / RW / Jl</th>
                        <th class="px-5 py-3 font-medium">Dukuh</th>
                        <th class="px-5 py-3 font-medium">Desa</th>
                        <th class="px-5 py-3 font-medium">Kecamatan</th>
                        <th class="px-5 py-3 font-medium">Kabupaten</th>
                        <th class="px-5 py-3 font-medium">Provinsi</th>
                        <th class="px-5 py-3 font-medium">Kode Pos</th>
                        <th class="px-5 py-3 font-medium">No KIP</th>
                        <th class="px-5 py-3 font-medium">Pondok</th>
                        <th class="px-5 py-3 font-medium">Tanggal Masuk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($siswa as $s)
                        <tr>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->nis_lokal ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->nis ?: '-' }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('siswa.show', $s) }}" class="font-medium text-gray-900 hover:text-blue-600 dark:text-gray-100 dark:hover:text-blue-400">{{ $s->nama }}</a>
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->nik ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->nisn ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->tempat_lahir ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ optional($s->tanggal_lahir)->translatedFormat('d F Y') ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : ($s->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->status_dalam_keluarga ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->anak_ke ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->jumlah_saudara_kandung ?? '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->no_hp_ortu ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->no_kk ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->kepala_keluarga ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->status_ayah ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->nama_ayah ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->nik_ayah ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->tempat_lahir_ayah ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ optional($s->tanggal_lahir_ayah)->translatedFormat('d F Y') ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->pekerjaan_ayah ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->penghasilan_ayah ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->status_ibu ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->nama_ibu ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->nik_ibu ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->tempat_lahir_ibu ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ optional($s->tanggal_lahir_ibu)->translatedFormat('d F Y') ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->pekerjaan_ibu ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->penghasilan_ibu ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->status_wali ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->nama_wali ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->nik_wali ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->tempat_lahir_wali ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ optional($s->tanggal_lahir_wali)->translatedFormat('d F Y') ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->jenis_kelamin_wali === 'L' ? 'Laki-laki' : ($s->jenis_kelamin_wali === 'P' ? 'Perempuan' : '-') }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->pekerjaan_wali ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->penghasilan_wali ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->jenis_sekolah ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->status_sekolah ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->nama_sekolah ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->npsn_nsm ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->rt_rw_jl ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->dukuh ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->desa_kelurahan ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->kecamatan ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->kabupaten_kota ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->provinsi ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->kode_pos ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->no_kip ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->pondok_pesantren ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ optional($s->tanggal_masuk)->translatedFormat('d F Y') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="49" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                Tidak ada data siswa untuk angkatan/kelas ini.
                                                                    <a href="{{ route('buku-induk.create', array_filter(['tahun_ajaran_id' => $tahunAjaranId])) }}" class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Tambah siswa sekarang</a>.
                                                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    {{ $siswa->links() }}
</div>
@endsection