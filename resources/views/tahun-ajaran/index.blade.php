@extends('layouts.app')

@section('title', 'Tahun Ajaran')
@section('subtitle', 'Kelola periode tahun ajaran sekolah')

@section('content')
@php $readonly = auth()->user()->role === 'kepsek'; @endphp

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">Hanya satu tahun ajaran yang boleh berstatus aktif pada satu waktu.</p>
        @unless ($readonly)
            <a href="{{ route('tahun-ajaran.create') }}">
                <x-button>+ Tahun Ajaran</x-button>
            </a>
        @endunless
    </div>

    <x-card class="overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3 font-medium">Tahun Ajaran</th>
                    <th class="px-5 py-3 font-medium">Semester</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Jumlah Kelas</th>
                    <th class="px-5 py-3 font-medium">Jumlah Angkatan (Buku Induk)</th>
                    @unless ($readonly)
                        <th class="px-5 py-3 text-right font-medium">Aksi</th>
                    @endunless
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($tahunAjarans as $ta)
                    <tr>
                        <td class="px-5 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $ta->nama }}</td>
                        <td class="px-5 py-3">
                            <x-badge color="slate">{{ $ta->semester }}</x-badge>
                        </td>
                        <td class="px-5 py-3">
                            @if ($ta->status === 'aktif')
                                <x-badge color="green">Aktif</x-badge>
                            @else
                                <x-badge>Nonaktif</x-badge>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $ta->kelas_count }}</td>
                        <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $ta->siswa_masuk_count }}</td>
                        @unless ($readonly)
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    @if ($ta->status !== 'aktif')
                                        <form method="POST" action="{{ route('tahun-ajaran.aktifkan', $ta) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('tahun-ajaran.edit', $ta) }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                        Ubah
                                    </a>
                                    @if ($ta->siswa_masuk_count)
                                        <span class="text-xs font-medium text-gray-300 dark:text-gray-600" title="Masih menjadi angkatan Buku Induk bagi {{ $ta->siswa_masuk_count }} siswa, tidak dapat dihapus">Terpakai</span>
                                    @else
                                        <form
                                            method="POST"
                                            action="{{ route('tahun-ajaran.destroy', $ta) }}"
                                            onsubmit="return confirm('Hapus tahun ajaran {{ $ta->nama }}?{{ $ta->kelas_count ? " {$ta->kelas_count} kelas terkait juga akan terhapus." : '' }}');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-400">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        @endunless
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                            Belum ada data tahun ajaran.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</div>
@endsection
