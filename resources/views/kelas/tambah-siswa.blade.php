@extends('layouts.app')

@section('title', 'Tambah Siswa ke ' . $kelas->tingkat . '-' . $kelas->nama_kelas)
@section('subtitle', 'Pilih siswa dari Buku Induk yang belum punya kelas, lalu tambahkan sekaligus')

@section('content')

<div x-data="{ cari: '' }" class="mx-auto max-w-3xl space-y-4">
    <a href="{{ route('kelas.index', ['tahun_ajaran_id' => $kelas->tahun_ajaran_id]) }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
        &larr; Kembali ke {{ $kelas->tingkat }}-{{ $kelas->nama_kelas }}
    </a>

    <x-card class="p-5">
        <div class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">Kelas Tujuan</p>
                <p class="font-medium text-gray-800 dark:text-gray-100">{{ $kelas->tingkat }} - {{ $kelas->nama_kelas }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">Wali Kelas</p>
                <p class="font-medium text-gray-800 dark:text-gray-100">{{ $kelas->waliKelas->nama ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">Tahun Ajaran</p>
                <p class="font-medium text-gray-800 dark:text-gray-100">{{ $kelas->tahunAjaran->label ?? '-' }}</p>
            </div>
        </div>
    </x-card>

    <form method="POST" action="{{ route('kelas.tambah-siswa', $kelas) }}">
        @csrf

        <x-card class="space-y-3 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                    Siswa Tanpa Kelas di Buku Induk
                </p>
                <a href="{{ route('buku-induk.create', ['tahun_ajaran_id' => $kelas->tahun_ajaran_id]) }}" class="text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    + Input manual (belum ada di Buku Induk)
                </a>
            </div>

            <input
                type="text"
                x-model="cari"
                placeholder="Cari nama, NIS, atau NISN..."
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-500"
            >

            @error('siswa_id')
                <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            @if ($siswaTanpaKelas->isEmpty())
                <p class="px-3 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
                    Semua siswa di Buku Induk sudah memiliki kelas.
                </p>
            @else
                <div class="flex items-center gap-2 border-b border-gray-100 px-1 pb-2 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <input type="checkbox" id="pilih-semua-siswa" onclick="document.querySelectorAll('.baris-siswa-pilih').forEach(cb => { if (cb.closest('div').style.display !== 'none') cb.checked = this.checked; })" class="rounded border-gray-300 dark:border-gray-600">
                    <label for="pilih-semua-siswa">Pilih semua yang tampil</label>
                </div>

                <div class="max-h-96 divide-y divide-gray-100 overflow-y-auto rounded-lg border border-gray-100 dark:divide-gray-800 dark:border-gray-800">
                    @foreach ($siswaTanpaKelas as $s)
                        <div
                            x-show="!cari || {{ Illuminate\Support\Js::from(strtolower($s->nama.' '.$s->nis.' '.$s->nisn)) }}.includes(cari.toLowerCase())"
                            class="flex items-center gap-3 px-3 py-2 text-sm"
                        >
                            <input type="checkbox" name="siswa_id[]" value="{{ $s->id }}" class="baris-siswa-pilih shrink-0 rounded border-gray-300 dark:border-gray-600">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-gray-800 dark:text-gray-100">{{ $s->nama }}</p>
                                <p class="truncate text-xs text-gray-400 dark:text-gray-500">
                                    NIS {{ $s->nis }} @if ($s->nisn) &middot; NISN {{ $s->nisn }} @endif
                                    &middot; {{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        @unless ($siswaTanpaKelas->isEmpty())
            <div class="mt-4 flex items-center justify-end gap-3">
                <a href="{{ route('kelas.index', ['tahun_ajaran_id' => $kelas->tahun_ajaran_id]) }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Batal</a>
                <x-button type="submit">Tambahkan Siswa Terpilih</x-button>
            </div>
        @endunless
    </form>
</div>
@endsection
