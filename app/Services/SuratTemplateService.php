<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;

/**
 * Menangani seluruh urusan template surat berbasis docx:
 * - Mendeteksi daftar placeholder ${nama_variabel} yang ada di file docx
 *   yang diupload TU/Admin saat membuat/mengubah Jenis Surat, dipakai untuk
 *   membangun form pengisian surat secara dinamis (tanpa kop surat manual,
 *   karena kop surat sudah ada di dalam file docx itu sendiri).
 * - Mengisi placeholder tersebut dengan data isian dari form pembuatan
 *   surat, lalu menghasilkan file docx baru (surat jadi) yang siap
 *   diunduh/dicetak, tanpa mengubah file template aslinya.
 */
class SuratTemplateService
{
    /**
     * Ambil daftar nama placeholder ${...} pada sebuah file docx.
     * Hasil diurutkan alfabet supaya tampilan form konsisten.
     */
    public function extractPlaceholders(string $absolutePath): array
    {
        $processor = new TemplateProcessor($absolutePath);
        $variables = $processor->getVariables();

        $variables = array_values(array_unique($variables));
        sort($variables);

        return $variables;
    }

    /**
     * Isi template dengan data (key => value) lalu simpan sebagai file docx
     * baru di $outputAbsolutePath. Key yang tidak ada di template otomatis
     * diabaikan oleh PhpWord; key di template yang tidak diisi otomatis
     * diganti dengan strip (-) agar tidak ada placeholder mentah tersisa.
     */
    public function generate(string $templateAbsolutePath, array $data, string $outputAbsolutePath): void
    {
        $processor = new TemplateProcessor($templateAbsolutePath);

        // Cocokkan nama variabel di docx dengan key $data tanpa peduli
        // besar/kecil huruf (mis. placeholder ${Nomor_Surat} di docx tetap
        // terisi dari key 'nomor_surat'), supaya nilai standar seperti
        // nomor surat, tanggal, dan perihal tetap otomatis terisi walau
        // penulisan huruf kapitalnya berbeda di template.
        $dataLower = [];
        foreach ($data as $key => $value) {
            $dataLower[strtolower($key)] = $value;
        }

        foreach ($processor->getVariables() as $variable) {
            $value = $data[$variable] ?? $dataLower[strtolower($variable)] ?? '';
            $processor->setValue($variable, $this->escape($value));
        }

        $processor->saveAs($outputAbsolutePath);
    }

    /**
     * Placeholder nilai yang aman disisipkan ke XML docx: karakter spesial
     * HTML/XML di-escape, dan baris baru pada isian bertipe paragraf/textarea
     * dikonversi jadi line break Word (<w:br/>) supaya format multi-baris
     * dari form tetap terlihat rapi di hasil surat.
     */
    private function escape(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return '-';
        }

        $escaped = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return str_replace(["\r\n", "\r", "\n"], '</w:t><w:br/><w:t xml:space="preserve">', $escaped);
    }
}
