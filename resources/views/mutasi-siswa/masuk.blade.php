@extends('layouts.app')

@section('title', 'Siswa Pindahan Masuk')
@section('subtitle', 'Mencatat siswa baru yang pindah masuk dari sekolah lain — data siswa akan otomatis dibuat')

@section('content')
<div class="mx-auto max-w-3xl">
    <x-card class="p-6">
        <form method="POST" action="{{ route('mutasi-siswa.masuk.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Data Mutasi</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-input type="date" name="tanggal" label="Tanggal Masuk" :value="old('tanggal')" required />
                    <x-input name="asal_sekolah" label="Sekolah Asal" :value="old('asal_sekolah')" required />
                    <x-input name="no_surat" label="No. Surat Pindah" :value="old('no_surat')" placeholder="Opsional" />
                    <div class="sm:col-span-2">
                        <x-textarea name="alasan" label="Alasan / Keterangan" :value="old('alasan')" />
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6 dark:border-gray-800">
                @include('siswa._form', ['showStatus' => false])
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a href="{{ route('mutasi-siswa.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Batal</a>
                <x-button type="submit">Simpan</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
