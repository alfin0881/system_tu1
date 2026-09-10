@extends('layouts.app')

@section('title', 'Tambah Siswa — Buku Induk')
@section('subtitle', 'Pendaftaran siswa baru, tercatat permanen pada angkatan terpilih')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">

    <a href="{{ route('buku-induk.index', ['tahun_ajaran_id' => $tahunAjaranId]) }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">&larr; Kembali ke Buku Induk</a>

    <x-card class="p-5">
        <form method="GET">
            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun Ajaran (Angkatan)</label>
            <select
                name="tahun_ajaran_id"
                onchange="this.form.submit()"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:max-w-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
            >
                @foreach ($tahunAjarans as $ta)
                    <option value="{{ $ta->id }}" @selected($tahunAjaranId == $ta->id)>
                        {{ $ta->label }}{{ $ta->status === 'aktif' ? ' (Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
        <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
            Siswa akan tercatat permanen di Buku Induk pada angkatan tahun ajaran ini. Angkatan tidak akan
            berubah lagi setelahnya walau nanti siswa naik kelas ke tahun ajaran berikutnya.
        </p>
    </x-card>

    <x-card class="p-6">
        <form method="POST" action="{{ route('buku-induk.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunAjaranId }}">

            @include('siswa._form', ['showStatus' => false, 'showKelas' => false])

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a href="{{ route('buku-induk.index', ['tahun_ajaran_id' => $tahunAjaranId]) }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Batal</a>
                <x-button type="submit">Simpan</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
