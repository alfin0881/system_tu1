@extends('layouts.app')

@section('title', 'Ubah Kelas')

@section('content')
<div class="mx-auto max-w-2xl">
    <x-card class="p-6">
        <form method="POST" action="{{ route('kelas.update', $kelas) }}" class="space-y-5">
            @csrf
            @method('PUT')

            @include('kelas._form')

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a href="{{ route('kelas.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Batal</a>
                <x-button type="submit">Simpan Perubahan</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
