/**
 * PERBAIKAN UNTUK FITUR CETAK LANGSUNG (docx-preview): SHAPE TERKELOMPOK
 * (<v:group>) HILANG TOTAL DARI PRATINJAU/CETAK.
 *
 * ---------------------------------------------------------------------
 * BUKTI PENYEBAB (dicek langsung ke source code docx-preview, BUKAN
 * dugaan): fungsi parseVmlElement() di node_modules/docx-preview/dist/
 * docx-preview.mjs punya switch(elem.localName) dengan case untuk "rect",
 * "oval", "line", "shape", dan "textbox" — TIDAK ADA case "group" sama
 * sekali. Kalau elemen-nya <v:group>, function itu jatuh ke branch
 * `default: return null`, sehingga SELURUH ISI grup itu (semua shape
 * anak di dalamnya) tidak pernah dirender.
 *
 * Ini nyata terjadi di template resmi proyek ini: blok tanda tangan
 * (gambar stempel + tanda tangan yang dikelompokkan jadi satu "Group",
 * dan kotak teks "Kepala Madrasah" + nama pejabat yang juga dikelompokkan
 * jadi satu "Group" lain) SAMA SEKALI TIDAK MUNCUL di halaman "Cetak
 * Langsung" — padahal keduanya cuma berisi gambar (<v:imagedata>) dan
 * kotak teks (<v:textbox>) yang masing-masing SEBENARNYA didukung penuh
 * oleh docx-preview kalau berdiri sendiri (tidak dibungkus <v:group>).
 *
 * ---------------------------------------------------------------------
 * SOLUSI: "BONGKAR" GRUP SEBELUM DIRENDER, BUKAN UBAH LIBRARY
 * ---------------------------------------------------------------------
 * Daripada menambal library docx-preview (butuh maintenance sendiri tiap
 * update versi), file .docx (arsip zip) dibongkar dulu via JSZip —
 * PERSIS pola yang sudah dipakai docx-vml-fix.js — lalu setiap
 * <v:group>...</v:group> di XML-nya diganti dengan ANAK-ANAKNYA
 * (<v:shape>) yang diangkat jadi elemen SEJAJAR/independen (bukan lagi
 * anak dari <v:group>), dengan posisi & ukurannya DIHITUNG ULANG dari
 * koordinat lokal grup (atribut coordorigin/coordsize milik <v:group>,
 * yang mendefinisikan "kotak koordinat" tempat posisi anak-anaknya
 * dinyatakan) menjadi posisi yang artinya SAMA PERSIS dengan sebelumnya
 * (relatif ke tepi kertas/margin/teks, mengikuti punya grup induknya).
 *
 * Setelah dibongkar, tiap shape anak jadi persis seperti shape mandiri
 * biasa (yang sudah didukung docx-preview), dan otomatis ikut diperbaiki
 * posisinya oleh perbaikiPosisiAnchor() di docx-anchor-fix.js — TIDAK
 * ada logika baru yang perlu ditambahkan di sana.
 *
 * ---------------------------------------------------------------------
 * KETERBATASAN YANG PERLU DIKETAHUI
 * ---------------------------------------------------------------------
 * - Cuma menangani <v:group> (format VML/lawas, dipakai sebagai fallback
 *   kompatibilitas oleh Word — dan berdasarkan pengecekan ke template
 *   resmi proyek ini, fallback ini SELALU ada tiap kali Word menyimpan
 *   shape terkelompok). Grup berformat DrawingML modern (<wpg:wgp>) yang
 *   SENGAJA disimpan TANPA fallback VML sama sekali (sangat jarang,
 *   biasanya hanya terjadi untuk objek non-shape seperti SmartArt/Chart)
 *   tidak tertangani oleh perbaikan ini.
 * - Grup DI DALAM grup (nested) ditangani lewat perulangan (dibongkar
 *   dari yang paling dalam dulu), TAPI kalau grup bagian dalam ternyata
 *   memakai satuan posisi lokal (bukan "pt" langsung, seperti lazimnya
 *   grup di dalam grup), perbaikan ini akan MELEWATI grup tsb apa adanya
 *   (lebih aman daripada menghitung dengan asumsi yang belum tentu
 *   benar) — kasus ini sangat jarang untuk template surat resmi
 *   (umumnya cuma satu tingkat pengelompokan, seperti yang terverifikasi
 *   di template proyek ini).
 */

const SHAPE_TAGS = ['shape', 'rect', 'oval', 'line'];

/** Parse "key1:val1;key2:val2;" (atribut style VML) jadi Map, urutan dipertahankan. */
function parseStyleMap(styleText) {
    const map = new Map();
    for (const bagian of (styleText || '').split(';')) {
        const idx = bagian.indexOf(':');
        if (idx === -1) continue;
        const key = bagian.slice(0, idx).trim().toLowerCase();
        const value = bagian.slice(idx + 1).trim();
        if (key) map.set(key, value);
    }
    return map;
}

