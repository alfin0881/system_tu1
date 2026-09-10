@extends('layouts.app')

@section('title', 'Ubah Surat')
@section('subtitle', $surat->nomor_surat)

@section('content')
<div class="mx-auto max-w-4xl">
    <form method="POST" action="{{ route('surat.update', $surat) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-card class="space-y-4 p-5">
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Data Surat</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">Jenis surat: <strong>{{ $surat->jenisSurat->nama }}</strong> &middot; Template: {{ $surat->jenisSurat->template_nama_asli ?? '-' }}</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-input name="nomor_surat" label="Nomor Surat" :value="old('nomor_surat', $surat->nomor_surat)" required />
                <x-input type="date" name="tanggal_surat" label="Tanggal Surat" :value="old('tanggal_surat', $surat->tanggal_surat->format('Y-m-d'))" required />
                <x-input name="perihal" label="Perihal" :value="old('perihal', $surat->perihal)" required />
            </div>
        </x-card>

        @if (! empty($placeholders))
            <x-card class="space-y-4 p-5">
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Isi Surat</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Kolom berikut mengikuti placeholder pada template "{{ $surat->jenisSurat->nama }}". Simpan untuk membuat ulang file docx-nya.</p>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($placeholders as $ph)
                        @if (! in_array(strtolower($ph), ['nomor_surat', 'tanggal_surat', 'perihal']))
                            @php $oldValue = old('data.'.$ph, $surat->data_isian[$ph] ?? ''); @endphp
                            @if (str_contains($ph, 'alamat') || str_contains($ph, 'isi') || str_contains($ph, 'keterangan') || str_contains($ph, 'maksud') || str_contains($ph, 'catatan') || str_contains($ph, 'dasar'))
                                <div class="sm:col-span-2">
                                    <x-textarea name="data[{{ $ph }}]" label="{{ ucwords(str_replace('_', ' ', $ph)) }}" :value="$oldValue" />
                                </div>
                            @else
                                <x-input name="data[{{ $ph }}]" label="{{ ucwords(str_replace('_', ' ', $ph)) }}" :value="$oldValue" />
                            @endif
                        @endif
                    @endforeach
                </div>
            </x-card>
        @endif

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('surat.show', $surat) }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Batal</a>
            <x-button type="submit">Simpan Perubahan</x-button>
        </div>
    </form>
</div>
@endsection
