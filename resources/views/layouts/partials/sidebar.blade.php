@php

    // Setiap item: [label, nama-route, icon]. 'route' boleh menunjuk ke rute
    // yang belum didaftarkan (fase berikutnya) — sidebar otomatis menandainya
    // "Segera" via Route::has() sehingga tidak pernah 404 saat diklik.
    $navGroups = [
        'Utama' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'pattern' => 'dashboard', 'icon' => 'home'],
        ],
        'Siswa & Kelas' => [
            ['label' => 'Pencarian Siswa', 'route' => 'siswa.index', 'pattern' => 'siswa.*', 'icon' => 'users'],
            // Data Kelas Siswa: satu halaman berisi Rekap + tiap kelas
            // (7-A, 7-B, dst) sebagai tab — tidak dipecah jadi banyak menu.
            ['label' => 'Data Kelas Siswa', 'route' => 'kelas.index', 'pattern' => 'kelas.*', 'icon' => 'squares'],
            ['label' => 'Buku Induk Siswa', 'route' => 'buku-induk.index', 'pattern' => 'buku-induk.*', 'icon' => 'book'],
        ],
        'Data Master' => [
            ['label' => 'Tahun Ajaran', 'route' => 'tahun-ajaran.index', 'pattern' => 'tahun-ajaran.*', 'icon' => 'calendar'],
            ['label' => 'Guru & Karyawan', 'route' => 'guru.index', 'pattern' => 'guru.*', 'icon' => 'id-card'],
        ],
        'Akademik' => [
            ['label' => 'Kenaikan Kelas & Kelulusan', 'route' => 'kenaikan-kelas.index', 'pattern' => 'kenaikan-kelas.*', 'icon' => 'trend-up'],
            ['label' => 'Mutasi Siswa', 'route' => 'mutasi-siswa.index', 'pattern' => 'mutasi-siswa.*', 'icon' => 'swap'],
        ],
        'Surat Menyurat' => [
            ['label' => 'Jenis Surat', 'route' => 'jenis-surat.index', 'pattern' => 'jenis-surat.*', 'icon' => 'tag'],
            ['label' => 'Buat Surat', 'route' => 'surat.create', 'pattern' => 'surat.create', 'icon' => 'plus-doc'],
            ['label' => 'Daftar Surat', 'route' => 'surat.index', 'pattern' => 'surat.index', 'icon' => 'doc'],
        ],
    ];

    $icons = [
        'home' => 'M3 11.5 12 4l9 7.5M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9',
        'building' => 'M4 21V5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v16M12 21V9a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v12M4 21h16M7 7h1M7 11h1M7 15h1M15 12h1M15 16h1',
        'calendar' => 'M7 3v3M17 3v3M4 8h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z',
        'id-card' => 'M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1ZM8 12a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm-3 5c.4-1.8 1.8-3 3-3s2.6 1.2 3 3M14 9h4M14 13h4',
        'squares' => 'M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z',
        'users' => 'M9 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-6 9c.6-3 3-5 6-5s5.4 2 6 5M17 11a2.7 2.7 0 1 0 0-5.4M17.5 14c2.3.4 4 2.2 4.5 5',
        'book' => 'M4 19.5V6a2 2 0 0 1 2-2h13.5v15H6a2 2 0 0 0-2 2ZM19.5 19H6a2 2 0 0 0-2 2M8 7h8M8 10.5h8',
        'trend-up' => 'm3 16 6-6 4 4 8-8M15 6h6v6',
        'swap' => 'm4 8 4-4 4 4M8 4v12M20 16l-4 4-4-4M16 20V8',
        'tag' => 'm4 12 8-8h6a1 1 0 0 1 1 1v6l-8 8a1 1 0 0 1-1.4 0l-5.6-5.6a1 1 0 0 1 0-1.4ZM15 8h.01',
        'plus-doc' => 'M7 3h7l4 4v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Zm7 0v4h4M12 12v6M9 15h6',
        'doc' => 'M7 3h7l4 4v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Zm7 0v4h4M9 12h6M9 16h4',
    ];
@endphp

<aside
    class="fixed inset-y-0 left-0 z-40 flex flex-col border-r border-gray-200 bg-white transition-all duration-200 ease-in-out lg:trangray-x-0 dark:border-gray-700 dark:bg-gray-800"
    :class="[
        sidebarOpen ? 'trangray-x-0' : '-trangray-x-full',
        sidebarCollapsed ? 'w-64 lg:w-20' : 'w-64'
    ]"
>
    {{-- Tombol ciut/buka sidebar — hanya tampil di layar lg ke atas.
         Di mobile, buka/tutup tetap lewat tombol hamburger di topbar. --}}
    <button
        @click="sidebarCollapsed = !sidebarCollapsed"
        type="button"
        class="absolute -right-3 top-6 z-10 hidden h-6 w-6 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-400 shadow-sm transition hover:text-blue-700 lg:flex dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:text-white"
        :title="sidebarCollapsed ? 'Buka sidebar' : 'Ciutkan sidebar'"
    >
        <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="sidebarCollapsed && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 19l-7-7 7-7" />
        </svg>
    </button>

    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 px-5 dark:border-gray-700" :class="sidebarCollapsed && 'lg:justify-center lg:px-0'">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-700 text-sm font-bold text-white dark:bg-blue-600">
            TU
        </div>
        <div class="min-w-0" :class="sidebarCollapsed ? 'lg:hidden' : ''">
            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ config('app.name', 'SI-TU Sekolah') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Sistem Tata Usaha</p>
        </div>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
        @foreach ($navGroups as $groupLabel => $items)
            <div>
                <p
                    class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500"
                    :class="sidebarCollapsed ? 'lg:hidden' : ''"
                >
                    {{ $groupLabel }}
                </p>
                <div class="space-y-0.5">
                    @foreach ($items as $item)
                        @php $exists = \Illuminate\Support\Facades\Route::has($item['route']); @endphp
                        <a
                            href="{{ $exists ? route($item['route']) : '#' }}"
                            title="{{ $item['label'] }}"
                            @if(! $exists) title="Segera hadir" @endif
                            class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                                {{ $exists && request()->routeIs($item['pattern'])
                                    ? 'bg-blue-50 text-blue-700 dark:bg-gray-700 dark:text-white'
                                    : ($exists ? 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' : 'cursor-not-allowed text-gray-300 dark:text-gray-600') }}"
                            :class="sidebarCollapsed && 'lg:justify-center lg:px-2'"
                        >
                            <svg class="h-4.5 w-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="{{ $icons[$item['icon']] }}" />
                            </svg>
                            <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ $item['label'] }}</span>
                            @unless ($exists)
                                <span class="ml-auto rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-400 dark:bg-gray-700 dark:text-gray-500" :class="sidebarCollapsed ? 'lg:hidden' : ''">Segera</span>
                            @endunless
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="border-t border-gray-200 p-3 dark:border-gray-700">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                title="Keluar"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-red-50 hover:text-red-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-red-400"
                :class="sidebarCollapsed && 'lg:justify-center lg:px-2'"
            >
                <svg class="h-4.5 w-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 17v1a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v1M9 12h12m0 0-3-3m3 3-3 3" />
                </svg>
                <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Keluar</span>
            </button>
        </form>
    </div>
</aside>