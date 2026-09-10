<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak {{ $surat->nomor_surat }} &middot; {{ config('app.name', 'SI-TU Sekolah') }}</title>
    @vite(['resources/js/print-surat.js'])
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #525659; font-family: system-ui, -apple-system, sans-serif; height: 100%; }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 16px;
            background: #0f172a;
            color: #fff;
        }
        .toolbar a { color: #cbd5e1; text-decoration: none; font-size: 14px; }
        .toolbar a:hover { color: #fff; text-decoration: underline; }
        .toolbar .actions { display: flex; gap: 8px; }
        .toolbar button, .toolbar .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 0;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-primary { background: #4f46e5; color: #fff; }
        .btn-primary:hover { background: #4338ca; }
        .btn-primary:disabled { background: #6366f1; cursor: wait; opacity: .7; }
        .btn-secondary { background: #1e293b; color: #e2e8f0; }
        .btn-secondary:hover { background: #334155; }

        .preview-wrapper { min-height: calc(100vh - 49px); }
        .loading-msg { color: #e2e8f0; text-align: center; padding: 64px 16px; font-size: 14px; }
        .error-msg { color: #fecaca; text-align: center; padding: 64px 16px; font-size: 14px; }

        /* docx-preview otomatis membuat elemen ".docx-wrapper" (bingkai abu-abu
           di layar) berisi satu <section class="docx"> per halaman docx-nya,
           dengan ukuran & margin (sebagai padding) PERSIS sesuai pengaturan
           halaman di file Word aslinya. Ukuran kertas cetaknya (rule @page)
           di-set lewat JS setelah render, mengikuti ukuran ASLI dari surat
           itu (F4/Legal/A4/dst — apa pun ukurannya di file Word-nya),
           bukan ukuran default printer/OS.  */

        @media print {
            .toolbar { display: none !important; }
            .loading-msg, .error-msg { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <a href="{{ route('surat.show', $surat) }}">&larr; Kembali ke detail surat</a>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route('surat.cetak', $surat) }}">Unduh Word (.docx)</a>
            <button id="btn-cetak" class="btn-primary" type="button" onclick="cetakSekarang()" disabled>
                Memuat...
            </button>
        </div>
    </div>

        {{-- docx surat dirender langsung di browser lewat docx-preview
             (client-side, tanpa proses konversi di server / tanpa
             LibreOffice / tanpa Microsoft Word). Lihat
             resources/js/docx-vml-fix.js & resources/js/docx-anchor-fix.js
             untuk perbaikan-perbaikan yang sudah diterapkan pada jalur
             ini (shape garis vektor & posisi gambar mengambang). --}}
        <div class="preview-wrapper">
            <p id="loading-msg" class="loading-msg">Memuat pratinjau surat...</p>
            <div id="docx-container"></div>
        </div>

        <script>
            // CATATAN SOAL PRINT.JS: sempat dicoba pakai Print.js
            // (`type: 'html'`) untuk tombol Cetak, TAPI ternyata mode itu di
            // Print.js secara internal MEMAKSA setiap elemen dapat
            // "max-width: 800px !important" dan "font-size: 12pt !important"
            // sendiri-sendiri — ini bikin layout presisi hasil docx-preview
            // (ukuran halaman, lebar tabel, dst) jadi berantakan saat dicetak,
            // walau tampilan di layar sudah benar. Ini bukan bug di
            // renderernya, tapi memang cara kerja bawaan Print.js yang tidak
            // cocok untuk dokumen dengan layout presisi seperti ini.
            //
            // Karena docx-preview sendiri sudah menyiapkan CSS khusus print
            // (otomatis menyembunyikan bingkai abu-abu & bayangan halaman saat
            // dicetak), tombol Cetak di sini memakai dialog Print BAWAAN
            // browser (window.print()) supaya yang tercetak PERSIS sama dengan
            // yang tampil di pratinjau — tanpa proses server, tanpa
            // LibreOffice/Word, dan tanpa risiko layout berubah seperti di atas.
            var docxUrl = @json($docxUrl);
            var siap = false;
            var autoPrinted = false;

            function cetakSekarang() {
                if (!siap) return;
                window.print();
            }

            // Set ukuran kertas cetak (rule @page) supaya PERSIS sama dengan
            // ukuran halaman asli di file Word-nya (bukan default printer),
            // dibaca dari elemen halaman pertama hasil render docx-preview
            // (docx-preview sudah memberi width/min-height dalam satuan "pt"
            // yang persis sesuai pengaturan halaman di dokumen aslinya).
            function terapkanUkuranKertas() {
                var halamanPertama = document.querySelector('#docx-container .docx-wrapper > section.docx');
                if (!halamanPertama) return;

                var lebar = halamanPertama.style.width;
                var tinggi = halamanPertama.style.minHeight || halamanPertama.style.height;
                if (!lebar || !tinggi) return;

                var style = document.createElement('style');
                style.textContent = '@page { size: ' + lebar + ' ' + tinggi + '; margin: 0; }';
                document.head.appendChild(style);
            }

            fetch(docxUrl)
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Gagal mengambil file surat (HTTP ' + response.status + ').');
                    }
                    return response.arrayBuffer();
                })
                .then(function (buffer) {
                    var container = document.getElementById('docx-container');
                    return window.renderDocxPreview(buffer, container, container, {
                        inWrapper: true,
                        breakPages: true,
                        ignoreWidth: false,
                        ignoreHeight: false,
                        useBase64URL: true,
                    });
                })
                .then(function () {
                    siap = true;
                    terapkanUkuranKertas();
                    document.getElementById('loading-msg').remove();

                    var tombol = document.getElementById('btn-cetak');
                    tombol.disabled = false;
                    tombol.textContent = 'Cetak / Simpan PDF';

                    if (!autoPrinted) {
                        autoPrinted = true;
                        setTimeout(cetakSekarang, 300);
                    }
                })
                .catch(function (err) {
                    var pesan = document.getElementById('loading-msg');
                    pesan.className = 'error-msg';
                    pesan.textContent = 'Gagal memuat pratinjau surat: ' + err.message;
                });
        </script>
</body>
</html>
