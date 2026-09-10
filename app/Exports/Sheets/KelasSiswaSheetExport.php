<?php

namespace App\Exports\Sheets;

use App\Models\Kelas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Sheet per kelas (mis. "VII-A") — daftar siswa aktifnya, sama seperti tab
 * kelas pada halaman Data Kelas Siswa.
 */
class KelasSiswaSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(private Kelas $kelas)
    {
    }

    public function collection(): Collection
    {
        return $this->kelas->siswaAktif;
    }

    public function map($siswa): array
    {
        return [
            $siswa->nis,
            $siswa->nisn,
            $siswa->nama,
            $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            $siswa->no_hp_ortu ?: '-',
        ];
    }

    public function headings(): array
    {
        return ['NIS', 'NISN', 'Nama', 'Jenis Kelamin', 'Kontak Ortu'];
    }

    public function title(): string
    {
        // Judul sheet Excel maksimal 31 karakter dan tidak boleh berisi
        // karakter \ / ? * [ ] : — dibersihkan supaya export tidak error
        // kalau ada nama kelas yang tidak biasa.
        $title = "{$this->kelas->tingkat}-{$this->kelas->nama_kelas}";
        $title = preg_replace('/[\\\\\/\?\*\[\]:]/', '-', $title);

        return mb_substr($title, 0, 31);
    }
}
