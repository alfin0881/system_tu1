
{{-- KOMPONEN TOMBOL EXPORT IMPORT UNTUK VIEW --}}
{{-- Pindahkan potongan kode ini ke file View (Blade) Anda sesuai dengan halamannya --}}

{{-- Tampilan Alert Notifikasi --}}
@if(session('success'))
    <div class="mb-4 flex items-center rounded-lg border border-green-300 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-gray-800 dark:text-green-400" role="alert">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 flex items-center rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-gray-800 dark:text-red-400" role="alert">
        {{ session('error') }}
    </div>
@endif


{{-- ================= 1. VIEW DATA INDUK ================= --}}
<div class="mb-4 rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="border-b border-gray-200 px-5 py-3 text-sm font-semibold text-gray-900 dark:border-gray-700 dark:text-white">Export & Import Data Induk</div>
    <div class="flex flex-wrap items-center gap-2 p-5">
        <a href="{{ route('data.induk.export') }}" class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2.5 text-center text-sm font-medium text-white hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">📥 Export CSV</a>
        <form action="{{ route('data.induk.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
            @csrf
            <input type="file" name="file" accept=".csv" required class="block rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-gray-700 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:file:bg-gray-600 dark:file:text-gray-200">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">📤 Import CSV</button>
        </form>
    </div>
</div>

{{-- ================= 2. VIEW GURU KARYAWAN ================= --}}
<div class="mb-4 rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="border-b border-gray-200 px-5 py-3 text-sm font-semibold text-gray-900 dark:border-gray-700 dark:text-white">Export & Import Guru Karyawan</div>
    <div class="flex flex-wrap items-center gap-2 p-5">
        <a href="{{ route('data.guru-karyawan.export') }}" class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2.5 text-center text-sm font-medium text-white hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">📥 Export CSV</a>
        <form action="{{ route('data.guru-karyawan.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
            @csrf
            <input type="file" name="file" accept=".csv" required class="block rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-900 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-gray-700 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:file:bg-gray-600 dark:file:text-gray-200">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">📤 Import CSV</button>
        </form>
    </div>
</div>

{{-- ================= 3. VIEW KELAS (EXPORT SAJA) ================= --}}
<div class="mb-4 rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="border-b border-gray-200 px-5 py-3 text-sm font-semibold text-gray-900 dark:border-gray-700 dark:text-white">Export Data Kelas</div>
    <div class="p-5">
        <a href="{{ route('data.kelas.export') }}" class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2.5 text-center text-sm font-medium text-white hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">📥 Export CSV Kelas Saja</a>
    </div>
</div>

{{-- ================= 4. VIEW SISWA (EXPORT SAJA) ================= --}}
<div class="mb-4 rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="border-b border-gray-200 px-5 py-3 text-sm font-semibold text-gray-900 dark:border-gray-700 dark:text-white">Export Data Siswa</div>
    <div class="p-5">
        <a href="{{ route('data.siswa.export') }}" class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2.5 text-center text-sm font-medium text-white hover:bg-green-800 focus:outline-none focus:ring-4 focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">📥 Export CSV Siswa Saja</a>
    </div>
</div>
