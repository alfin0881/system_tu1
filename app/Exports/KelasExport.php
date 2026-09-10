<?php

namespace App\Exports;

use App\Exports\Sheets\KelasRekapSheetExport;
use App\Exports\Sheets\KelasSiswaSheetExport;
use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Export "Data Kelas Siswa" — sebelumnya hanya berisi satu sheet rekap
 * (nama kelas, tingkat, wali kelas, tahun ajaran) tanpa daftar siswanya.
 * Sekarang mengikuti struktur tab pada halaman index: satu sheet "Rekap"
 * DITAMBAH satu sheet per kelas (mis. VII-A, VII-B, ..., VII-H) berisi
 * daftar siswa aktif di kelas tersebut.
 *
 * Catatan Maatwebsite Excel v4: kelas yang memakai WithMultipleSheets WAJIB
 * ikut implements marker interface Export, kalau tidak Excel::download()
 * akan menolaknya dengan TypeError (lihat UPGRADE-4.x.md).
 */
class KelasExport implements Export, WithMultipleSheets
{
    public function sheets(): array
    {
        $kelas = Kelas::with(['waliKelas', 'tahunAjaran'])
            ->with(['siswaAktif' => fn ($q) => $q->orderBy('nama')])
            ->withCount([
                'siswaAktif',
                'siswaAktif as siswa_l_count' => fn ($q) => $q->where('jenis_kelamin', 'L'),
                'siswaAktif as siswa_p_count' => fn ($q) => $q->where('jenis_kelamin', 'P'),
            ])
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $sheets = [
            new KelasRekapSheetExport($kelas),
        ];

        foreach ($kelas as $k) {
            $sheets[] = new KelasSiswaSheetExport($k);
        }

        return $sheets;
    }
}
