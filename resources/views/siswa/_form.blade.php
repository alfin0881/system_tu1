@php
    $siswa ??= null;
    $kelasGrouped ??= collect();
    $showStatus ??= true;
    $showKelas ??= true;
@endphp

@if ($siswa?->foto)
    <div class="flex items-center gap-4">
        <img src="{{ \Illuminate\Support\Facades\Storage::url($siswa->foto) }}" alt="Foto {{ $siswa->nama }}" class="h-16 w-16 rounded-full border border-gray-200 object-cover dark:border-gray-700">
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
        <x-input name="nis" label="NIS" :value="$siswa->nis ?? ''" required />
        <x-input name="nis_lokal" label="NIS Lokal" :value="$siswa->nis_lokal ?? ''" />
        <x-input name="nama" label="Nama Lengkap" :value="$siswa->nama ?? ''" required />
        <x-input name="nisn" label="NISN" :value="$siswa->nisn ?? ''" placeholder="Kosongkan jika belum ada" />
        <x-input name="nik" label="Nomor Induk Kependudukan (NIK)" :value="$siswa->nik ?? ''" />

        <x-select name="jenis_kelamin" label="Jenis Kelamin (L/P)" required>
            <option value="">-- Pilih --</option>
            <option value="L" @selected(old('jenis_kelamin', $siswa->jenis_kelamin ?? '') === 'L')>Laki-laki</option>
            <option value="P" @selected(old('jenis_kelamin', $siswa->jenis_kelamin ?? '') === 'P')>Perempuan</option>
        </x-select>
        <x-input name="tempat_lahir" label="Tempat Lahir" :value="$siswa->tempat_lahir ?? ''" />
        <x-input type="date" name="tanggal_lahir" label="Tanggal Lahir" :value="optional($siswa?->tanggal_lahir)->format('Y-m-d')" />

        <x-input name="agama" label="Agama" :value="$siswa->agama ?? ''" />
    </div>
</div>

<div>
    <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Data Keluarga</p>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-input name="status_dalam_keluarga" label="Status dalam Keluarga" :value="$siswa->status_dalam_keluarga ?? ''" placeholder="mis. Anak Kandung" />
        <x-input type="number" name="anak_ke" label="Anak Ke" :value="$siswa->anak_ke ?? ''" min="1" />
        <x-input type="number" name="jumlah_saudara_kandung" label="Jumlah Saudara Kandung" :value="$siswa->jumlah_saudara_kandung ?? ''" min="0" />
        <x-input name="no_kk" label="No. Kartu Keluarga" :value="$siswa->no_kk ?? ''" />
        <x-input name="kepala_keluarga" label="Kepala Keluarga" :value="$siswa->kepala_keluarga ?? ''" />
    </div>
</div>

<div>
    <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Data Ayah</p>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-input name="nama_ayah" label="Nama Ayah" :value="$siswa->nama_ayah ?? ''" />
        <x-input name="status_ayah" label="Status Ayah" :value="$siswa->status_ayah ?? ''" placeholder="mis. Kandung / Wafat" />
        <x-input name="nik_ayah" label="NIK Ayah" :value="$siswa->nik_ayah ?? ''" />
        <x-input name="tempat_lahir_ayah" label="Tempat Lahir Ayah" :value="$siswa->tempat_lahir_ayah ?? ''" />
        <x-input type="date" name="tanggal_lahir_ayah" label="Tanggal Lahir Ayah" :value="optional($siswa?->tanggal_lahir_ayah)->format('Y-m-d')" />
        <x-input name="pendidikan_terakhir_ayah" label="Pendidikan Terakhir Ayah" :value="$siswa->pendidikan_terakhir_ayah ?? ''" />
        <x-input name="pekerjaan_ayah" label="Pekerjaan Ayah" :value="$siswa->pekerjaan_ayah ?? ''" />
        <x-input name="penghasilan_ayah" label="Penghasilan Ayah" :value="$siswa->penghasilan_ayah ?? ''" />
    </div>
</div>

<div>
    <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Data Ibu</p>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-input name="nama_ibu" label="Nama Ibu" :value="$siswa->nama_ibu ?? ''" />
        <x-input name="status_ibu" label="Status Ibu" :value="$siswa->status_ibu ?? ''" placeholder="mis. Kandung / Wafat" />
        <x-input name="nik_ibu" label="NIK Ibu" :value="$siswa->nik_ibu ?? ''" />
        <x-input name="tempat_lahir_ibu" label="Tempat Lahir Ibu" :value="$siswa->tempat_lahir_ibu ?? ''" />
        <x-input type="date" name="tanggal_lahir_ibu" label="Tanggal Lahir Ibu" :value="optional($siswa?->tanggal_lahir_ibu)->format('Y-m-d')" />
        <x-input name="pendidikan_terakhir_ibu" label="Pendidikan Terakhir Ibu" :value="$siswa->pendidikan_terakhir_ibu ?? ''" />
        <x-input name="pekerjaan_ibu" label="Pekerjaan Ibu" :value="$siswa->pekerjaan_ibu ?? ''" />
        <x-input name="penghasilan_ibu" label="Penghasilan Ibu" :value="$siswa->penghasilan_ibu ?? ''" />
    </div>
