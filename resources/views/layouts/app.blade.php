<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') &middot; {{ config('app.name', 'SI-TU Sekolah') }}</title>

    {{-- Cegah "kedip" saat pindah halaman: baca preferensi sidebar ciut
         dan tema gelap dari localStorage lalu tandai <html> SEBELUM body
         dirender. Script ini sengaja TANPA "defer" supaya jalan lebih
         dulu daripada Alpine & sebelum body dicat. --}}
    <script>
        (function () {
            try {
                if (localStorage.getItem('sidebarCollapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }

                var theme = localStorage.getItem('theme');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (theme === 'dark' || (theme === null && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body
    class="min-h-screen bg-gray-50 text-gray-900 antialiased transition-colors dark:bg-gray-900 dark:text-gray-100"
    x-data="{
        sidebarOpen: false,
        sidebarCollapsed: document.documentElement.classList.contains('sidebar-collapsed'),
        darkMode: document.documentElement.classList.contains('dark')
    }"
    x-init="
        $watch('sidebarCollapsed', (value) => {
            document.documentElement.classList.toggle('sidebar-collapsed', value);
            try { localStorage.setItem('sidebarCollapsed', value) } catch (e) {}
        });
        $watch('darkMode', (value) => {
            document.documentElement.classList.toggle('dark', value);
            try { localStorage.setItem('theme', value ? 'dark' : 'light') } catch (e) {}
        })
    "
>

    {{-- Overlay mobile --}}
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
        style="display: none;"
    ></div>

    @include('layouts.partials.sidebar')

    {{-- Padding kiri konten mengikuti lebar sidebar: 64 (terbuka) / 20 (ciut) --}}
    <div
        class="transition-all duration-200 ease-in-out"
        :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'"
    >
        @include('layouts.partials.topbar')

        <main class="p-4 sm:p-6 lg:p-8">
            @if (session('success'))
                <div class="mb-6 flex items-center rounded-lg border border-green-300 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-gray-800 dark:text-green-400" role="alert">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    <span class="ml-3">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 flex items-center rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-gray-800 dark:text-red-400" role="alert">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M12 8v5M12 16h.01" />
                    </svg>
                    <span class="ml-3">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
