<div class="mt-6 flex items-start justify-between text-sm">
    <table>
        <tr><td class="w-20 align-top">Nomor</td><td class="w-3 align-top">:</td><td class="align-top">{{ $surat->nomor_surat }}</td></tr>
        <tr><td class="align-top">Lampiran</td><td class="align-top">:</td><td class="align-top">{{ $surat->lampiran ?: '-' }}</td></tr>
        <tr><td class="align-top">Perihal</td><td class="align-top">:</td><td class="align-top font-semibold">{{ $surat->perihal }}</td></tr>
    </table>
    <p class="shrink-0 whitespace-nowrap">{{ $surat->tempat_terbit ? $surat->tempat_terbit.', ' : '' }}{{ $surat->tanggal_surat->translatedFormat('d F Y') }}</p>
</div>
