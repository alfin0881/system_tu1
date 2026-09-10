@extends('layouts.app')

@section('title', 'Jenis Surat')
@section('subtitle', 'Master jenis surat & template isi')

@section('content')
@php $readonly = auth()->user()->role === 'kepsek'; @endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="w-full sm:max-w-xs">
            <select
                name="kategori"
                onchange="this.form.submit()"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
            >
                <option value="">Semua Kategori</option>
                @foreach (['keterangan' => 'Keterangan', 'keputusan' => 'Keputusan (SK)', 'undangan' => 'Undangan', 'tugas' => 'Surat Tugas', 'sppd' => 'SPPD'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('kategori') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </form>

        @unless ($readonly)
            <a href="{{ route('jenis-surat.create') }}">
                <x-button>+ Jenis Surat</x-button>
            </a>
        @endunless
    </div>

    <x-card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Kode</th>
                        <th class="px-5 py-3 font-medium">Kategori</th>
                        <th class="px-5 py-3 font-medium">Template</th>
                        <th class="px-5 py-3 font-medium">Jumlah Surat</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        @unless ($readonly)
                            <th class="px-5 py-3 text-right font-medium">Aksi</th>
                        @endunless
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($jenisSurat as $j)
                        <tr>
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $j->nama }}</p>
                                @if ($j->deskripsi)
                                    <p class="truncate text-xs text-gray-400 dark:text-gray-500">{{ $j->deskripsi }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $j->kode }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ ucfirst($j->kategori) }}</td>
                            <td class="px-5 py-3">
                                @if ($j->template_path)
                                    <x-badge color="green">📄 Terupload</x-badge>
                                @else
                                    <x-badge color="red">Belum ada</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $j->surat_count }}</td>
                            <td class="px-5 py-3">
                                <x-badge :color="$j->aktif ? 'green' : 'slate'">{{ $j->aktif ? 'Aktif' : 'Nonaktif' }}</x-badge>
                            </td>
                            @unless ($readonly)
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('jenis-surat.edit', $j) }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Ubah</a>
                                        <form method="POST" action="{{ route('jenis-surat.destroy', $j) }}" onsubmit="return confirm('Hapus jenis surat {{ $j->nama }}?');">
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
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                Belum ada jenis surat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
