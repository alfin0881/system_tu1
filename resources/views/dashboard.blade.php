@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', $tahunAjaranAktif ? "Tahun Ajaran {$tahunAjaranAktif->nama}" : 'Belum ada tahun ajaran aktif')

@section('content')
<div class="space-y-6">

    {{-- Kartu Statistik --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Siswa Aktif</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['siswa_aktif']) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ number_format($stats['siswa_alumni']) }} alumni tercatat</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Guru & Karyawan Aktif</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['guru_aktif']) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Termasuk Kepala Sekolah</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Kelas Berjalan</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['total_kelas']) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Tahun ajaran aktif</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Surat Bulan Ini</p>
            <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['surat_bulan_ini']) }}</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ now()->translatedFormat('F Y') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Surat Terbaru --}}
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm lg:col-span-2 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Surat Terbaru</h2>
                @if (\Illuminate\Support\Facades\Route::has('surat.index'))
                    <a href="{{ route('surat.index') }}" class="text-xs font-medium text-blue-700 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Lihat semua</a>
                @endif
            </div>

            @if ($suratTerbaru->isEmpty())
                <div class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                    Belum ada surat yang diterbitkan.
                </div>
            @else
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($suratTerbaru as $surat)
                        <div class="flex items-center justify-between gap-4 px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $surat->perihal }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                    {{ $surat->nomor_surat }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $surat->status === 'final' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' }}">
                                    {{ ucfirst($surat->status) }}
                                </span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">{{ $surat->tanggal_surat->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Ringkasan Kelas --}}
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Ringkasan Kelas</h2>
            </div>

            @if ($kelasRingkas->isEmpty())
                <div class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                    Belum ada kelas pada tahun ajaran aktif.
                </div>
            @else
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($kelasRingkas as $kelas)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $kelas->nama_kelas }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                    Wali: {{ $kelas->waliKelas->nama ?? '-' }}
                                </p>
                            </div>
                            <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                {{ $kelas->siswa_aktif_count }} siswa
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection