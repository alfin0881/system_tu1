@php
    $jenisSurat ??= null;
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <x-input name="nama" label="Nama Jenis Surat" :value="$jenisSurat->nama ?? ''" placeholder="" required />
    <x-input name="kode" label="Kode" :value="$jenisSurat->kode ?? ''" placeholder="" required />

    <x-select name="kategori" label="Kategori" required>
        <option value="">-- Pilih kategori --</option>
        @foreach (['keterangan' => 'Keterangan', 'keputusan' => 'Keputusan (SK)', 'undangan' => 'Undangan', 'tugas' => 'Surat Tugas', 'sppd' => 'SPPD', 'lainnya' => 'Lainnya'] as $val => $label)
            <option value="{{ $val }}" @selected(old('kategori', $jenisSurat->kategori ?? '') === $val)>{{ $label }}</option>
        @endforeach
    </x-select>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Format Nomor Surat</label>
        <input type="text" disabled value="{{ \App\Http\Controllers\SuratController::FORMAT_NOMOR_FIX }}"
            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400">
    </div>
</div>

<p class="-mt-2 text-xs text-gray-400 dark:text-gray-500">
    Format nomor surat sudah <strong>fix/baku</strong> untuk semua jenis surat dan tidak bisa diubah dari sini. Contoh hasilnya: <code class="rounded bg-gray-100 px-1 dark:bg-gray-700">087/MTs.18/G/L.PM/VIII/2026</code>.
    Yang mengikuti jenis surat ini hanya bagian <strong>Kode</strong> di atas (pada contoh: <code class="rounded bg-gray-100 px-1 dark:bg-gray-700">G</code>) dan nomor urut yang otomatis bertambah &amp; reset tiap tahun.
</p>

<div class="space-y-2 border-t border-gray-100 pt-5 dark:border-gray-800">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Surat (.docx){{ $jenisSurat ? '' : ' *' }}</label>

    @if ($jenisSurat && $jenisSurat->template_path)
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900">
            <span class="truncate text-gray-700 dark:text-gray-300">📄 {{ $jenisSurat->template_nama_asli ?? basename($jenisSurat->template_path) }}</span>
            <a href="{{ \Illuminate\Support\Facades\Storage::url($jenisSurat->template_path) }}" target="_blank" class="shrink-0 text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">Lihat file</a>
        </div>
        @if (! empty($jenisSurat->template_variables))
            <p class="text-xs text-gray-400 dark:text-gray-500">
                Placeholder terdeteksi:
                @foreach ($jenisSurat->template_variables as $ph)
                    <code class="rounded bg-gray-100 px-1 dark:bg-gray-700">${{ '{' }}{{ $ph }}{{ '}' }}</code>
                @endforeach
            </p>
        @endif
        <p class="text-xs text-gray-400 dark:text-gray-500">Upload file baru di bawah ini untuk mengganti template (opsional).</p>
    @endif

    <input
        type="file"
        name="template"
        accept=".docx"
        class="block w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:file:bg-blue-500/10 dark:file:text-blue-300 dark:hover:file:bg-blue-500/20"
    >
    @error('template')
        <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    <p class="text-xs text-gray-400 dark:text-gray-500">
        Buat surat di Microsoft Word / Google Docs seperti biasa lengkap dengan kop surat, lalu tandai bagian yang berubah-ubah dengan placeholder
        <code class="rounded bg-gray-100 px-1 dark:bg-gray-700">${nama_placeholder}</code>, contoh <code class="rounded bg-gray-100 px-1 dark:bg-gray-700">${nama_siswa}</code>,
        <code class="rounded bg-gray-100 px-1 dark:bg-gray-700">${tanggal_lahir}</code>, dsb — lalu simpan/export sebagai .docx dan upload di sini.
        Karena kop surat sudah ada di dalam file docx, sistem tidak lagi menambahkan kop surat otomatis.
        Sistem akan mendeteksi seluruh placeholder tersebut dan otomatis menjadikannya kolom isian pada form pembuatan surat.
    </p>
</div>

<x-textarea name="deskripsi" label="Deskripsi" :value="$jenisSurat->deskripsi ?? ''" />

<label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
    <input type="checkbox" name="aktif" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:text-blue-400" @checked(old('aktif', $jenisSurat->aktif ?? true))>
    Aktif (tampil sebagai pilihan saat membuat surat)
</label>
