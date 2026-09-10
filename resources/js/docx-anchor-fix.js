/**
 * PERBAIKAN POSISI ELEMEN "MENGAMBANG" (ANCHORED) PADA FITUR CETAK LANGSUNG.
 *
 * Dipakai bersama docx-vml-fix.js oleh resources/js/print-surat.js.
 *
 * ---------------------------------------------------------------------
 * KENAPA POSISI LOGO/GARIS DI KOP SURAT BISA BERGESER SAAT DICETAK LANGSUNG
 * ---------------------------------------------------------------------
 * Di Word, elemen kop surat (logo sekolah, garis pemisah, stempel/ttd dekat
 * tanda tangan, dst) sering ditaruh "mengambang" — posisinya diatur lewat
 * jarak dari TEPI KERTAS atau dari MARGIN halaman (`relativeFrom="page"`
 * atau `"margin"` pada `<wp:positionH>`/`<wp:positionV>` untuk gambar, atau
 * `mso-position-horizontal-relative:page` pada shape versi lama/VML) —
 * BUKAN mengikuti ke mana pun teks di sekitarnya mengalir.
 *
 * docx-preview (library yang dipakai untuk menampilkan .docx langsung di
 * browser tanpa LibreOffice/Word, lihat print-surat.js) TIDAK memodelkan
 * ini dengan benar: elemen tsb tetap dirender sebagai bagian dari ALIRAN
 * TEKS biasa (`position: relative`), lalu jarak yang seharusnya "dari tepi
 * kertas" itu cuma ditambahkan sebagai geseran CSS `left`/`top` (gambar)
 * atau `margin-left`/`margin-top` (shape VML) DARI POSISI ALAMINYA di
 * alur teks — bukan dari tepi kertas sesungguhnya. Posisi alami itu sendiri
 * dipengaruhi banyak hal (margin halaman, indentasi paragraf, dst), jadi
 * hasilnya jadi bergeser (di project ini sudah dicek langsung ke file
 * template asli: pergeserannya bisa sampai beberapa senti ke kanan/bawah,
 * cukup jauh untuk menabrak teks di sebelahnya).
 *
 * ---------------------------------------------------------------------
 * CARA PERBAIKAN: UKUR POSISI ASLI DI BROWSER, BARU KOREKSI
 * ---------------------------------------------------------------------
 * Daripada menghitung ulang secara manual seberapa jauh pergeserannya
 * (butuh meniru PERSIS algoritma tata-letak Word — margin halaman,
 * indentasi paragraf, tabel, dst — yang sangat rumit dan gampang salah),
 * fungsi di file ini menunggu docx-preview SELESAI merender, lalu:
 *
 * 1. Mengukur posisi & lebar halaman yang BENAR-BENAR dirender browser
 *    (elemen <section class="docx">) sebagai patokan "tepi kertas".
 * 2. Untuk tiap elemen yang di file .docx aslinya memang diatur
 *    `relativeFrom="page"` atau `"margin"`, HITUNG ULANG posisinya
 *    langsung dari tepi kertas hasil pengukuran tsb (bukan dari posisi
 *    alaminya di alur teks), lalu pasang sebagai `position: absolute`.
 * 3. Sumbu yang TIDAK diatur relatif-ke-halaman (mis. relatif ke
 *    paragraf/teks di sekitarnya) SENGAJA tidak disentuh — posisi yang
 *    sudah dirender docx-preview untuk sumbu itu dipertahankan APA
 *    ADANYA (persis pixel yang sama), supaya tidak menimbulkan
 *    pergeseran baru di sumbu yang sebelumnya sudah lumayan benar.
 *
 * Pendekatan "ukur dulu baru koreksi" ini SENGAJA dipilih supaya tidak
 * perlu meniru algoritma tata-letak Word (margin, indentasi paragraf,
 * tabel, dst) satu per satu — cukup percaya pada perhitungan tata-letak
 * browser sendiri untuk segala sesuatu SELAIN yang memang salah.
 *
 * ---------------------------------------------------------------------
 * KETERBATASAN YANG PERLU DIKETAHUI
 * ---------------------------------------------------------------------
 * - Kalau ada LEBIH DARI SATU header (atau footer) berbeda dalam satu
 *   dokumen yang SAMA-SAMA punya elemen mengambang relatif-halaman (mis.
 *   header khusus halaman pertama beda dengan header halaman berikutnya,
 *   dan KEDUANYA punya logo mengambang) — perbaikan ini SENGAJA
 *   dilewati untuk header/footer supaya tidak salah pasang posisi milik
 *   header lain ke header yang salah. Ini kasus yang sangat jarang untuk
 *   template surat sekolah (biasanya cuma satu header dipakai di semua
 *   halaman), dan tidak akan lebih buruk dari sebelumnya (baseline lama
 *   dipertahankan) kalau memang terjadi.
 * - Gambar yang di Word berupa GRUP (gabungan beberapa objek jadi satu,
 *   biasanya dipakai untuk stempel+tanda tangan+QR code) sama sekali
 *   TIDAK didukung docx-preview (bukan cuma salah posisi, tapi hilang
 *   total dari pratinjau/cetak — ini keterbatasan library docx-preview
 *   sendiri, di luar cakupan perbaikan posisi di file ini). Kalau di
 *   surat memang ada elemen begini yang hilang, solusinya di sisi
 *   template Word: pilih objek grupnya, klik kanan → "Save as Picture..."
 *   lalu hapus grup lama dan ganti dengan gambar hasil simpan tsb
 *   (jadi SATU gambar biasa, bukan grup) — gambar tunggal seperti ini
 *   didukung penuh dan akan ikut diperbaiki posisinya oleh file ini.
 */

