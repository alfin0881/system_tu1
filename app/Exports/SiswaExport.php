<?php
namespace App\Exports;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SiswaExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return Siswa::all();
    }
    public function map($siswa): array
    {
        return [
            $siswa->nisn, $siswa->nis, $siswa->nama, $siswa->tempat_lahir, $siswa->tanggal_lahir,
            $siswa->jenis_kelamin, $siswa->agama, $siswa->alamat, $siswa->nama_ayah, $siswa->nama_ibu,
            $siswa->nama_wali, $siswa->no_hp_ortu, $siswa->kelas_id, $siswa->status, $siswa->tahun_masuk,
        ];
    }
    public function headings(): array
    {
        return [
            'NISN', 'NIS', 'Nama Siswa', 'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin', 'Agama',
            'Alamat', 'Nama Ayah', 'Nama Ibu', 'Nama Wali', 'No HP Ortu', 'Kelas ID', 'Status', 'Tahun Masuk'
        ];
    }
}