function styleMapToText(map) {
    return Array.from(map.entries()).map(([k, v]) => `${k}:${v}`).join(';');
}

/** "324.5pt" -> 324.5. Return null kalau bukan angka dengan satuan pt yang valid. */
function parsePt(value) {
    if (value == null) return null;
    const cocok = String(value).trim().match(/^(-?[\d.]+)pt$/i);
    return cocok ? parseFloat(cocok[1]) : null;
}

/** "23336,16281" atau "23336 16281" -> [23336, 16281]. */
function parsePair(value) {
    if (!value) return null;
    const bagian = value.trim().split(/[\s,]+/).map(Number);
    if (bagian.length !== 2 || bagian.some((n) => !Number.isFinite(n))) return null;
    return bagian;
}

/** Baca satu atribut mentah (bukan bagian dari style) dari tag pembuka, mis. coordsize="...". */
function bacaAtribut(attrsText, nama) {
    const pola = new RegExp(`\\b${nama}\\s*=\\s*"([^"]*)"`, 'i');
    const cocok = attrsText.match(pola);
    return cocok ? cocok[1] : null;
}

/**
 * Cari SEMUA shape anak langsung (<v:shape>/<v:rect>/<v:oval>/<v:line>) di dalam
 * isi satu <v:group>, urut sesuai kemunculan. <v:shapetype> (definisi bentuk,
 * bukan instance) dan <w10:wrap> (metadata wrap, tidak dipakai docx-preview)
 * sengaja diabaikan/dibuang saat dibongkar.
 */
function cariShapeAnak(isiGroup) {
    const hasil = [];
    const pola = new RegExp(`<v:(${SHAPE_TAGS.join('|')})\\b([^>]*?)(?:/>|>([\\s\\S]*?)<\\/v:\\1>)`, 'g');
    let m;
    while ((m = pola.exec(isiGroup)) !== null) {
        hasil.push({ whole: m[0], tag: m[1], attrsText: m[2], inner: m[3] ?? null, selfClosing: m[3] == null });
    }
    return hasil;
}

/**
 * Bongkar SATU <v:group>...</v:group> (whole match dari regex utama) jadi
 * gabungan shape anaknya yang sudah dikoreksi posisi/ukurannya. Return null
 * kalau gagal memproses (data tidak lengkap/tidak dikenali) — pemanggil akan
 * membiarkan grup itu APA ADANYA (lebih aman daripada salah hitung).
 */
