@extends('layouts.print')

@section('title', $surat->nomor_surat)

@section('content')
    @include('surat.partials.kop-surat', ['sekolah' => $sekolah])

    @if ($surat->status !== 'final')
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
            <p class="rotate-[-30deg] text-8xl font-black text-slate-200">DRAFT</p>
        </div>
    @endif

    @if ($surat->jenisSurat->kategori === 'keterangan')
        <div class="mt-8 text-center">
            <p class="font-bold uppercase underline">{{ $surat->jenisSurat->nama }}</p>
            <p>Nomor : {{ $surat->nomor_surat }}</p>
        </div>

        <p class="mt-6 whitespace-pre-line text-justify indent-8">{{ $surat->isi_surat }}</p>

        <p class="mt-4 text-justify">Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>

        @include('surat.partials.ttd', ['surat' => $surat])
    @endif

    @if ($surat->jenisSurat->kategori === 'keputusan')
        <div class="mt-8 text-center">
            <p class="font-bold uppercase underline">{{ $surat->jenisSurat->nama }}</p>
            <p class="font-bold">NOMOR : {{ $surat->nomor_surat }}</p>
            <p class="mt-2 font-bold uppercase">TENTANG</p>
            <p class="font-bold uppercase">{{ $surat->perihal }}</p>
        </div>

        <p class="mt-6">{{ $surat->jabatan_penandatangan ?: 'Kepala Sekolah' }},</p>

        <div class="mt-3">
            <p class="font-semibold">Menimbang :</p>
            <p class="whitespace-pre-line text-justify indent-8">{{ $surat->isi_surat }}</p>
        </div>

        @if ($surat->dasar_surat)
            <div class="mt-3">
                <p class="font-semibold">Mengingat :</p>
                <p class="whitespace-pre-line text-justify">{{ $surat->dasar_surat }}</p>
            </div>
        @endif

        <p class="mt-4 text-center font-bold uppercase">MEMUTUSKAN</p>
        <table class="mt-2 w-full text-sm">
            <tr>
                <td class="w-32 align-top font-semibold">Menetapkan</td>
                <td class="w-4 align-top">:</td>
                <td class="align-top font-semibold uppercase">{{ $surat->perihal }}</td>
            </tr>
        </table>
        <table class="mt-3 w-full text-sm">
            <tr>
                <td class="w-32 align-top font-semibold">KESATU</td>
                <td class="w-4 align-top">:</td>
                <td class="align-top">{{ $surat->perihal }}, sebagaimana diuraikan di atas.</td>
            </tr>
            <tr>
                <td class="align-top font-semibold">KEDUA</td>
                <td class="align-top">:</td>
                <td class="align-top">Surat keputusan ini mulai berlaku sejak tanggal ditetapkan, dengan ketentuan akan diadakan perbaikan sebagaimana mestinya apabila di kemudian hari terdapat kekeliruan dalam penetapan ini.</td>
            </tr>
        </table>

        @include('surat.partials.ttd', ['surat' => $surat])

        @if ($surat->penerima->isNotEmpty())
            <div class="break-before-page">
                <p class="mt-8 text-sm">Lampiran Surat Keputusan</p>
                <p class="text-sm">Nomor : {{ $surat->nomor_surat }}</p>
                <p class="mb-4 text-sm">Tentang : {{ $surat->perihal }}</p>
                <table class="w-full border-collapse border border-slate-400 text-sm">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-400 px-2 py-1">No.</th>
                            <th class="border border-slate-400 px-2 py-1">Nama</th>
                            <th class="border border-slate-400 px-2 py-1">Jabatan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($surat->penerima as $i => $p)
                            <tr>
                                <td class="border border-slate-400 px-2 py-1 text-center">{{ $i + 1 }}</td>
                                <td class="border border-slate-400 px-2 py-1">{{ $p->nama_penerima }}</td>
                                <td class="border border-slate-400 px-2 py-1">{{ $p->jabatan_custom ?: ($p->guru?->jabatan ?: '-') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    @if ($surat->jenisSurat->kategori === 'undangan')
        @include('surat.partials.header-surat', ['surat' => $surat])

        <div class="mt-4 text-sm">
            <p>Kepada Yth.</p>
            @if ($surat->penerima->count() > 0 && $surat->penerima->count() <= 5)
                @foreach ($surat->penerima as $p)
                    <p>{{ $p->nama_penerima }}</p>
                @endforeach
            @elseif ($surat->penerima->count() > 5)
                <p>(Daftar penerima terlampir)</p>
            @else
                <p>.....................................</p>
            @endif
            <p>di Tempat</p>
        </div>

        <p class="mt-6 whitespace-pre-line text-justify indent-8">{{ $surat->isi_surat }}</p>
        <p class="mt-4 text-justify">Demikian undangan ini kami sampaikan, atas perhatian dan kehadirannya kami ucapkan terima kasih.</p>

        @include('surat.partials.ttd', ['surat' => $surat])

        @if ($surat->penerima->count() > 5)
            <div class="break-before-page">
                <p class="mb-4 mt-8 text-sm">Lampiran Surat Nomor : {{ $surat->nomor_surat }}</p>
                <table class="w-full border-collapse border border-slate-400 text-sm">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-400 px-2 py-1">No.</th>
                            <th class="border border-slate-400 px-2 py-1">Nama</th>
                            <th class="border border-slate-400 px-2 py-1">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($surat->penerima as $i => $p)
                            <tr>
                                <td class="border border-slate-400 px-2 py-1 text-center">{{ $i + 1 }}</td>
                                <td class="border border-slate-400 px-2 py-1">{{ $p->nama_penerima }}</td>
                                <td class="border border-slate-400 px-2 py-1">{{ $p->jabatan_custom ?: ($p->guru?->jabatan ?: '-') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    @if ($surat->jenisSurat->kategori === 'tugas')
        <div class="mt-8 text-center">
            <p class="font-bold uppercase underline">{{ $surat->jenisSurat->nama }}</p>
            <p>Nomor : {{ $surat->nomor_surat }}</p>
        </div>

        <p class="mt-6 text-justify indent-8">Yang bertanda tangan di bawah ini, {{ $surat->jabatan_penandatangan ?: 'Kepala Sekolah' }} {{ $sekolah->nama_sekolah ?? '' }}, dengan ini menugaskan kepada:</p>

        <table class="mt-3 w-full border-collapse border border-slate-400 text-sm">
            <thead>
                <tr class="bg-slate-100">
                    <th class="border border-slate-400 px-2 py-1">No.</th>
                    <th class="border border-slate-400 px-2 py-1">Nama</th>
                    <th class="border border-slate-400 px-2 py-1">Jabatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($surat->penerima as $i => $p)
                    <tr>
                        <td class="border border-slate-400 px-2 py-1 text-center">{{ $i + 1 }}</td>
                        <td class="border border-slate-400 px-2 py-1">{{ $p->nama_penerima }}</td>
                        <td class="border border-slate-400 px-2 py-1">{{ $p->jabatan_custom ?: ($p->guru?->jabatan ?: '-') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="border border-slate-400 px-2 py-1 text-center text-slate-400">-</td></tr>
                @endforelse
            </tbody>
        </table>

        <p class="mt-4 whitespace-pre-line text-justify indent-8">Untuk {{ $surat->isi_surat }}</p>
        <p class="mt-4 text-justify">Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.</p>

        @include('surat.partials.ttd', ['surat' => $surat])
    @endif
@endsection
