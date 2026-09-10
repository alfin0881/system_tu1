@extends('layouts.app')

@section('title', 'Tambah Kelas')

@section('content')
<div class="mx-auto max-w-2xl">
    <x-card class="p-6">
        <form method="POST" action="{{ route('kelas.store') }}" class="space-y-5">
            @csrf

            @include('kelas._form')

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a href="{{ route('kelas.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Batal</a>
                <x-button type="submit">Simpan</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