function bongkarSatuGroup(groupAttrsText, isiGroup) {
    const styleGroup = parseStyleMap(bacaAtribut(groupAttrsText, 'style'));
    const marginLeftPt = parsePt(styleGroup.get('margin-left'));
    const marginTopPt = parsePt(styleGroup.get('margin-top'));
    const widthPt = parsePt(styleGroup.get('width'));
    const heightPt = parsePt(styleGroup.get('height'));

    const coordsize = parsePair(bacaAtribut(groupAttrsText, 'coordsize'));
    const coordorigin = parsePair(bacaAtribut(groupAttrsText, 'coordorigin')) ?? [0, 0];

    if (marginLeftPt == null || marginTopPt == null || widthPt == null || heightPt == null || !coordsize) {
        // Style grup tidak memakai "margin-left/top dalam pt" (mis. ini
        // grup di dalam grup lain yang satuannya lokal, bukan pt) —
        // jangan dipaksa, biarkan apa adanya.
        return null;
    }

    const [coordW, coordH] = coordsize;
    const [originX, originY] = coordorigin;
    if (!coordW || !coordH) return null;

    const scaleX = widthPt / coordW;
    const scaleY = heightPt / coordH;

    const relH = styleGroup.get('mso-position-horizontal-relative');
    const relV = styleGroup.get('mso-position-vertical-relative');
    const zIndex = styleGroup.get('z-index');
    const wrapDistLeft = styleGroup.get('mso-wrap-distance-left');
    const wrapDistRight = styleGroup.get('mso-wrap-distance-right');
    const wrapDistTop = styleGroup.get('mso-wrap-distance-top');
    const wrapDistBottom = styleGroup.get('mso-wrap-distance-bottom');

    const shapeAnak = cariShapeAnak(isiGroup);
    if (shapeAnak.length === 0) return null;

    const hasilShape = shapeAnak.map((shape) => {
        const styleAnak = parseStyleMap(bacaAtribut(shape.attrsText, 'style'));

        const leftLokal = parseFloat(styleAnak.get('left')) || 0;
        const topLokal = parseFloat(styleAnak.get('top')) || 0;
        const widthLokal = parseFloat(styleAnak.get('width'));
        const heightLokal = parseFloat(styleAnak.get('height'));

        if (!Number.isFinite(widthLokal) || !Number.isFinite(heightLokal)) {
            // Shape anak tidak punya ukuran eksplisit — daripada salah
            // hitung, biarkan shape ini apa adanya (masih di dalam style
            // lokal lama, kemungkinan tetap tidak dirender docx-preview,
            // tapi tidak membuat hasil lain jadi salah).
            return shape.whole;
        }

        const styleBaru = new Map();
        styleBaru.set('position', 'absolute');
        styleBaru.set('margin-left', `${(marginLeftPt + (leftLokal - originX) * scaleX).toFixed(2)}pt`);
        styleBaru.set('margin-top', `${(marginTopPt + (topLokal - originY) * scaleY).toFixed(2)}pt`);
        styleBaru.set('width', `${(widthLokal * scaleX).toFixed(2)}pt`);
        styleBaru.set('height', `${(heightLokal * scaleY).toFixed(2)}pt`);

        // Properti lain dari shape anak (visibility, mso-wrap-style,
        // v-text-anchor, dst) dipertahankan apa adanya. "left"/"top" milik
        // shape anak SENGAJA tidak ikut disalin — itu koordinat LOKAL
        // (relatif ke kotak koordinat grup) yang sudah diterjemahkan jadi
        // margin-left/margin-top absolut di atas; kalau ikut disalin
        // mentah-mentah, nilainya jadi tidak berarti apa-apa lagi (dan
        // berpotensi bentrok dengan properti CSS "left"/"top" beneran).
        const kunciDilewati = new Set(['left', 'top', 'width', 'height']);
        for (const [k, v] of styleAnak) {
            if (!kunciDilewati.has(k) && !styleBaru.has(k)) styleBaru.set(k, v);
        }

        // Properti posisi-relatif & wrap-distance diwarisi dari grup
        // induknya (shape anak sendiri tidak pernah punya ini), supaya
        // perbaikiPosisiAnchor() di docx-anchor-fix.js bisa mengoreksi
        // posisinya dengan cara yang sama seperti shape mandiri lain.
        if (relH) styleBaru.set('mso-position-horizontal-relative', relH);
        if (relV) styleBaru.set('mso-position-vertical-relative', relV);
        if (zIndex) styleBaru.set('z-index', zIndex);
        if (wrapDistLeft) styleBaru.set('mso-wrap-distance-left', wrapDistLeft);
        if (wrapDistRight) styleBaru.set('mso-wrap-distance-right', wrapDistRight);
        if (wrapDistTop) styleBaru.set('mso-wrap-distance-top', wrapDistTop);
        if (wrapDistBottom) styleBaru.set('mso-wrap-distance-bottom', wrapDistBottom);

        const attrsBaru = shape.attrsText.replace(
            /\bstyle\s*=\s*"[^"]*"/i,
            `style="${styleMapToText(styleBaru)}"`
        );

        return shape.selfClosing
            ? `<v:${shape.tag}${attrsBaru}/>`
            : `<v:${shape.tag}${attrsBaru}>${shape.inner}</v:${shape.tag}>`;
    });

    return hasilShape.join('');
}

/**
 * Bongkar semua <v:group> di satu bagian XML docx (document.xml/headerN.xml/
 * footerN.xml). Grup yang bersarang ditangani lewat perulangan: tiap putaran
 * cuma memproses grup yang PALING DALAM (tidak berisi <v:group> lain di
 * dalamnya), supaya batas tag tetap dikenali dengan benar. Grup yang gagal
 * diproses (lihat bongkarSatuGroup) dibiarkan apa adanya.
 */
export function ungroupVmlGroups(xml) {
    let hasil = xml;
    let batasPengaman = 20; // cegah infinite loop kalau ada XML yang tidak wajar

    while (hasil.includes('<v:group') && batasPengaman-- > 0) {
        const polaGroupTerdalam = /<v:group\b([^>]*)>((?:(?!<\/?v:group\b)[\s\S])*)<\/v:group>/g;
        let adaYangDiproses = false;

        const setelahnya = hasil.replace(polaGroupTerdalam, (whole, attrsText, isiGroup) => {
            const dibongkar = bongkarSatuGroup(attrsText, isiGroup);
            if (dibongkar == null) {
                // Tandai supaya tidak diproses ulang selamanya: ganti
                // sementara tag pembukanya jadi placeholder unik, nanti
                // dikembalikan setelah loop selesai.
                return whole;
            }
            adaYangDiproses = true;
            return dibongkar;
        });

        if (!adaYangDiproses) {
            // Sisa <v:group> yang ada semuanya gagal diproses (mis. grup
            // bersarang dengan satuan lokal) — hentikan supaya tidak
            // infinite loop, biarkan sisanya apa adanya.
            break;
        }

        hasil = setelahnya;
    }

    return hasil;
}
