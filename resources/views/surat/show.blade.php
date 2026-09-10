@extends('layouts.app')

@section('title', $surat->nomor_surat)
@section('subtitle', 'Detail surat')

@section('content')
@php $readonly = auth()->user()->role === 'kepsek'; @endphp

<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('surat.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">&larr; Kembali ke daftar surat</a>

        <div class="flex items-center gap-3">
            @if ($surat->file_path)
                <a href="{{ route('surat.print', $surat) }}" target="_blank" rel="noopener">
                    <x-button>Cetak</x-button>
                </a>
                <a href="{{ route('surat.cetak', $surat) }}">
                    <x-button variant="secondary">Unduh (.docx)</x-button>
                </a>
            @endif
            @unless ($readonly)
                @if ($surat->status === 'draft')
                    <a href="{{ route('surat.edit', $surat) }}"><x-button variant="secondary">Ubah</x-button></a>
                    <form method="POST" action="{{ route('surat.destroy', $surat) }}" onsubmit="return confirm('Hapus draft surat {{ $surat->nomor_surat }}?');">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger">Hapus</x-button>
                    </form>
                    <form method="POST" action="{{ route('surat.finalisasi', $surat) }}" onsubmit="return confirm('Finalisasi surat ini? Nomor surat akan terkunci dan surat tidak dapat diubah/dihapus lagi.');">
                        @csrf
                        <x-button type="submit">Finalisasi</x-button>
                    </form>
                @endif
            @endunless
        </div>
    </div>

    @unless ($surat->file_path)
        <x-card class="border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-500/10">
            <p class="text-sm text-amber-700 dark:text-amber-400">File docx surat ini belum berhasil dibuat. Buka halaman Ubah lalu simpan ulang untuk membuatnya.</p>
        </x-card>
    @endunless

    <x-card class="p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $surat->jenisSurat->nama ?? '-' }}</p>
                <h2 class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $surat->nomor_surat }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $surat->perihal }}</p>
            </div>
            <x-badge :color="$surat->status === 'final' ? 'green' : 'amber'">{{ ucfirst($surat->status) }}</x-badge>
        </div>

        <dl class="mt-6 grid grid-cols-1 gap-x-6 gap-y-3 border-t border-gray-100 pt-5 text-sm sm:grid-cols-2 dark:border-gray-800">
            <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Tanggal Surat</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $surat->tanggal_surat->translatedFormat('d F Y') }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Template</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $surat->jenisSurat->template_nama_asli ?? '-' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 dark:text-gray-400">Dibuat oleh</dt><dd class="font-medium text-gray-900 dark:text-gray-100">{{ $surat->dibuatOleh->name ?? '-' }}</dd></div>
        </dl>

        @if (! empty($surat->data_isian))
            <div class="mt-5 border-t border-gray-100 pt-5 dark:border-gray-800">
                <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Data Isian</p>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    @foreach ($surat->data_isian as $key => $value)
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="whitespace-pre-line text-right font-medium text-gray-900 dark:text-gray-100">{{ $value ?: '-' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </x-card>
</div>
@endsection
