@php
    $tahunAjaran ??= null;
    $readonly = auth()->user()->role === 'kepsek';
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <x-input
        name="nama"
        label="Tahun Ajaran"
        placeholder=""
        :value="$tahunAjaran->nama ?? ''"
        required
        :disabled="$readonly"
    />

    <x-select name="semester" label="Semester" required :disabled="$readonly">
        <option value="Ganjil" @selected(old('semester', $tahunAjaran->semester ?? 'Ganjil') === 'Ganjil')>Ganjil</option>
        <option value="Genap" @selected(old('semester', $tahunAjaran->semester ?? 'Ganjil') === 'Genap')>Genap</option>
    </x-select>

    <x-select name="status" label="Status" required :disabled="$readonly">
        <option value="nonaktif" @selected(old('status', $tahunAjaran->status ?? 'nonaktif') === 'nonaktif')>Nonaktif</option>
        <option value="aktif" @selected(old('status', $tahunAjaran->status ?? 'nonaktif') === 'aktif')>Aktif (jadikan tahun ajaran berjalan)</option>
    </x-select>
</div>

<p class="text-xs text-gray-400 dark:text-gray-500">
    Jika status diset "Aktif", tahun ajaran lain otomatis dinonaktifkan — hanya satu yang boleh berjalan pada satu waktu.
</p>