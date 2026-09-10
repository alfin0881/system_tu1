@php
    $sekolah ??= \App\Models\SekolahProfil::aktif();
@endphp

{{-- Kop surat baku: logo di kiri, identitas sekolah bertingkat di tengah,
     kotak tipis di atas/kiri/kanan + garis ganda tebal di bawah sebagai
     pemisah — mengikuti persis format kop surat resmi sekolah. --}}
<div class="border-x-2 border-t-2 border-slate-900 px-3 pt-3">
    <div class="flex items-start gap-4 border-b-4 border-double border-slate-900 pb-2">
        <div class="w-20 shrink-0">
            @if ($sekolah?->logo)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($sekolah->logo) }}" alt="Logo Sekolah" class="h-20 w-20 object-contain">
            @endif
        </div>
        <div class="flex-1 text-center leading-snug">
            @if ($sekolah?->lembaga_induk)
                <p class="font-bold uppercase">{{ $sekolah->lembaga_induk }}</p>
            @endif
            @if ($sekolah?->nama_yayasan)
                <p class="font-bold uppercase">{{ $sekolah->nama_yayasan }}</p>
            @endif
            @if ($sekolah?->dasar_hukum)
                <p class="text-sm font-bold">{{ $sekolah->dasar_hukum }}</p>
            @endif
            <p class="text-3xl font-bold uppercase">{{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }}</p>
            @if ($sekolah?->akreditasi)
                <p class="font-bold uppercase">TERAKREDITASI {{ $sekolah->akreditasi }}</p>
            @endif
            @if ($sekolah?->nomor_izin)
                <p class="font-bold">Nomor : {{ $sekolah->nomor_izin }}</p>
            @endif
            @if ($sekolah?->alamat)
                <p class="font-serif text-sm italic">{{ $sekolah->alamat_lengkap }}</p>
            @endif
            @if ($sekolah?->email || $sekolah?->website)
                <p class="font-serif text-sm italic">
                    @if ($sekolah->email)e-mail : <span class="text-indigo-700 underline">{{ $sekolah->email }}</span>@endif
                    @if ($sekolah->email && $sekolah->website)&nbsp;&nbsp;&nbsp;@endif
                    @if ($sekolah->website)Website : {{ $sekolah->website }}@endif
                </p>
            @endif
        </div>
        <div class="w-20 shrink-0"></div>
    </div>
</div>
