@extends('layouts.app')

@section('title', 'Siswa Pindah / Keluar')
@section('subtitle', 'Mencatat siswa aktif yang pindah ke sekolah lain — status siswa otomatis berubah menjadi Pindah')

@section('content')
@php
    // Map data siswa aktif ke array JSON untuk Alpine.js
    $siswaListJson = $siswaAktif->map(function ($s) {
        return [
            'id' => $s->id,
            'nama' => $s->nama,
            'nis' => $s->nis,
            'kelas_id' => $s->kelas_id,
            'nama_kelas' => $s->kelas?->nama_kelas ?? 'Tanpa Kelas',
        ];
    })->values();

    // Grouping kelas untuk dropdown filter di modal
    $kelasGrouped = $siswaAktif->pluck('kelas')->filter()->unique('id')->groupBy('tingkat');
@endphp

<div class="mx-auto max-w-2xl">
    <x-card class="p-6">
        <form method="POST" action="{{ route('mutasi-siswa.keluar.store') }}" class="space-y-6">
            @csrf

            {{-- COMPONENT COMPONENT PICKER SISWA (ALPINE.JS) --}}
            <div x-data="siswaPicker({{ json_encode($siswaListJson) }}, '{{ old('siswa_id') }}')" class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Siswa <span class="text-rose-500 dark:text-rose-400">*</span></label>
                
                {{-- Input Hidden yang dikirim ke backend --}}
                <input type="hidden" name="siswa_id" :value="selectedSiswa?.id" required>

                {{-- Tampilan Input & Tombol Cari --}}
                <div class="flex items-center gap-2">
                    <input 
                        type="text" 
                        readonly 
                        :value="selectedSiswa ? `${selectedSiswa.nama} — NIS ${selectedSiswa.nis || '-'} (${selectedSiswa.nama_kelas})` : ''"
                        placeholder="-- Klik tombol cari untuk memilih siswa aktif --"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-800 focus:outline-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                        @click="openModal = true"
                    >
                    
                    <button 
                        type="button" 
                        @click="openModal = true"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Cari Siswa
                    </button>
                </div>
                
                @error('siswa_id')
                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror

                {{-- MODAL PENCARIAN & FILTER --}}
                <div 
                    x-show="openModal" 
                    x-cloak 
                    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm"
                >
                    <div @click.away="openModal = false" class="w-full max-w-xl rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
                        
                        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Pilih Siswa Aktif</h3>
                            <button type="button" @click="openModal = false" class="text-xl font-bold text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">&times;</button>
                        </div>

                        {{-- Filter Area --}}
                        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <input 
                                type="text" 
                                x-model="search" 
                                placeholder="Cari Nama / NIS..."
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600"
                                x-ref="searchInput"
                            >

                            <select x-model="filterKelas" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600">
                                <option value="">-- Semua Kelas --</option>
                                @foreach ($kelasGrouped as $groupLabel => $kelasList)
                                    <optgroup label="{{ $groupLabel }}">
                                        @foreach ($kelasList as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tabel List Hasil Pencarian --}}
                        <div class="max-h-64 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                                <thead class="sticky top-0 bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    <tr>
                                        <th class="px-4 py-2.5">Siswa</th>
                                        <th class="px-4 py-2.5">Kelas</th>
                                        <th class="px-4 py-2.5 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <template x-for="item in filteredSiswa" :key="item.id">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                            <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-gray-100">
                                                <div x-text="item.nama"></div>
                                                <div class="text-xs font-normal text-gray-400 dark:text-gray-500" x-text="'NIS: ' + (item.nis || '-')"></div>
                                            </td>
                                            <td class="px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300" x-text="item.nama_kelas"></td>
                                            <td class="px-4 py-2.5 text-right">
                                                <button 
                                                    type="button" 
                                                    @click="select(item)"
                                                    class="rounded bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20"
                                                >
                                                    Pilih
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="filteredSiswa.length === 0">
                                        <tr>
                                            <td colspan="3" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500">
                                                Siswa tidak ditemukan.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-input type="date" name="tanggal" label="Tanggal Keluar" :value="old('tanggal')" required />
                <x-input name="tujuan_sekolah" label="Sekolah Tujuan" :value="old('tujuan_sekolah')" required />
                <x-input name="no_surat" label="No. Surat Pindah" :value="old('no_surat')" placeholder="Opsional" />
            </div>

            <x-textarea name="alasan" label="Alasan / Keterangan" :value="old('alasan')" />

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                <a href="{{ route('mutasi-siswa.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Batal</a>
                <x-button type="submit" onclick="return confirm('Catat siswa ini pindah/keluar? Status siswa akan otomatis berubah menjadi Pindah dan kelas dikosongkan.');">Simpan</x-button>
            </div>
        </form>
    </x-card>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('siswaPicker', (initialSiswa, oldId) => ({
            openModal: false,
            search: '',
            filterKelas: '',
            selectedSiswa: null,
            siswaList: initialSiswa,

            init() {
                if (oldId) {
                    this.selectedSiswa = this.siswaList.find(s => s.id == oldId) || null;
                }
            },

            get filteredSiswa() {
                return this.siswaList.filter(s => {
                    const matchesSearch = s.nama.toLowerCase().includes(this.search.toLowerCase()) || 
                                          (s.nis && s.nis.toLowerCase().includes(this.search.toLowerCase()));
                    const matchesKelas = this.filterKelas === '' || s.kelas_id == this.filterKelas;
                    return matchesSearch && matchesKelas;
                });
            },

            select(siswa) {
                this.selectedSiswa = siswa;
                this.openModal = false;
            }
        }));
    });
</script>
@endsection