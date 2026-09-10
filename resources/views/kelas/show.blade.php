@extends('layouts.app')

@section('title', 'Kelas ' . $kelas->nama_kelas)
@section('subtitle', 'Daftar siswa aktif di kelas ini')

@section('content')

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('kelas.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                &larr; Kembali ke Rekap Kelas
            </a>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Tingkat {{ $kelas->tingkat }} &middot; Wali Kelas: {{ $kelas->waliKelas->nama ?? '-' }} &middot;
                {{ $kelas->tahunAjaran->label ?? '-' }}
            </p>
        </div>

        <a href="{{ route('buku-induk.create', ['tahun_ajaran_id' => $kelas->tahun_ajaran_id]) }}" title="Siswa baru didaftarkan lewat Buku Induk, penempatan kelas menyusul lewat menu Siswa/Kenaikan Kelas">
            <x-button>+ Siswa</x-button>
        </a>
    </div>

    <x-card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Jenis Kelamin</th>
                        <th class="px-5 py-3 font-medium">Kontak Ortu</th>
                        <th class="px-5 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($siswa as $s)
                        <tr>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($s->foto)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($s->foto) }}" class="h-8 w-8 shrink-0 rounded-full object-cover" alt="">
                                    @else
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-semibold text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                                            {{ strtoupper(substr($s->nama, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('siswa.show', $s) }}" class="truncate font-medium text-gray-900 hover:text-blue-600 dark:text-gray-100 dark:hover:text-blue-400">{{ $s->nama }}</a>
                                        <p class="truncate text-xs text-gray-400 dark:text-gray-500">NIS {{ $s->nis }} @if ($s->nisn) &middot; NISN {{ $s->nisn }} @endif</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->no_hp_ortu ?: '-' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('siswa.edit', $s) }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Ubah</a>
                                    <form method="POST" action="{{ route('siswa.destroy', $s) }}" onsubmit="return confirm('Hapus data siswa {{ $s->nama }}? Riwayat kelas dan mutasi terkait akan ikut terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-400">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                Belum ada siswa aktif di kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
