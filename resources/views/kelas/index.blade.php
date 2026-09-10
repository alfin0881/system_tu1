@extends('layouts.app')

@section('title', 'Data Kelas Siswa')
@section('subtitle', 'Rekap, kelola kelas, dan siswa per kelas dalam satu halaman')

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')

<div x-data="{ tab: 'rekap' }" class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="w-full sm:max-w-xs">
            <select
                name="tahun_ajaran_id"
                onchange="this.form.submit()"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
            >
                @foreach ($tahunAjarans as $ta)
                    <option value="{{ $ta->id }}" @selected($tahunAjaranId == $ta->id)>
                        {{ $ta->label }} @if ($ta->status === 'aktif') (Aktif) @endif
                    </option>
                @endforeach
            </select>
        </form>

        <div class="flex shrink-0 items-center gap-2">
            <a href="{{ route('kelas.export') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">
                Export Excel
            </a>
            <a href="{{ route('kelas.create') }}">
                <x-button>+ Kelas</x-button>
            </a>
        </div>
    </div>

    {{-- Tab: Rekap + tiap Kelas (mis. "7-A"). Semua digabung dalam satu
         halaman ini supaya menu tidak terlalu banyak. --}}
    <div class="flex gap-1 overflow-x-auto border-b border-gray-200 dark:border-gray-700">
        <button
            type="button"
            @click="tab = 'rekap'"
            class="shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition"
            :class="tab === 'rekap'
                ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
        >
            Rekap
        </button>
        @foreach ($kelas as $k)
            <button
                type="button"
                @click="tab = 'kelas-{{ $k->id }}'"
                class="shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition"
                :class="tab === 'kelas-{{ $k->id }}'
                    ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
            >
                {{ $k->tingkat }}-{{ $k->nama_kelas }}
            </button>
        @endforeach
    </div>

    {{-- Panel: Rekap --}}
    <div x-show="tab === 'rekap'" x-cloak>
        <x-card class="overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium" rowspan="2">Tingkat - Rombel</th>
                        <th class="px-5 py-3 text-center font-medium" colspan="3">Jumlah Siswa</th>
                        <th class="px-5 py-3 font-medium" rowspan="2">Wali Kelas</th>
                        <th class="px-5 py-3 text-right font-medium" rowspan="2">Aksi</th>
                    </tr>
                    <tr>
                        <th class="px-5 py-2 text-center font-medium">L</th>
                        <th class="px-5 py-2 text-center font-medium">P</th>
                        <th class="px-5 py-2 text-center font-medium">L+P</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($kelas as $k)
                        <tr>
                            <td class="px-5 py-3 font-medium text-gray-900 dark:text-gray-100">
                                <button type="button" @click="tab = 'kelas-{{ $k->id }}'" class="hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ $k->tingkat }} - {{ $k->nama_kelas }}
                                </button>
                            </td>
                            <td class="px-5 py-3 text-center text-gray-600 dark:text-gray-300">{{ $k->siswa_l_count }}</td>
                            <td class="px-5 py-3 text-center text-gray-600 dark:text-gray-300">{{ $k->siswa_p_count }}</td>
                            <td class="px-5 py-3 text-center">
                                <x-badge color="indigo">{{ $k->siswa_aktif_count }}</x-badge>
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $k->waliKelas->nama ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('kelas.edit', $k) }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Ubah</a>
                                    <form
                                        method="POST"
                                        action="{{ route('kelas.destroy', $k) }}"
                                        onsubmit="return confirm('Hapus kelas {{ $k->nama_kelas }}?{{ $k->siswa_aktif_count ? " {$k->siswa_aktif_count} siswa akan kehilangan kelas." : '' }}');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-400">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                Belum ada kelas pada tahun ajaran ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>
    </div>

    {{-- Panel: tiap Kelas — daftar siswa + tombol menuju form Tambah Siswa
         yang terpisah (halaman sendiri), supaya tidak menumpuk di bawah
         tabel data siswa. --}}
    @foreach ($kelas as $k)
        <div x-show="tab === 'kelas-{{ $k->id }}'" x-cloak class="space-y-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Tingkat {{ $k->tingkat }} &middot; Wali Kelas: {{ $k->waliKelas->nama ?? '-' }} &middot;
                    {{ $k->tahunAjaran->label ?? '-' }} &middot;
                    {{ $k->siswa_aktif_count }} siswa aktif ({{ $k->siswa_l_count }} L, {{ $k->siswa_p_count }} P)
                </p>
                <a href="{{ route('kelas.tambah-siswa.create', $k) }}" class="shrink-0">
                    <x-button>+ Tambah Siswa</x-button>
                </a>
            </div>

            <x-card class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3 font-medium">Nama</th>
                                <th class="px-5 py-3 font-medium">Jenis Kelamin</th>
                                <th class="px-5 py-3 font-medium">Kontak Ortu</th>
                                <th class="px-5 py-3 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($k->siswaAktif as $s)
                                <tr>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            @if ($s->foto)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::url($s->foto) }}" class="h-8 w-8 shrink-0 rounded-full object-cover" alt="">
                                            @else
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-semibold text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                                                    {{ strtoupper(substr($s->nama, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <a href="{{ route('siswa.show', $s) }}" class="truncate font-medium text-gray-900 hover:text-blue-600 dark:text-gray-100 dark:hover:text-blue-400">{{ $s->nama }}</a>
                                                <p class="truncate text-xs text-gray-400 dark:text-gray-500">NIS {{ $s->nis }} @if ($s->nisn) &middot; NISN {{ $s->nisn }} @endif</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->no_hp_ortu ?: '-' }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('siswa.edit', $s) }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Ubah</a>
                                            <form method="POST" action="{{ route('siswa.destroy', $s) }}" onsubmit="return confirm('Hapus data siswa {{ $s->nama }}? Riwayat kelas dan mutasi terkait akan ikut terhapus.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-400">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                        Belum ada siswa aktif di kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    @endforeach
</div>
@endsection
