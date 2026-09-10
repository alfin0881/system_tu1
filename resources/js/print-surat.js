import { renderAsync } from 'docx-preview';
import { fixDocxVmlShapes, bacaXmlBagianDocx } from './docx-vml-fix.js';
import { perbaikiPosisiAnchor } from './docx-anchor-fix.js';

// Dipakai oleh resources/views/surat/print.blade.php.
//
// renderAsync (docx-preview): me-render file .docx langsung di browser
// dengan tampilan HAMPIR IDENTIK dengan aslinya di Word (ukuran halaman,
// margin, kop surat, tabel, gambar, dst tetap sama persis) — jauh lebih
// presisi dibanding convert docx->HTML di server (yang kehilangan banyak
// detail formatting), dan tidak butuh LibreOffice/Microsoft Word sama
// sekali karena murni parsing docx di JavaScript. Ini penting terutama
// kalau server produksinya nanti hosting/cPanel biasa yang TIDAK bisa
// pasang LibreOffice/Microsoft Word — jalur ini memang SATU-SATUNYA cara
// render/cetak yang dipakai di aplikasi ini (sengaja tidak ada jalur
// konversi PDF di server sama sekali).
//
// Print.js TIDAK dipakai lagi di halaman ini: mode HTML-nya memaksa setiap
// elemen dapat "max-width" & "font-size" sendiri (lihat catatan di
// print.blade.php), yang merusak layout presisi hasil docx-preview. Tombol
// Cetak di halaman itu sekarang memakai window.print() bawaan browser.
//
// SEBELUM di-render, buffer docx-nya dilewatkan dulu ke fixDocxVmlShapes()
// (lihat docx-vml-fix.js) yang melakukan DUA perbaikan berurutan:
// 1) ungroupVmlGroups() (docx-group-fix.js) — membongkar shape yang
//    DIKELOMPOKKAN (<v:group>, mis. blok stempel+ttd atau kotak nama+
//    jabatan pejabat) karena docx-preview TIDAK PERNAH menangani <v:group>
//    sama sekali (isinya hilang total kalau dibiarkan, walau shape di
//    dalamnya masing-masing sebenarnya didukung).
// 2) fixUnsupportedVmlShapes() — supaya garis/bar dekoratif di kop surat
//    (dibuat di Word sebagai shape vektor <v:shape> berisi path+fillcolor)
//    tetap tampil, karena docx-preview juga tidak bisa merender shape
//    jenis itu sama sekali.
// Keduanya murni perbaikan representasi XML di memori sebelum dirender —
// file .docx asli di server tidak ikut berubah.
//
// SETELAH render selesai, perbaikiPosisiAnchor() (lihat docx-anchor-fix.js)
// dipanggil untuk mengoreksi posisi elemen kop surat yang "mengambang"
// (logo, garis pemisah, dst yang diatur relatif ke halaman/margin, bukan
// mengikuti alur teks) — tanpa ini, elemen semacam itu bisa tampil
// bergeser dari posisi aslinya di Word (kadang sampai menabrak teks di
// sebelahnya).
async function renderDocxPreviewDenganPerbaikan(buffer, bodyContainer, styleContainer, options) {
    const [bufferSudahDiperbaiki, xmlBagian] = await Promise.all([
        fixDocxVmlShapes(buffer),
        bacaXmlBagianDocx(buffer),
    ]);

    const hasil = await renderAsync(bufferSudahDiperbaiki, bodyContainer, styleContainer, options);

    perbaikiPosisiAnchor(bodyContainer, (namaBagian) => xmlBagian[namaBagian] ?? null);

    return hasil;
}

window.renderDocxPreview = renderDocxPreviewDenganPerbaikan;
