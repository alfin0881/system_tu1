@extends('layouts.app')

@section('title', 'Tambah Siswa')

@section('content')
<div class="mx-auto max-w-3xl">
    <x-card class="p-6">
        <form method="POST" action="{{ route('siswa.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @include('siswa._form')

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a href="{{ route('siswa.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Batal</a>
                <x-button type="submit">Simpan</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
