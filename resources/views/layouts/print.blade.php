<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cetak Surat') &middot; {{ config('app.name', 'SI-TU Sekolah') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page {
            size: 215mm 330mm; /* F4 / Folio */
            margin: 20mm 20mm 20mm 30mm;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .surat-page {
                box-shadow: none !important;
                margin: 0 !important;
                width: auto !important;
                min-height: 0 !important;
                padding: 0 !important;
            }
        }
        @media screen {
            body { background: #e2e8f0; }
            .surat-page {
                width: 215mm;
                min-height: 330mm;
                margin: 24px auto 48px;
                background: #fff;
                box-shadow: 0 1px 4px rgba(15, 23, 42, .18);
                padding: 20mm 20mm 20mm 30mm;
            }
        }
        .break-before-page { break-before: page; }
    </style>
    @stack('styles')
</head>
<body class="text-slate-900">
    <div class="no-print sticky top-0 z-10 flex items-center justify-between bg-slate-900 px-6 py-3 text-white">
        <a href="{{ url()->previous() }}" class="text-sm font-medium hover:underline">&larr; Kembali</a>
        <button onclick="window.print()" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold hover:bg-indigo-500">
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="surat-page relative overflow-hidden font-serif text-[12pt] leading-relaxed">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
