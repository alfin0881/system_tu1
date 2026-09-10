@php
    $tampilkanSiswa ??= false;
    $existingPenerima ??= collect();
    $existingGuruIds = $existingPenerima->pluck('guru_id')->filter()->all();
    $existingSiswaIds = $existingPenerima->pluck('siswa_id')->filter()->all();
    $existingCustom = $existingPenerima->filter(fn ($p) => $p->nama_custom)->values();
@endphp

<x-card class="space-y-4 p-5">
    <div>
        <p class="text-sm font-semibold text-gray-900">Penerima</p>
        <p class="text-xs text-gray-400">Opsional. Centang guru/karyawan dan/atau siswa yang menjadi penerima surat ini, atau tambahkan penerima pihak luar secara manual.</p>
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700">Guru / Karyawan</label>
        <div class="max-h-48 overflow-y-auto rounded-lg border border-gray-200 p-3">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                @foreach ($guruAktif as $g)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="penerima_guru[]" value="{{ $g->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" @checked(in_array($g->id, old('penerima_guru', $existingGuruIds)))>
                        {{ $g->nama_lengkap }}
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    @if ($tampilkanSiswa)
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">Orang Tua / Wali Siswa</label>
            <div class="max-h-48 overflow-y-auto rounded-lg border border-gray-200 p-3">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach ($siswaAktif as $s)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="penerima_siswa[]" value="{{ $s->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" @checked(in_array($s->id, old('penerima_siswa', $existingSiswaIds)))>
                            {{ $s->nama }} ({{ $s->kelas->nama_kelas ?? '-' }})
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div>
        <div class="mb-2 flex items-center justify-between">
            <label class="block text-sm font-medium text-gray-700">Penerima Pihak Luar</label>
            <button type="button" onclick="tambahPenerimaCustom()" class="text-xs font-medium text-blue-600 hover:text-blue-700">+ Tambah</button>
        </div>
        <div id="penerima-custom-rows" class="space-y-2">
            @foreach ($existingCustom as $i => $p)
                <div class="flex gap-2">
                    <input type="text" name="penerima_custom[{{ $i }}][nama]" value="{{ $p->nama_custom }}" placeholder="Nama" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <input type="text" name="penerima_custom[{{ $i }}][jabatan]" value="{{ $p->jabatan_custom }}" placeholder="Jabatan/Instansi" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <button type="button" onclick="this.closest('div').remove()" class="shrink-0 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-500 hover:bg-gray-50">&times;</button>
                </div>
            @endforeach
        </div>
    </div>
</x-card>

@push('scripts')
    <script>
        (function () {
            const container = document.getElementById('penerima-custom-rows');
            let penerimaCustomIndex = container ? container.querySelectorAll(':scope > div').length : 0;

            window.tambahPenerimaCustom = function () {
                const i = penerimaCustomIndex++;
                const row = document.createElement('div');
                row.className = 'flex gap-2';
                row.innerHTML = `
                    <input type="text" name="penerima_custom[${i}][nama]" placeholder="Nama" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <input type="text" name="penerima_custom[${i}][jabatan]" placeholder="Jabatan/Instansi" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <button type="button" onclick="this.closest('div').remove()" class="shrink-0 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-500 hover:bg-gray-50">&times;</button>
                `;
                container.appendChild(row);
            };
        })();
    </script>
@endpush
