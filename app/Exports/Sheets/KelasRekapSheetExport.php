<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Sheet "Rekap" — sama seperti tab Rekap pada halaman Data Kelas Siswa:
 * jumlah siswa L/P/L+P per tingkat-rombel, beserta wali kelasnya.
 */
class KelasRekapSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(private Collection $kelas)
    {
    }

    public function collection(): Collection
    {
        return $this->kelas;
    }

    public function map($kelas): array
    {
        return [
            $kelas->tingkat.' - '.$kelas->nama_kelas,
            $kelas->siswa_l_count,
            $kelas->siswa_p_count,
            $kelas->siswa_aktif_count,
            $kelas->waliKelas->nama ?? '-',
            $kelas->tahunAjaran->label ?? '-',
        ];
    }

    public function headings(): array
    {
        return ['Tingkat - Rombel', 'L', 'P', 'L+P', 'Wali Kelas', 'Tahun Ajaran'];
    }

    public function title(): string
    {
        return 'Rekap';
    }
}
