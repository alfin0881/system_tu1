import JSZip from 'jszip';
import { ungroupVmlGroups } from './docx-group-fix.js';

/**
 * PERBAIKAN UNTUK FITUR CETAK LANGSUNG (docx-preview).
 *
 * Kop surat pada template resmi (mis. SURAT_TUGAS_-_Web.docx) memakai garis
 * pemisah di bawah kop yang di Word disimpan sebagai SHAPE VEKTOR
 * (<v:shape> dengan atribut path + fillcolor, dibungkus <w:pict>), BUKAN
 * sebagai gambar (picture) atau border paragraf biasa. Ini cara Word
 * menyimpan objek "Shapes > Garis/Line" modern (drawing canvas), lengkap
 * dengan fallback VML lama untuk kompatibilitas versi Word lawas.
 *
 * Library docx-preview yang dipakai di sini (resources/js/print-surat.js)
 * TERBUKTI tidak bisa merender shape seperti itu: elemen <v:shape> hanya
 * dipetakan jadi elemen <g> KOSONG (lihat fungsi parseVmlElement di
 * node_modules/docx-preview/dist/docx-preview.mjs), dan atribut geometri
 * path" pada shape tsb sama sekali tidak pernah dibaca. Akibatnya garis
 * pemisah di bawah kop surat HILANG total saat ditampilkan/dicetak lewat
 * halaman "Cetak Langsung" (resources/views/surat/print.blade.php), padahal
 * di file .docx aslinya (dan hasil unduhan .docx) tampilannya sudah benar.
 *
 * Elemen VML lain yang MEMANG didukung docx-preview: <v:rect>, <v:oval>,
 * <v:line>, dan <v:shape> yang isinya gambar (<v:imagedata>) atau kotak
 * teks (<v:textbox>) — ketiganya tetap dibiarkan apa adanya oleh fungsi
 * di bawah ini.
 *
 * SOLUSI: sebelum buffer .docx diserahkan ke docx-preview, file docx
 * (yang sebenarnya adalah arsip zip) dibongkar lewat JSZip, lalu setiap
 * <v:shape> "polos" (punya atribut path, TANPA <v:imagedata>/<v:textbox>
 * di dalamnya — ciri khas shape garis/bar dekoratif seperti ini) diganti
 * elemen-nya menjadi <v:rect> dengan atribut style & fillcolor yang SAMA
 * PERSIS. <v:rect> ini didukung penuh oleh docx-preview (langsung
 * dipetakan ke elemen <rect> SVG dengan posisi & ukuran mengikuti "style"
 * yang sudah benar), sehingga hasilnya berupa balok/garis solid di posisi
 * & warna yang sama seperti aslinya di Word — jauh lebih presisi
 * dibanding sebelumnya (hilang sama sekali).
 *
 * Fungsi ini murni memperbaiki representasi XML di dalam docx SEBELUM
 * dirender, dan TIDAK mengubah file .docx yang diunduh/tersimpan di
 * server (file asli hasil generate PhpWord tetap utuh) — hanya salinan
 * di memori browser yang dipakai khusus untuk pratinjau & cetak.
 *
 * SEBELUM shape garis dekoratif di atas diperbaiki, tiap bagian XML juga
 * dilewatkan dulu ke ungroupVmlGroups() (lihat docx-group-fix.js) untuk
 * membongkar shape yang DIKELOMPOKKAN (<v:group>) — mis. blok tanda
 * tangan (stempel+ttd) atau kotak nama+jabatan pejabat yang sengaja
 * dikelompokkan jadi satu objek di Word — karena docx-preview TIDAK
 * PERNAH menangani <v:group> sama sekali (isinya hilang total kalau
 * dibiarkan). Lihat docx-group-fix.js untuk detail & buktinya.
 */

const VML_XML_FILES = [
    'word/document.xml',
    'word/footer1.xml',
    'word/footer2.xml',
    'word/footer3.xml',
    'word/header1.xml',
    'word/header2.xml',
    'word/header3.xml',
];

