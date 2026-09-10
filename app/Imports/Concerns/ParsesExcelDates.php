<?php

namespace App\Imports\Concerns;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Konversi nilai tanggal dari cell Excel menjadi format Y-m-d yang aman
 * disimpan ke kolom bertipe date.
 *
 * Cell tanggal di Excel bisa datang dalam dua bentuk saat dibaca lewat
 * Maatwebsite/PhpSpreadsheet:
 *  - Angka serial Excel (mis. 45123), jika cell diformat sebagai Date/Number
 *  - Teks tanggal biasa (mis. "12/01/2020", "2020-01-12")
 *
 * Tanpa konversi ini, angka serial akan lolos begitu saja ke kolom yang
 * di-cast 'date' pada model Eloquent. Karena angka tsb is_numeric(),
 * Eloquent menganggapnya sebagai Unix timestamp (detik sejak 1-1-1970),
 * padahal itu adalah hari ke-N sejak 30-12-1899 (epoch Excel). Serial
 * seperti 45123 sebagai Unix timestamp hanya berjarak beberapa jam dari
 * 1-1-1970, sehingga tanggal yang tersimpan selalu tampil "01-01-1970".
 */
trait ParsesExcelDates
{
    private function parseExcelDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
