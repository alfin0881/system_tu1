@extends('layouts.print')

@section('title', $surat->nomor_surat)

@php
    $sppd = $surat->sppd;
    $suratTugas = $sppd?->suratTugas;
    $petugas = $suratTugas?->penerima ?? collect();
    $lamaHari = ($sppd?->tanggal_berangkat && $sppd?->tanggal_kembali)
        ? $sppd->tanggal_berangkat->diffInDays($sppd->tanggal_kembali) + 1
        : null;
@endphp

@section('content')
    @include('surat.partials.kop-surat', ['sekolah' => $sekolah])

    @if ($surat->status !== 'final')
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
            <p class="rotate-[-30deg] text-8xl font-black text-slate-200">DRAFT</p>
        </div>
    @endif

    <div class="mt-8 text-center">
        <p class="font-bold uppercase underline">Surat Perintah Perjalanan Dinas (SPPD)</p>
        <p>Nomor : {{ $surat->nomor_surat }}</p>
        <p class="text-xs">Lembar Ke-1</p>
    </div>

    <table class="mt-4 w-full border-collapse border border-slate-400 text-sm">
        <tbody>
            <tr>
                <td class="w-8 border border-slate-400 px-2 py-1 align-top">1</td>
                <td class="w-64 border border-slate-400 px-2 py-1 align-top">Pejabat yang memberi perintah</td>
                <td class="border border-slate-400 px-2 py-1 align-top">{{ $sppd?->pejabatPemberiPerintah?->nama_lengkap ?? '-' }}</td>
            </tr>
            <tr>
                <td class="border border-slate-400 px-2 py-1 align-top">2</td>
                <td class="border border-slate-400 px-2 py-1 align-top">Nama pegawai yang diperintahkan</td>
                <td class="border border-slate-400 px-2 py-1 align-top">
                    @forelse ($petugas as $p)
                        {{ $p->nama_penerima }}@if (! $loop->last), @endif
                    @empty
                        {{ $suratTugas ? '-' : 'Lihat Surat Tugas terkait' }}
                    @endforelse
                </td>
            </tr>
            <tr>
                <td class="border border-slate-400 px-2 py-1 align-top">3</td>
                <td class="border border-slate-400 px-2 py-1 align-top">Jabatan / Instansi</td>
                <td class="border border-slate-400 px-2 py-1 align-top">{{ $petugas->first()?->guru?->jabatan ?? '-' }}</td>
            </tr>
            <tr>
                <td class="border border-slate-400 px-2 py-1 align-top">4</td>
                <td class="border border-slate-400 px-2 py-1 align-top">Maksud perjalanan dinas</td>
                <td class="border border-slate-400 px-2 py-1 align-top">{{ $sppd?->maksud_perjalanan ?: '-' }}</td>
            </tr>
            <tr>
                <td class="border border-slate-400 px-2 py-1 align-top">5</td>
                <td class="border border-slate-400 px-2 py-1 align-top">Alat angkut yang digunakan</td>
                <td class="border border-slate-400 px-2 py-1 align-top">{{ $sppd?->alat_transportasi ?: '-' }}</td>
            </tr>
            <tr>
                <td class="border border-slate-400 px-2 py-1 align-top">6</td>
                <td class="border border-slate-400 px-2 py-1 align-top">Tempat berangkat &amp; tujuan</td>
                <td class="border border-slate-400 px-2 py-1 align-top">{{ $sppd?->tempat_berangkat ?: '-' }} &rarr; {{ $sppd?->tempat_tujuan ?: '-' }}</td>
            </tr>
            <tr>
                <td class="border border-slate-400 px-2 py-1 align-top">7</td>
                <td class="border border-slate-400 px-2 py-1 align-top">Lama perjalanan dinas</td>
                <td class="border border-slate-400 px-2 py-1 align-top">
                    {{ optional($sppd?->tanggal_berangkat)->translatedFormat('d F Y') ?: '-' }} s.d. {{ optional($sppd?->tanggal_kembali)->translatedFormat('d F Y') ?: '-' }}
                    @if ($lamaHari) ({{ $lamaHari }} hari) @endif
                </td>
            </tr>
            <tr>
                <td class="border border-slate-400 px-2 py-1 align-top">8</td>
                <td class="border border-slate-400 px-2 py-1 align-top">Pengikut</td>
                <td class="border border-slate-400 px-2 py-1 align-top">{{ $sppd?->pengikut ?: '-' }}</td>
            </tr>
            <tr>
                <td class="border border-slate-400 px-2 py-1 align-top">9</td>
                <td class="border border-slate-400 px-2 py-1 align-top">Pembebanan anggaran</td>
                <td class="border border-slate-400 px-2 py-1 align-top">{{ $sppd?->biaya_keterangan ?: '-' }}</td>
            </tr>
            <tr>
                <td class="border border-slate-400 px-2 py-1 align-top">10</td>
                <td class="border border-slate-400 px-2 py-1 align-top">Surat Tugas terkait</td>
                <td class="border border-slate-400 px-2 py-1 align-top">{{ $suratTugas?->nomor_surat ?: '-' }}</td>
            </tr>
        </tbody>
    </table>

    @include('surat.partials.ttd', ['surat' => $surat])

    <div class="break-before-page">
        <div class="mt-8 text-center">
            <p class="font-bold uppercase underline">Surat Perintah Perjalanan Dinas (SPPD)</p>
            <p>Nomor : {{ $surat->nomor_surat }}</p>
            <p class="text-xs">Lembar Ke-2</p>
        </div>

        <table class="mt-4 w-full border-collapse border border-slate-400 text-sm">
            <thead>
                <tr class="bg-slate-100">
                    <th class="border border-slate-400 px-2 py-1">Berangkat dari</th>
                    <th class="border border-slate-400 px-2 py-1">Ke</th>
                    <th class="border border-slate-400 px-2 py-1">Pada tanggal</th>
                    <th class="border border-slate-400 px-2 py-1">Tanda Tangan &amp; Cap</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-slate-400 px-2 py-1">{{ $sppd?->tempat_berangkat ?: '-' }}</td>
                    <td class="border border-slate-400 px-2 py-1">{{ $sppd?->tempat_tujuan ?: '-' }}</td>
                    <td class="border border-slate-400 px-2 py-1">{{ optional($sppd?->tanggal_berangkat)->translatedFormat('d F Y') ?: '-' }}</td>
                    <td class="h-24 border border-slate-400 px-2 py-1"></td>
                </tr>
                <tr>
                    <td class="border border-slate-400 px-2 py-1">{{ $sppd?->tempat_tujuan ?: '-' }}</td>
                    <td class="border border-slate-400 px-2 py-1">{{ $sppd?->tempat_berangkat ?: '-' }}</td>
                    <td class="border border-slate-400 px-2 py-1">{{ optional($sppd?->tanggal_kembali)->translatedFormat('d F Y') ?: '-' }}</td>
                    <td class="h-24 border border-slate-400 px-2 py-1"></td>
                </tr>
            </tbody>
        </table>

        <p class="mt-6 text-sm font-semibold">Catatan lain-lain:</p>
        <p class="whitespace-pre-line text-sm">{{ $sppd?->biaya_keterangan ?: '-' }}</p>

        @include('surat.partials.ttd', ['surat' => $surat])
    </div>
@endsection