const EMU_PER_PT = 12700;

/**
 * Hapus bungkus <mc:AlternateContent>...</mc:AlternateContent>, sisakan
 * cuma isi <mc:Fallback>-nya saja. docx-preview TIDAK PERNAH memakai isi
 * <mc:Choice> (lihat variabel `supportedNamespaceURIs` yang sengaja
 * dikosongkan di node_modules/docx-preview/dist/docx-preview.js) — jadi
 * yang benar-benar dirender selalu isi <mc:Fallback>. Transformasi ini
 * dipakai HANYA untuk menghitung urutan elemen <w:drawing> dengan benar
 * (supaya urutannya sama persis dengan yang benar-benar muncul di DOM),
 * TIDAK mengubah file yang diserahkan ke docx-preview.
 */
function samakanDenganYangDirenderDocxPreview(xml) {
    return xml.replace(
        /<mc:AlternateContent>[\s\S]*?<mc:Fallback>([\s\S]*?)<\/mc:Fallback>[\s\S]*?<\/mc:AlternateContent>/g,
        '$1'
    );
}

/** Baca satu nilai atribut w:xxx="angka" dari sepotong XML <w:pgMar .../>. */
function bacaAngkaAtribut(xml, tagXml, namaAtribut) {
    const pola = new RegExp(`<${tagXml}\\b[^>]*\\b${namaAtribut}="(-?\\d+)"`);
    const cocok = xml.match(pola);
    return cocok ? parseInt(cocok[1], 10) : null;
}

/**
 * Ambil ukuran & margin halaman (dalam pt) dari word/document.xml. Dipakai
 * sebagai referensi "tepi kertas"/"tepi margin" untuk elemen yang
 * relativeFrom="page" atau "margin". Kalau section pertama tidak ketemu
 * (dokumen tidak wajar), return null supaya pemanggil tahu untuk
 * melewati semua koreksi (lebih aman daripada menghitung dengan asumsi 0
 * yang belum tentu benar).
 */
export function bacaGeometriHalaman(documentXml) {
    const marginKiriTwip = bacaAngkaAtribut(documentXml, 'w:pgMar', 'w:left');
    const marginAtasTwip = bacaAngkaAtribut(documentXml, 'w:pgMar', 'w:top');

    if (marginKiriTwip == null || marginAtasTwip == null) {
        return null;
    }

    return {
        marginKiriPt: marginKiriTwip / 20,
        marginAtasPt: marginAtasTwip / 20,
    };
}

/**
 * Terjemahkan relativeFrom (drawingML, camelCase: "page", "margin",
 * "leftMargin", "paragraph", dst — atau VML, huruf kecil: "page",
 * "margin", "text", "char") + nilai offset (pt) menjadi posisi ABSOLUT
 * dari tepi kertas (pt). Return null kalau relativeFrom bukan salah satu
 * yang bisa dipastikan (mis. relatif ke paragraf/teks di sekitarnya) —
 * pemanggil akan MEMPERTAHANKAN posisi asli hasil render docx-preview
 * untuk sumbu itu, bukan mengoreksinya.
 */
