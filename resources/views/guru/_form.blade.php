@php
    $guru ??= null;
@endphp

@if ($guru?->foto)
    <div class="flex items-center gap-4">
        <img src="{{ \Illuminate\Support\Facades\Storage::url($guru->foto) }}" alt="Foto {{ $guru->nama }}" class="h-16 w-16 rounded-full border border-gray-200 object-cover dark:border-gray-700">
        <p class="text-xs text-gray-400 dark:text-gray-500">Foto saat ini. Unggah file baru di bawah untuk menggantinya.</p>
    </div>
@endif

<div>
    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Foto</label>
    <input
        type="file"
        name="foto"
        accept="image/*"
        class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 dark:text-gray-300 dark:file:bg-blue-500/10 dark:file:text-blue-300 dark:hover:file:bg-blue-500/20"
    >
    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">PNG/JPG, maksimal 1MB. Opsional.</p>
    @error('foto')
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div>
    <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Identitas</p>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-input name="nama" label="Nama" :value="$guru->nama ?? ''" placeholder="Ahmad Fauzi" required />
        <x-input name="gelar_akademik" label="Gelar Akademik" :value="$guru->gelar_akademik ?? ''" placeholder="S.Pd., M.M." />
        <x-select name="jenis_kelamin" label="JK (L/P)" required>
            <option value="">-- Pilih --</option>
            <option value="L" @selected(old('jenis_kelamin', $guru->jenis_kelamin ?? '') === 'L')>Laki-laki</option>
            <option value="P" @selected(old('jenis_kelamin', $guru->jenis_kelamin ?? '') === 'P')>Perempuan</option>
        </x-select>
        <x-input name="nip_niy" label="NIP" :value="$guru->nip_niy ?? ''" placeholder="Kosongkan jika belum ada" />
        <x-input name="no_ktp" label="No KTP" :value="$guru->no_ktp ?? ''" />
        <x-input name="tempat_lahir" label="Tempat Lahir" :value="$guru->tempat_lahir ?? ''" />
        <x-input type="date" name="tanggal_lahir" label="Tanggal Lahir" :value="optional($guru?->tanggal_lahir)->format('Y-m-d')" />
        <x-input name="pendidikan_terakhir" label="Pendidikan Terakhir" :value="$guru->pendidikan_terakhir ?? ''" placeholder="S1 Pendidikan Bahasa Indonesia" />
        <x-input name="nama_ibu_kandung" label="Ibu Kandung" :value="$guru->nama_ibu_kandung ?? ''" />
    </div>
</div>

<div>
    <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Jabatan &amp; Kepegawaian</p>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-input name="jabatan" label="Jabatan" :value="$guru->jabatan ?? ''" placeholder="Guru Mapel, Kepala Sekolah, Staff TU, dst." required />
        <x-input name="mengajar" label="Mengajar" :value="$guru->mengajar ?? ''" placeholder="Mata pelajaran yang diampu" />
        <x-input name="golongan_pangkat" label="Golongan" :value="$guru->golongan_pangkat ?? ''" placeholder="III/c, Penata Muda" />
        <x-select name="status_kepegawaian" label="Status PNS/Non PNS" required>
            @foreach (['PNS' => 'PNS', 'Non PNS' => 'Non PNS'] as $val => $label)
                <option value="{{ $val }}" @selected(old('status_kepegawaian', $guru->status_kepegawaian ?? 'Non PNS') === $val)>{{ $label }}</option>
            @endforeach
        </x-select>
        <x-select name="status" label="Status" required>
            @foreach (['aktif' => 'Aktif', 'pensiun' => 'Pensiun', 'pindah' => 'Pindah'] as $val => $label)
                <option value="{{ $val }}" @selected(old('status', $guru->status ?? 'aktif') === $val)>{{ $label }}</option>
            @endforeach
        </x-select>
    </div>
</div>

<div>
    <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">TMT (Terhitung Mulai Tanggal)</p>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-input type="date" name="tmt_madrasah" label="TMT di Madrasah" :value="optional($guru?->tmt_madrasah)->format('Y-m-d')" />
        <x-input type="date" name="tmt_pns" label="TMT PNS" :value="optional($guru?->tmt_pns)->format('Y-m-d')" />
        <x-input type="date" name="tmt_golongan" label="TMT Golongan" :value="optional($guru?->tmt_golongan)->format('Y-m-d')" />
    </div>
</div>

<div>
    <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Alamat &amp; Kontak</p>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-input name="kelurahan" label="Kelurahan" :value="$guru->kelurahan ?? ''" />
        <x-input name="kecamatan" label="Kecamatan" :value="$guru->kecamatan ?? ''" />
        <div class="sm:col-span-2">
            <x-textarea name="alamat" label="Alamat Rumah Lengkap" :value="$guru->alamat ?? ''" />
        </div>
        <x-input name="no_hp" label="No. HP" :value="$guru->no_hp ?? ''" />
        <x-input type="email" name="email" label="Email" :value="$guru->email ?? ''" />
    </div>
</div>
