@php
    $nip = $surat->penandatangan->nip_niy ?? null;
@endphp

<div class="mt-10 flex justify-end">
    <div class="w-64 text-center text-sm">
        <p>{{ $surat->tempat_terbit ? $surat->tempat_terbit.', ' : '' }}{{ $surat->tanggal_surat->translatedFormat('d F Y') }}</p>
        <p>{{ $surat->jabatan_penandatangan ?: 'Kepala Sekolah' }},</p>
        <div class="h-20"></div>
        <p class="font-bold underline">{{ $surat->penandatangan->nama_lengkap ?? '.............................' }}</p>
        @if ($nip)
            <p>NIP. {{ $nip }}</p>
        @endif
    </div>
</div>