function keAbsolutDariTepiKertas(relativeFrom, offsetPt, geometriHalaman, sumbu) {
    const rf = (relativeFrom || '').toLowerCase();

    if (rf === 'page') {
        return offsetPt;
    }

    const marginRelatif = [
        'margin', 'leftmargin', 'rightmargin', 'topmargin', 'bottommargin',
        'insidemargin', 'outsidemargin',
    ];
    if (marginRelatif.includes(rf)) {
        const marginPt = sumbu === 'x' ? geometriHalaman.marginKiriPt : geometriHalaman.marginAtasPt;
        return marginPt + offsetPt;
    }

    return null;
}

/**
 * Parse semua <w:drawing> di satu bagian dokumen (document.xml / headerN.xml
 * / footerN.xml), URUT SESUAI KEMUNCULAN, supaya urutannya bisa dicocokkan
 * satu-satu dengan elemen <div> hasil render docx-preview (yang juga
 * muncul urut sesuai XML sumbernya). Tiap entri:
 *   - null → bukan anchor (gambar inline biasa / tidak relevan), JANGAN
 *     disentuh sama sekali.
 *   - { xPt, yPt } → posisi absolut dari tepi kertas yang SUDAH dihitung
 *     untuk sumbu yang relativeFrom-nya "page"/"margin" (properti xPt/yPt
 *     bisa salah satu saja kalau cuma satu sumbu yang begitu).
 */
export function parseUrutanAnchorGambar(partXml, geometriHalaman) {
    const xmlBersih = samakanDenganYangDirenderDocxPreview(partXml);
    const hasil = [];
    const polaDrawing = /<w:drawing>([\s\S]*?)<\/w:drawing>/g;
    let m;

    while ((m = polaDrawing.exec(xmlBersih)) !== null) {
        const isi = m[1];
        const anchorMatch = isi.match(/<wp:anchor\b[\s\S]*?<\/wp:anchor>/);

        if (!anchorMatch) {
            hasil.push(null);
            continue;
        }

        const anchorXml = anchorMatch[0];
        const posH = bacaSumbuPosisi(anchorXml, 'positionH');
        const posV = bacaSumbuPosisi(anchorXml, 'positionV');

        let xPt = null;
        let yPt = null;

        if (posH) {
            xPt = keAbsolutDariTepiKertas(posH.relativeFrom, posH.offsetEmu / EMU_PER_PT, geometriHalaman, 'x');
        }
        if (posV) {
            yPt = keAbsolutDariTepiKertas(posV.relativeFrom, posV.offsetEmu / EMU_PER_PT, geometriHalaman, 'y');
        }

        hasil.push((xPt != null || yPt != null) ? { xPt, yPt } : null);
    }

    return hasil;
}

function bacaSumbuPosisi(anchorXml, namaTag) {
    const pola = new RegExp(`<wp:${namaTag}\\b[^>]*relativeFrom="([^"]+)"[^>]*>([\\s\\S]*?)</wp:${namaTag}>`);
    const cocok = anchorXml.match(pola);
    if (!cocok) {
        return null;
    }

    const relativeFrom = cocok[1];
    const isi = cocok[2];
    const offsetMatch = isi.match(/<wp:posOffset>(-?\d+)<\/wp:posOffset>/);
    if (!offsetMatch) {
        // Posisi berbasis <wp:align> (kiri/tengah/kanan), bukan offset
        // angka — docx-preview sudah punya penanganan sendiri untuk ini
        // (float kiri/kanan), sengaja tidak disentuh.
        return null;
    }

    return { relativeFrom, offsetEmu: parseInt(offsetMatch[1], 10) };
}

/**
 * Cari SATU bagian (dari daftar `partNames`, misal ['word/header1.xml',
 * 'word/header2.xml', 'word/header3.xml']) yang punya paling sedikit satu
 * anchor yang bisa dikoreksi, DAN pastikan cuma SATU bagian yang begitu.
 * Kalau nol atau lebih dari satu bagian yang punya anchor terkoreksi,
 * return null (lewati perbaikan header/footer sepenuhnya) — lebih aman
 * daripada salah menempelkan data posisi milik header/footer yang beda
 * (lihat "KETERBATASAN" di komentar atas file ini).
 */
