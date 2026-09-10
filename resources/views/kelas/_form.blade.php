@php
    $kelas ??= null;
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <x-input name="nama_kelas" label="Nama Kelas" placeholder="VII - A" :value="$kelas->nama_kelas ?? ''" required />
    <x-input name="tingkat" label="Tingkat" placeholder="VII / VIII / IX" :value="$kelas->tingkat ?? ''" required />

    <x-select name="tahun_ajaran_id" label="Tahun Ajaran" required>
        <option value="">-- Pilih Tahun Ajaran --</option>
        @foreach ($tahunAjarans as $ta)
            <option value="{{ $ta->id }}" @selected(old('tahun_ajaran_id', $kelas->tahun_ajaran_id ?? '') == $ta->id)>
                {{ $ta->label }} @if ($ta->status === 'aktif') (Aktif) @endif
            </option>
        @endforeach
    </x-select>

    <x-select name="wali_kelas_id" label="Wali Kelas">
        <option value="">-- Belum ada wali kelas --</option>
        @foreach ($guru as $g)
            <option value="{{ $g->id }}" @selected(old('wali_kelas_id', $kelas->wali_kelas_id ?? '') == $g->id)>
                {{ $g->nama_lengkap }}
            </option>
        @endforeach
    </x-select>
</div>