</div>

<div>
    <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Data Wali</p>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-input name="nama_wali" label="Nama Wali" :value="$siswa->nama_wali ?? ''" placeholder="Kosongkan jika sama dengan orang tua" />
        <x-input name="status_wali" label="Status Wali" :value="$siswa->status_wali ?? ''" />
        <x-input name="nik_wali" label="NIK Wali" :value="$siswa->nik_wali ?? ''" />
        <x-input name="tempat_lahir_wali" label="Tempat Lahir Wali" :value="$siswa->tempat_lahir_wali ?? ''" />
        <x-input type="date" name="tanggal_lahir_wali" label="Tanggal Lahir Wali" :value="optional($siswa?->tanggal_lahir_wali)->format('Y-m-d')" />
        <x-select name="jenis_kelamin_wali" label="Jenis Kelamin Wali">
            <option value="">-- Pilih --</option>
            <option value="L" @selected(old('jenis_kelamin_wali', $siswa->jenis_kelamin_wali ?? '') === 'L')>Laki-laki</option>
            <option value="P" @selected(old('jenis_kelamin_wali', $siswa->jenis_kelamin_wali ?? '') === 'P')>Perempuan</option>
        </x-select>
        <x-input name="pendidikan_terakhir_wali" label="Pendidikan Terakhir Wali" :value="$siswa->pendidikan_terakhir_wali ?? ''" />
        <x-input name="pekerjaan_wali" label="Pekerjaan Wali" :value="$siswa->pekerjaan_wali ?? ''" />
        <x-input name="penghasilan_wali" label="Penghasilan Wali" :value="$siswa->penghasilan_wali ?? ''" />
    </div>
</div>

<div>
    <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Kontak &amp; Alamat</p>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-input name="no_hp_ortu" label="No. HP Orang Tua/Wali" :value="$siswa->no_hp_ortu ?? ''" />
        <x-input name="rt_rw_jl" label="RT/RW/Jl" :value="$siswa->rt_rw_jl ?? ''" />
        <x-input name="dukuh" label="Dukuh" :value="$siswa->dukuh ?? ''" />
        <x-input name="desa_kelurahan" label="Desa/Kelurahan" :value="$siswa->desa_kelurahan ?? ''" />
        <x-input name="kecamatan" label="Kecamatan" :value="$siswa->kecamatan ?? ''" />
        <x-input name="kabupaten_kota" label="Kabupaten/Kota" :value="$siswa->kabupaten_kota ?? ''" />
        <x-input name="provinsi" label="Provinsi" :value="$siswa->provinsi ?? ''" />
        <x-input name="kode_pos" label="Kode Pos" :value="$siswa->kode_pos ?? ''" />
        <div class="sm:col-span-2 lg:col-span-3">
            <x-textarea name="alamat" label="Alamat" :value="$siswa->alamat ?? ''" />
        </div>
    </div>
</div>

<div>
    <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Sekolah Asal &amp; KIP</p>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-input name="jenis_sekolah" label="Jenis Sekolah" :value="$siswa->jenis_sekolah ?? ''" />
        <x-input name="status_sekolah" label="Status Sekolah" :value="$siswa->status_sekolah ?? ''" placeholder="Negeri / Swasta" />
        {{-- <x-input name="npsn_nsm" label="NPSN/NSM" :value="$siswa->npsn_nsm ?? ''" /> --}}
        <x-input name="nama_sekolah" label="Nama Sekolah" :value="$siswa->nama_sekolah ?? ''" />
        <x-input name="status_kepemilikan_kip" label="Status Kepemilikan KIP" :value="$siswa->status_kepemilikan_kip ?? ''" />
        <x-input name="no_kip" label="No. KIP" :value="$siswa->no_kip ?? ''" />
        <x-input name="pondok_pesantren" label="Pondok Pesantren" :value="$siswa->pondok_pesantren ?? ''" />
    </div>
</div>

<div>
    <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">Akademik</p>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @if ($showKelas)
            <x-select name="kelas_id" label="Kelas" :required="! $showStatus">
                <option value="">-- Belum ada kelas (alumni/pindah) --</option>
                @foreach ($kelasGrouped as $groupLabel => $kelasList)
                    <optgroup label="{{ $groupLabel }}">
                        @foreach ($kelasList as $k)
                            <option value="{{ $k->id }}" @selected(old('kelas_id', $siswa->kelas_id ?? '') == $k->id)>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </x-select>
        @endif

        <x-input type="date" name="tanggal_masuk" label="Tanggal Masuk" :value="optional($siswa?->tanggal_masuk)->format('Y-m-d')" />

        @if ($showStatus)
            <x-select name="status" label="Status" required>
                @foreach (['aktif' => 'Aktif', 'alumni' => 'Alumni', 'pindah' => 'Pindah', 'keluar' => 'Keluar'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $siswa->status ?? 'aktif') === $val)>{{ $label }}</option>
                @endforeach
            </x-select>
        @endif
    </div>
</div>