function pilihSumberAnchorTunggal(partNames, ambilXmlBagian, geometriHalaman) {
    let terpilih = null;

    for (const nama of partNames) {
        const xml = ambilXmlBagian(nama);
        if (!xml) {
            continue;
        }

        const urutan = parseUrutanAnchorGambar(xml, geometriHalaman);
        const adaYangBisaDikoreksi = urutan.some((d) => d != null);

        if (adaYangBisaDikoreksi) {
            if (terpilih != null) {
                // Lebih dari satu bagian punya anchor — ambigu, batalkan.
                return null;
            }
            terpilih = urutan;
        }
    }

    return terpilih;
}

/** Cari elemen <div> hasil render docx-preview untuk gambar "mengambang" (drawingML). */
function cariDivGambarMengambang(scopeEl) {
    return Array.from(scopeEl.querySelectorAll('div')).filter(
        (el) => el.style.position === 'relative' && el.style.display === 'inline-block'
    );
}

/** Cari elemen <svg> hasil render docx-preview untuk shape versi lama (VML) yang mengambang. */
function cariSvgShapeMengambang(scopeEl) {
    return Array.from(scopeEl.querySelectorAll('svg')).filter(
        (el) => /mso-position-(horizontal|vertical)-relative/i.test(el.getAttribute('style') || '')
    );
}

function pxPerPt(pageEl) {
    const lebarPt = parseFloat(pageEl.style.width);
    if (!lebarPt) {
        return null;
    }
    return pageEl.getBoundingClientRect().width / lebarPt;
}

/**
 * Pasang ulang posisi satu elemen (div gambar ATAU svg shape) supaya
 * SUMBU yang seharusnya relatif-ke-halaman benar-benar diukur dari tepi
 * kertas, dan SUMBU lainnya dipertahankan persis seperti hasil render
 * docx-preview apa adanya (lihat penjelasan strategi di atas).
 */
function pasangPosisiAbsolut(el, pageEl, targetXPt, targetYPt) {
    const skala = pxPerPt(pageEl);
    if (!skala) {
        return;
    }

    const rectEl = el.getBoundingClientRect();
    const rectHalaman = pageEl.getBoundingClientRect();
    const xSaatIniPx = rectEl.left - rectHalaman.left;
    const ySaatIniPx = rectEl.top - rectHalaman.top;

    const xPx = targetXPt != null ? targetXPt * skala : xSaatIniPx;
    const yPx = targetYPt != null ? targetYPt * skala : ySaatIniPx;

    if (getComputedStyle(pageEl).position === 'static') {
        pageEl.style.position = 'relative';
    }

    el.style.position = 'absolute';
    el.style.margin = '0';
    el.style.left = `${xPx}px`;
    el.style.top = `${yPx}px`;
}

/** Terapkan koreksi gambar drawingML (div) di satu ruang lingkup (satu instance header/footer/article) memakai satu daftar urutan anchor. */
function terapkanKoreksiGambar(scopeEl, pageEl, urutanAnchor) {
    if (!urutanAnchor) {
        return;
    }

    const divs = cariDivGambarMengambang(scopeEl);

    urutanAnchor.forEach((deskripsi, i) => {
        if (!deskripsi || !divs[i]) {
            return;
        }
        pasangPosisiAbsolut(divs[i], pageEl, deskripsi.xPt, deskripsi.yPt);
    });
}

/** Terapkan koreksi shape VML (svg) di satu ruang lingkup — tidak butuh data XML sama sekali, semua info (relativeFrom & jarak) sudah ada langsung di atribut style hasil render. */
function terapkanKoreksiShapeVml(scopeEl, pageEl, geometriHalaman) {
    for (const svg of cariSvgShapeMengambang(scopeEl)) {
        const style = svg.getAttribute('style') || '';

        const relH = (style.match(/mso-position-horizontal-relative\s*:\s*([a-z]+)/i) || [])[1];
        const relV = (style.match(/mso-position-vertical-relative\s*:\s*([a-z]+)/i) || [])[1];
        const marginLeftPt = parseFloat((style.match(/margin-left\s*:\s*(-?[\d.]+)pt/i) || [])[1]);
        const marginTopPt = parseFloat((style.match(/margin-top\s*:\s*(-?[\d.]+)pt/i) || [])[1]);

        const xPt = Number.isFinite(marginLeftPt)
            ? keAbsolutDariTepiKertas(relH, marginLeftPt, geometriHalaman, 'x')
            : null;
        const yPt = Number.isFinite(marginTopPt)
            ? keAbsolutDariTepiKertas(relV, marginTopPt, geometriHalaman, 'y')
            : null;

        if (xPt == null && yPt == null) {
            continue;
        }

        pasangPosisiAbsolut(svg, pageEl, xPt, yPt);
    }
}

