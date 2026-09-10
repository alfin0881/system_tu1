<header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-gray-200 bg-white/80 px-4 backdrop-blur sm:px-6 lg:px-8 dark:border-gray-700 dark:bg-gray-800/80">
    <button
        @click="sidebarOpen = true"
        type="button"
        class="-ml-1 rounded-lg p-2 text-gray-500 hover:bg-gray-100 lg:hidden dark:text-gray-400 dark:hover:bg-gray-700"
    >
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="min-w-0 flex-1">
        <h1 class="truncate text-base font-semibold text-gray-900 dark:text-white">@yield('title', 'Dashboard')</h1>
        @hasSection('subtitle')
            <p class="truncate text-xs text-gray-500 dark:text-gray-400">@yield('subtitle')</p>
        @endif
    </div>

    {{-- Tombol ganti tema (terang/gelap). Preferensi disimpan di
         localStorage lewat "darkMode" pada x-data induk (lihat app.blade.php)
         supaya tetap konsisten saat pindah halaman. --}}
    <button
        @click="darkMode = !darkMode"
        type="button"
        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700"
        :title="darkMode ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'"
    >
        <svg x-show="!darkMode" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8Z" />
        </svg>
        <svg x-show="darkMode" style="display:none" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 3v2M12 19v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M3 12h2M19 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4" />
            <circle cx="12" cy="12" r="4.5" />
        </svg>
    </button>

    <div class="flex items-center gap-3" x-data="{ open: false }">
        <button @click="open = !open" @click.outside="open = false" type="button" class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-700">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="hidden text-left sm:block">
                <p class="text-sm font-medium leading-tight text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                <p class="text-xs capitalize leading-tight text-gray-400 dark:text-gray-500">{{ auth()->user()->role }}</p>
            </div>
            <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <path d="m6 9 6 6 6-6" />
            </svg>
        </button>

        <div
            x-show="open"
            x-transition
            style="display: none;"
            class="absolute right-4 top-14 z-30 w-48 rounded-lg border border-gray-200 bg-white py-1 shadow-lg sm:right-6 lg:right-8 dark:border-gray-700 dark:bg-gray-700"
        >
            <div class="border-b border-gray-100 px-3 py-2 dark:border-gray-600">
                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-600">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</header>
