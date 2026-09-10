@extends('layouts.app')

@section('title', 'Ubah Tahun Ajaran')

@section('content')
<div class="mx-auto max-w-2xl">
    <x-card class="p-6">
        <form method="POST" action="{{ route('tahun-ajaran.update', $tahunAjaran) }}" class="space-y-5">
            @csrf
            @method('PUT')

            @include('tahun-ajaran._form')

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a href="{{ route('tahun-ajaran.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Batal</a>
                <x-button type="submit">Simpan Perubahan</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