/**
 * Fungsi utama: panggil ini SETELAH docx-preview selesai merender
 * (setelah Promise dari renderAsync/renderDocxPreview selesai).
 *
 * @param {HTMLElement} containerEl elemen pembungkus hasil render docx-preview
 *   (yang berisi satu atau lebih <section class="docx">, satu per halaman).
 * @param {(namaBagian: string) => (string|null)} ambilXmlBagian fungsi untuk
 *   mengambil isi XML mentah suatu bagian docx (mis. 'word/header2.xml'),
 *   return null kalau bagian itu tidak ada di file docx yang dirender.
 */
export function perbaikiPosisiAnchor(containerEl, ambilXmlBagian) {
    try {
        const documentXml = ambilXmlBagian('word/document.xml');
        if (!documentXml) {
            return;
        }

        const geometriHalaman = bacaGeometriHalaman(documentXml);
        if (!geometriHalaman) {
            return;
        }

        const urutanBody = parseUrutanAnchorGambar(documentXml, geometriHalaman);
        const urutanHeader = pilihSumberAnchorTunggal(
            ['word/header1.xml', 'word/header2.xml', 'word/header3.xml'],
            ambilXmlBagian,
            geometriHalaman
        );
        const urutanFooter = pilihSumberAnchorTunggal(
            ['word/footer1.xml', 'word/footer2.xml', 'word/footer3.xml'],
            ambilXmlBagian,
            geometriHalaman
        );

        const halaman = Array.from(containerEl.querySelectorAll('section.docx'));

        // Shape versi lama (VML): tidak butuh pencocokan urutan XML sama
        // sekali (semua info yang dibutuhkan sudah ada langsung di atribut
        // style hasil render, lihat terapkanKoreksiShapeVml), jadi cukup
        // disapu sekali per halaman, mencakup header+isi+footer sekaligus.
        for (const sectionEl of halaman) {
            terapkanKoreksiShapeVml(sectionEl, sectionEl, geometriHalaman);
        }

        // Body: setiap elemen isi dokumen muncul PERSIS SEKALI di seluruh
        // halaman, urut sesuai dokumen aslinya (docx-preview tidak pernah
        // mengulang/mengacak isi body ke banyak halaman) — jadi cukup
        // dicocokkan satu-per-satu lintas semua halaman sekaligus.
        if (urutanBody.some((d) => d != null)) {
            let indexBody = 0;
            for (const sectionEl of halaman) {
                for (const article of sectionEl.querySelectorAll(':scope > article')) {
                    const divs = cariDivGambarMengambang(article);
                    for (const div of divs) {
                        const deskripsi = urutanBody[indexBody];
                        if (deskripsi) {
                            pasangPosisiAbsolut(div, sectionEl, deskripsi.xPt, deskripsi.yPt);
                        }
                        indexBody += 1;
                    }
                }
            }
        }

        // Header & footer: kontennya diulang sama persis di tiap halaman,
        // jadi daftar urutan yang SAMA dipakai ulang untuk tiap instance.
        // (Shape VML sudah dibereskan lewat sapuan per-halaman di atas,
        // jadi di sini cukup urus gambar drawingML saja.)
        for (const sectionEl of halaman) {
            const headerEl = sectionEl.querySelector(':scope > header');
            if (headerEl) {
                terapkanKoreksiGambar(headerEl, sectionEl, urutanHeader);
            }

            const footerEl = sectionEl.querySelector(':scope > footer');
            if (footerEl) {
                terapkanKoreksiGambar(footerEl, sectionEl, urutanFooter);
            }
        }
    } catch (err) {
        console.warn('Gagal memperbaiki posisi elemen mengambang pada docx, memakai hasil render apa adanya:', err);
    }
}
