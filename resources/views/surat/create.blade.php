@extends('layouts.app')

@section('title', 'Buat Surat')
@section('subtitle', 'Form pengisian otomatis mengikuti template surat yang dipilih')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <x-card class="p-5">
        <form method="GET" class="max-w-md">
            <x-select name="jenis_surat_id" label="Jenis Surat" onchange="this.form.submit()">
                <option value="">-- Pilih jenis surat --</option>
                @foreach ($jenisSuratOptions as $kategori => $list)
                    <optgroup label="{{ ucfirst($kategori) }}">
                        @foreach ($list as $j)
                            <option value="{{ $j->id }}" @selected(optional($jenisSurat)->id === $j->id)>{{ $j->nama }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </x-select>
        </form>
    </x-card>

    @if ($jenisSurat && ! $jenisSurat->hasTemplate())
        <x-card class="p-5">
            <p class="text-sm text-red-600 dark:text-red-400">Jenis surat ini belum punya template docx. Upload templatenya dulu lewat menu Jenis Surat.</p>
        </x-card>
    @elseif ($jenisSurat)
        <form method="POST" action="{{ route('surat.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="jenis_surat_id" value="{{ $jenisSurat->id }}">

            <x-card class="space-y-4 p-5">
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Data Surat</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Template: {{ $jenisSurat->template_nama_asli ?? '-' }}</p>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-input type="date" name="tanggal_surat" label="Tanggal Surat" :value="old('tanggal_surat', now()->format('Y-m-d'))" required />
                    <x-input name="perihal" label="Perihal" :value="old('perihal', $jenisSurat->nama)" required />
                </div>

                <div class="border-t border-gray-100 pt-4 dark:border-gray-800">
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <input
                            type="checkbox"
                            id="pakai_nomor_manual"
                            onchange="document.getElementById('nomor_manual_wrap').classList.toggle('hidden', !this.checked)"
                            @checked(old('nomor_surat_manual'))
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:text-blue-400"
                        >
                        Isi nomor surat secara manual
                    </label>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Aktifkan jika nomor surat ini perlu disesuaikan dengan nomor yang sudah berjalan di buku agenda tata usaha. Jika tidak diaktifkan, nomor surat dibuat otomatis mengikuti format jenis surat.</p>
                    <div id="nomor_manual_wrap" class="mt-3 max-w-sm {{ old('nomor_surat_manual') ? '' : 'hidden' }}">
                        <x-input name="nomor_surat_manual" label="Nomor Surat Manual" :value="old('nomor_surat_manual')" placeholder="mis. 087/MTs.18/{{ $jenisSurat->kode }}/L.PM/VIII/2026" />
                    </div>
                </div>
            </x-card>

            @if (! empty($placeholders))
                <x-card class="space-y-4 p-5">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Isi Surat</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Kolom berikut mengikuti placeholder pada template "{{ $jenisSurat->nama }}". Nomor surat, tanggal, dan perihal di atas otomatis mengisi placeholder dengan nama sama (${nomor_surat}, ${tanggal_surat}, ${perihal}) jika ada di template.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach ($placeholders as $ph)
                            @if (! in_array(strtolower($ph), ['nomor_surat', 'tanggal_surat', 'perihal']))
                                @php
                                    // Pengecekan kata kunci untuk menentukan inputan berupa Textarea
                                    $isTextarea = \Illuminate\Support\Str::contains(strtolower($ph), [
                                        'alamat', 'isi', 'keterangan', 'maksud', 'catatan', 'dasar', 
                                        'acara', 'deskripsi', 'agenda', 'rincian', 'kegiatan'
                                    ]);
                                @endphp

                                @if ($isTextarea)
                                    <div class="sm:col-span-2">
                                        <x-textarea name="data[{{ $ph }}]" label="{{ ucwords(str_replace('_', ' ', $ph)) }}" :value="old('data.'.$ph)" />
                                    </div>
                                @else
                                    <x-input name="data[{{ $ph }}]" label="{{ ucwords(str_replace('_', ' ', $ph)) }}" :value="old('data.'.$ph)" />
                                @endif
                            @endif
                        @endforeach
                    </div>
                </x-card>
            @endif

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('surat.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Batal</a>
                <x-button type="submit">Buat Surat (Draft)</x-button>
            </div>
        </form>
    @endif
</div>
@endsection