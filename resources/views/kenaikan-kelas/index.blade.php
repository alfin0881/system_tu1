@extends('layouts.app')

@section('title', 'Kenaikan Kelas & Kelulusan')
@section('subtitle', 'Proses naik kelas, tinggal kelas, atau kelulusan secara massal per kelas')

@section('content')
@php $readonly = auth()->user()->role === 'kepsek'; @endphp

<div class="space-y-6">
    <x-card class="p-5">
        <form method="GET" class="max-w-md">
            <x-select name="kelas_asal_id" label="Pilih Kelas yang Akan Diproses" onchange="this.form.submit()">
                <option value="">-- Pilih kelas --</option>
                @foreach ($kelasOptions as $groupLabel => $list)
                    <optgroup label="{{ $groupLabel }}">
                        @foreach ($list as $k)
                            <option value="{{ $k->id }}" @selected(request('kelas_asal_id') == $k->id)>
                                {{ $k->nama_kelas }} ({{ $k->siswa_aktif_count }} siswa aktif)
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </x-select>
        </form>
    </x-card>

    @if ($kelasAsal)
        @if ($siswaKelasAsal->isEmpty())
            <x-card class="p-8 text-center text-sm text-gray-400 dark:text-gray-500">
                Tidak ada siswa aktif di kelas {{ $kelasAsal->nama_kelas }}.
            </x-card>
        @elseif ($readonly)
            <x-card class="p-8 text-center text-sm text-gray-400 dark:text-gray-500">
                Anda hanya memiliki akses lihat. Hubungi admin/TU untuk memproses kenaikan kelas & kelulusan.
            </x-card>
        @else
            @if (strtoupper(trim($kelasAsal->tingkat)) !== 'IX')
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-500/10 dark:text-amber-400">
                    Opsi "Lulus" hanya tersedia untuk kelas tingkat <strong>IX</strong> (tingkat tertinggi). Siswa kelas {{ $kelasAsal->tingkat }} hanya bisa Naik Kelas atau Tinggal di Kelas Ini.
                </div>
            @endif
            <form method="POST" action="{{ route('kenaikan-kelas.proses') }}">
                @csrf
                <input type="hidden" name="kelas_asal_id" value="{{ $kelasAsal->id }}">

                <x-card class="p-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-select name="tahun_ajaran_tujuan_id" label="Dicatat sebagai Riwayat Tahun Ajaran" required>
                            @foreach ($tahunAjarans as $ta)
                                <option value="{{ $ta->id }}" @selected(old('tahun_ajaran_tujuan_id', optional(\App\Models\TahunAjaran::getAktif())->id) == $ta->id)>
                                    {{ $ta->label }} @if ($ta->status === 'aktif') (Aktif) @endif
                                </option>
                            @endforeach
                        </x-select>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Terapkan Kelas Tujuan ke Semua Terpilih</label>
                            <div class="flex gap-2">
                                <select id="bulk-kelas-tujuan" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">-- Pilih kelas --</option>
                                    @foreach ($kelasOptions as $groupLabel => $list)
                                        <optgroup label="{{ $groupLabel }}">
                                            @foreach ($list as $k)
                                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <button type="button" onclick="terapkanKeSemua()" class="shrink-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">
                                    Terapkan
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Isi kolom "Kelas Tujuan" semua baris di bawah sekaligus. Baris yang aksinya "Lulus" akan diabaikan.</p>
                        </div>
                    </div>
                </x-card>

                <x-card class="mt-4 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                                <tr>
                                    <th class="w-10 px-5 py-3 font-medium">
                                        <input type="checkbox" id="pilih-semua" onclick="pilihSemua(this)" checked class="rounded border-gray-300 dark:border-gray-600">
                                    </th>
                                    <th class="px-5 py-3 font-medium">Siswa</th>
                                    <th class="px-5 py-3 font-medium">Aksi</th>
                                    <th class="px-5 py-3 font-medium">Kelas Tujuan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($siswaKelasAsal as $s)
                                    <tr>
                                        <td class="px-5 py-3">
                                            <input type="checkbox" name="pilih[]" value="{{ $s->id }}" class="baris-pilih rounded border-gray-300 dark:border-gray-600" checked>
                                        </td>
                                        <td class="px-5 py-3">
                                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $s->nama }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">NIS {{ $s->nis }}</p>
                                        </td>
                                        <td class="px-5 py-3">
                                            <select name="aksi[{{ $s->id }}]" class="baris-aksi rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" onchange="toggleKelasTujuan(this)">
                                                <option value="naik">Naik Kelas</option>
                                                <option value="tinggal">Tinggal di Kelas Ini</option>
                                                @if (strtoupper(trim($kelasAsal->tingkat)) === 'IX')
                                                    <option value="lulus">Lulus</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td class="px-5 py-3">
                                            <select name="kelas_tujuan[{{ $s->id }}]" class="baris-kelas-tujuan w-full rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                                <option value="">-- Pilih kelas tujuan --</option>
                                                @foreach ($kelasOptions as $groupLabel => $list)
                                                    <optgroup label="{{ $groupLabel }}">
                                                        @foreach ($list as $k)
                                                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>

                <div class="mt-4 flex items-center justify-end gap-3">
                    <x-button type="submit" onclick="return confirm('Proses kenaikan kelas/kelulusan untuk siswa terpilih? Aksi ini langsung mengubah data siswa.');">
                        Proses Kenaikan Kelas & Kelulusan
                    </x-button>
                </div>
            </form>

            @push('scripts')
                <script>
                    function pilihSemua(master) {
                        document.querySelectorAll('.baris-pilih').forEach(cb => cb.checked = master.checked);
                    }
                    function terapkanKeSemua() {
                        const val = document.getElementById('bulk-kelas-tujuan').value;
                        if (! val) return;
                        document.querySelectorAll('.baris-kelas-tujuan').forEach(sel => {
                            if (! sel.disabled) sel.value = val;
                        });
                    }
                    function toggleKelasTujuan(select) {
                        const row = select.closest('tr');
                        const kelasSelect = row.querySelector('.baris-kelas-tujuan');
                        kelasSelect.disabled = select.value === 'lulus';
                        if (kelasSelect.disabled) kelasSelect.value = '';
                    }
                </script>
            @endpush
        @endif
    @endif
</div>
@endsection
