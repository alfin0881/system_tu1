@extends('layouts.app')

@section('title', 'Guru & Karyawan')
@section('subtitle', 'Data Pendidik dan Tenaga Kependidikan (PTK)')

@section('content')

<div class="space-y-6">
    <!-- Baris Aksi & Filter (Sebaris dan Presisi) -->
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        
        <!-- Group Export, Import, dan Search -->
        <div class="flex flex-wrap items-center gap-3">
    <!-- Tombol Export Excel -->
    <a href="{{ route('guru.export') }}" 
       class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-green-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 transition-colors whitespace-nowrap shrink-0">
        Export Excel
    </a>

    <!-- Form Import Data -->
    <form action="{{ route('guru.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3 m-0 shrink-0">
        @csrf
        <div class="relative flex items-center h-11 w-64 rounded-lg border border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-800 overflow-hidden">
            <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                   class="block w-full h-full text-sm text-gray-500 file:mr-3 file:h-full file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:text-gray-400 dark:file:bg-blue-500/10 dark:file:text-blue-400 dark:hover:file:bg-blue-500/20 cursor-pointer">
        </div>
        <button type="submit" 
                class="inline-flex h-11 items-center justify-center rounded-lg bg-blue-600 px-6 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors whitespace-nowrap shrink-0">
            Import Data
        </button>
    </form>

    <!-- Form Pencarian & Filter -->
    <form method="GET" class="flex flex-wrap items-center gap-3 m-0">
        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Cari nama, NIP/NIY, atau jabatan..."
            class="h-11 w-64 rounded-lg border border-gray-300 px-4 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500"
        >
        <select
            name="status"
            onchange="this.form.submit()"
            class="h-11 rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
        >
            <option value="">Semua Status</option>
            @foreach (['aktif' => 'Aktif', 'pensiun' => 'Pensiun', 'pindah' => 'Pindah'] as $val => $label)
                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" 
                class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-900 shrink-0">
            Cari
        </button>
    </form>
</div>

        <!-- Tombol Tambah Data -->
        <a href="{{ route('guru.create') }}" class="shrink-0">
            <x-button class="h-9 text-1xl">+ Guru & Karyawan</x-button>
        </a>
    </div>

    <x-card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-3 font-medium">No</th>
                        <th class="px-3 py-3 font-medium">Nama</th>
                        <th class="px-3 py-3 font-medium">JK (L/P)</th>
                        <th class="px-3 py-3 font-medium">Gelar Akademik</th>
                        <th class="px-3 py-3 font-medium">NIP</th>
                        <th class="px-3 py-3 font-medium">Tempat Lahir</th>
                        <th class="px-3 py-3 font-medium">Tanggal Lahir</th>
                        <th class="px-3 py-3 font-medium">No KTP</th>
                        <th class="px-3 py-3 font-medium">Status PNS/Non PNS</th>
                        <th class="px-3 py-3 font-medium">TMT di Madrasah</th>
                        <th class="px-3 py-3 font-medium">TMT PNS</th>
                        <th class="px-3 py-3 font-medium">TMT Golongan</th>
                        <th class="px-3 py-3 font-medium">Golongan</th>
                        <th class="px-3 py-3 font-medium">Mengajar</th>
                        <th class="px-3 py-3 font-medium">Alamat Rumah Lengkap</th>
                        <th class="px-3 py-3 font-medium">Kelurahan</th>
                        <th class="px-3 py-3 font-medium">Kecamatan</th>
                        <th class="px-3 py-3 font-medium">Pendidikan Terakhir</th>
                        <th class="px-3 py-3 font-medium">Ibu Kandung</th>
                        <th class="px-3 py-3 font-medium">No HP</th>
                        <th class="px-3 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($guru as $g)
                        <tr>
                            <td class="px-3 py-3 text-gray-500 dark:text-gray-400">{{ $loop->iteration + ($guru->currentPage() - 1) * $guru->perPage() }}</td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($g->foto)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($g->foto) }}" class="h-8 w-8 shrink-0 rounded-full object-cover" alt="">
                                    @else
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-semibold text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                                            {{ strtoupper(substr($g->nama, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $g->nama }}</p>
                                        {{-- <p class="truncate text-xs text-gray-400 dark:text-gray-500">{{ $g->jabatan }}</p> --}}
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $g->jenis_kelamin }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $g->gelar_akademik ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $g->nip_niy ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $g->tempat_lahir ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ optional($g->tanggal_lahir)->format('d-m-Y') ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $g->no_ktp ?: '-' }}</td>
                            <td class="px-3 py-3">
                                <x-badge :color="$g->status_kepegawaian === 'PNS' ? 'green' : 'slate'">
                                    {{ $g->status_kepegawaian }}
                                </x-badge>
                            </td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ optional($g->tmt_madrasah)->format('d-m-Y') ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ optional($g->tmt_pns)->format('d-m-Y') ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ optional($g->tmt_golongan)->format('d-m-Y') ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $g->golongan_pangkat ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $g->mengajar ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300 max-w-xs truncate" title="{{ $g->alamat }}">{{ $g->alamat ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $g->kelurahan ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $g->kecamatan ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $g->pendidikan_terakhir ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $g->nama_ibu_kandung ?: '-' }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $g->no_hp ?: '-' }}</td>
                            <td class="px-3 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('guru.edit', $g) }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Ubah</a>
                                    <form method="POST" action="{{ route('guru.destroy', $g) }}" onsubmit="return confirm('Hapus data {{ $g->nama }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-400">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="21" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                Tidak ada data yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    {{ $guru->links() }}
</div>
@endsection