/**
 * Ganti setiap <v:shape ...>...</v:shape> yang punya atribut "path" (shape
 * bergeometri custom, biasanya garis/bar dekoratif) dan TIDAK berisi
 * <v:imagedata>/<v:textbox> di dalamnya, menjadi <v:rect ...>...</v:rect>
 * dengan atribut yang sama. Shape berisi gambar atau kotak teks (kop
 * logo, stempel, nama pejabat, dst) sengaja tidak disentuh sama sekali.
 *
 * Shape yang SENGAJA dibuat transparan/tanpa isi (filled="f", tanpa
 * fillcolor) juga sengaja TIDAK dikonversi — <v:rect> hasil konversi tanpa
 * warna eksplisit akan otomatis terisi HITAM PEKAT oleh browser (default
 * SVG), yang justru bisa menutupi/menabrak teks di sekitarnya kalau shape
 * itu ternyata cuma bingkai/garis bantu yang seharusnya tidak terlihat.
 * Lebih aman shape seperti itu tetap hilang (perilaku lama) daripada
 * malah muncul jadi kotak hitam menimpa isi surat.
 */
export function fixUnsupportedVmlShapes(xml) {
    return xml.replace(
        /<v:shape\b([^>]*)>([\s\S]*?)<\/v:shape>/g,
        (whole, attrs, inner) => {
            const isDecorativeLineShape = /\bpath\s*=\s*"/.test(attrs);
            const hasRealContent = /<v:imagedata\b/.test(inner) || /<v:textbox\b/.test(inner);
            const adaWarnaIsian = /\bfillcolor\s*=\s*"/.test(attrs);
            const sengajaTanpaIsian = /\bfilled\s*=\s*"f"/.test(attrs);

            if (!isDecorativeLineShape || hasRealContent || !adaWarnaIsian || sengajaTanpaIsian) {
                return whole;
            }

            return `<v:rect${attrs}>${inner}</v:rect>`;
        }
    );
}

/**
 * Baca isi mentah beberapa bagian XML dari file .docx (document.xml,
 * headerN.xml, footerN.xml) TANPA mengubah apa pun — dipakai oleh
 * docx-anchor-fix.js untuk membaca info posisi (relativeFrom, ukuran
 * halaman, dst) yang tidak ikut terbawa ke DOM hasil render docx-preview.
 * Bagian yang tidak ada di file (mis. dokumen tidak punya footer3)
 * dilewati begitu saja (tidak ikut masuk ke object hasil).
 */
export async function bacaXmlBagianDocx(arrayBuffer) {
    const hasil = {};

    try {
        const zip = await JSZip.loadAsync(arrayBuffer);

        for (const path of VML_XML_FILES) {
            const file = zip.file(path);
            if (file) {
                hasil[path] = await file.async('string');
            }
        }
    } catch (err) {
        console.warn('Gagal membaca bagian XML docx untuk perbaikan posisi:', err);
    }

    return hasil;
}

/**
 * Terima ArrayBuffer file .docx apa adanya, kembalikan ArrayBuffer baru
 * dengan seluruh shape garis/bar dekoratif yang tidak didukung docx-preview
 * sudah diperbaiki. Jika terjadi kegagalan apa pun saat memproses (mis.
 * file bukan docx yang valid), buffer ASLI dikembalikan apa adanya supaya
 * pratinjau tetap bisa tampil (lebih baik tanpa perbaikan daripada gagal
 * total).
 */
export async function fixDocxVmlShapes(arrayBuffer) {
    try {
        const zip = await JSZip.loadAsync(arrayBuffer);
        let adaPerubahan = false;

        for (const path of VML_XML_FILES) {
            const file = zip.file(path);
            if (!file) {
                continue;
            }

            const xml = await file.async('string');
            const setelahUngroup = ungroupVmlGroups(xml);
            const fixed = fixUnsupportedVmlShapes(setelahUngroup);

            if (fixed !== xml) {
                zip.file(path, fixed);
                adaPerubahan = true;
            }
        }

        if (!adaPerubahan) {
            return arrayBuffer;
        }

        return await zip.generateAsync({
            type: 'arraybuffer',
            compression: 'DEFLATE',
            compressionOptions: { level: 6 },
        });
    } catch (err) {
        console.warn('Gagal memperbaiki shape VML pada docx, memakai file asli:', err);
        return arrayBuffer;
    }
}
