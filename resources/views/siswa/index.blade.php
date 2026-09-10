@extends('layouts.app')

@section('title', 'Siswa')
@section('subtitle', 'Data master siswa')

@section('content')

<div class="space-y-6">
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="flex flex-1 flex-col gap-2 sm:flex-row sm:flex-wrap">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari nama, NIS, atau NISN..."
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:w-56 dark:border-gray-600 dark:text-gray-100 dark:placeholder:text-gray-500"
            >
            <select
                name="tahun_ajaran_id"
                onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
            >
                <option value="semua" @selected(is_null($tahunAjaranId))>Semua Tahun Ajaran</option>
                @foreach ($tahunAjarans as $ta)
                    <option value="{{ $ta->id }}" @selected($tahunAjaranId == $ta->id)>
                        {{ $ta->label }}{{ $ta->status === 'aktif' ? ' (Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
            <select
                name="kelas_id"
                onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
            >
                <option value="">Semua Kelas @if ($tahunAjaranId) (tahun ajaran terpilih) @endif</option>
                @foreach ($kelasOptions as $k)
                    <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
            <select
                name="status"
                onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
            >
                <option value="">Semua Status</option>
                @foreach (['aktif' => 'Aktif', 'alumni' => 'Alumni', 'pindah' => 'Pindah', 'keluar' => 'Keluar'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-900">
                Cari
            </button>
        </form>

        <div class="flex shrink-0 items-center gap-3">
            <a href="{{ route('buku-induk.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                Buku Induk Siswa
            </a>
                            <a href="{{ route('buku-induk.create', array_filter(['tahun_ajaran_id' => $tahunAjaranId])) }}">
                    <x-button>+ Siswa</x-button>
                </a>
                    </div>
    </div>
    <p class="-mt-3 text-xs text-gray-400 dark:text-gray-500">
        Menampilkan siswa {{ $tahunAjaranId ? 'pada tahun ajaran '.optional($tahunAjarans->firstWhere('id', $tahunAjaranId))->nama : 'dari semua tahun ajaran' }}.
        Pendaftaran siswa baru sekaligus pembagian kelasnya kini dilakukan lewat menu <strong>Buku Induk Siswa</strong>
        (disusun per angkatan). Untuk melihat siswa yang sudah lulus/keluar/pindah beserta riwayat lengkap, buka
        menu yang sama.
    </p>

    <x-card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50/60 text-xs uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Kelas</th>
                        <th class="px-5 py-3 font-medium">Jenis Kelamin</th>
                        <th class="px-5 py-3 font-medium">Kontak Ortu</th>
                        <th class="px-5 py-3 font-medium">Status</th>
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
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ $s->no_hp_ortu ?: '-' }}</td>
                            <td class="px-5 py-3">
                                <x-badge :color="['aktif' => 'green', 'alumni' => 'indigo', 'pindah' => 'amber', 'keluar' => 'red'][$s->status]">
                                    {{ ucfirst($s->status) }}
                                </x-badge>
                            </td>
                                                            <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('siswa.edit', $s) }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">Ubah</a>
                                        @if ($s->status === 'aktif')
                                            <form method="POST" action="{{ route('siswa.destroy', $s) }}" onsubmit="return confirm('Hapus data siswa {{ $s->nama }}? Riwayat kelas dan mutasi terkait akan ikut terhapus.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-400">Hapus</button>
                                            </form>
                                        @else
                                            <span class="text-xs font-medium text-gray-300 dark:text-gray-600" title="Arsip Buku Induk, tidak dapat dihapus">Terarsip</span>
                                        @endif
                                    </div>
                                </td>
                                                    </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                Tidak ada data yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    {{ $siswa->links() }}
</div>
@endsection