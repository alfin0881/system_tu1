@extends('layouts.app')

@section('title', 'Mutasi Siswa')
@section('subtitle', 'Riwayat siswa pindahan masuk dan siswa yang pindah/keluar')

@section('content')
@php $readonly = auth()->user()->role === 'kepsek'; @endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="w-full sm:max-w-xs">
            <select
                name="jenis_mutasi"
                onchange="this.form.submit()"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
            >
                <option value="">Semua Jenis Mutasi</option>
                <option value="masuk" @selected(request('jenis_mutasi') === 'masuk')>Masuk</option>
                <option value="keluar" @selected(request('jenis_mutasi') === 'keluar')>Keluar</option>
            </select>
        </form>

        @unless ($readonly)
            <div class="flex items-center gap-3">
                <a href="{{ route('mutasi-siswa.keluar.create') }}">
                    <x-button variant="secondary">+ Siswa Keluar</x-button>
                </a>
                <a href="{{ route('mutasi-siswa.masuk.create') }}">
                    <x-button>+ Siswa Masuk</x-button>
                </a>
            </div>
        @endunless
    </div>

    <x-card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">Siswa</th>
                        <th class="px-5 py-3 font-medium">Jenis</th>
                        <th class="px-5 py-3 font-medium">Asal / Tujuan Sekolah</th>
                        <th class="px-5 py-3 font-medium">No. Surat</th>
                        @unless ($readonly)
                            <th class="px-5 py-3 text-right font-medium">Aksi</th>
                        @endunless
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($mutasi as $m)
                        <tr>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $m->tanggal->translatedFormat('d F Y') }}</td>
                            <td class="px-5 py-3">
                                @if ($m->siswa)
                                    <a href="{{ route('siswa.show', $m->siswa) }}" class="font-medium text-gray-900 hover:text-blue-600 dark:text-gray-100 dark:hover:text-blue-400">{{ $m->siswa->nama }}</a>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">NIS {{ $m->siswa->nis }}</p>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">Siswa telah dihapus</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <x-badge :color="$m->jenis_mutasi === 'masuk' ? 'green' : 'amber'">{{ ucfirst($m->jenis_mutasi) }}</x-badge>
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $m->jenis_mutasi === 'masuk' ? ($m->asal_sekolah ?: '-') : ($m->tujuan_sekolah ?: '-') }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $m->no_surat ?: '-' }}</td>
                            @unless ($readonly)
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-3">
                                        <form method="POST" action="{{ route('mutasi-siswa.destroy', $m) }}" onsubmit="return confirm('Hapus catatan mutasi ini? Status/kelas siswa tidak otomatis dikembalikan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-400">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            @endunless
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                Belum ada catatan mutasi siswa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    {{ $mutasi->links() }}
</div>
@endsection
