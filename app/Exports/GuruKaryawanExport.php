<?php
namespace App\Exports;
use App\Models\GuruKaryawan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GuruKaryawanExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return GuruKaryawan::orderBy('id')->get();
    }

    public function map($guru): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $guru->nama,
            $guru->jenis_kelamin,
            $guru->gelar_akademik,
            $guru->nip_niy,
            $guru->tempat_lahir,
            optional($guru->tanggal_lahir)->format('d-m-Y'),
            $guru->no_ktp,
            $guru->status_kepegawaian,
            optional($guru->tmt_madrasah)->format('d-m-Y'),
            optional($guru->tmt_pns)->format('d-m-Y'),
            optional($guru->tmt_golongan)->format('d-m-Y'),
            $guru->golongan_pangkat,
            $guru->mengajar,
            $guru->alamat,
            $guru->kelurahan,
            $guru->kecamatan,
            $guru->pendidikan_terakhir,
            $guru->nama_ibu_kandung,
            $guru->no_hp,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'JK (L/P)',
            'Gelar Akademik',
            'NIP',
            'Tempat Lahir',
            'Tanggal Lahir',
            'No KTP',
            'Status PNS/Non PNS',
            'TMT di Madrasah',
            'TMT PNS',
            'TMT Golongan',
            'Golongan',
            'Mengajar',
            'Alamat Rumah Lengkap',
            'Kelurahan',
            'Kecamatan',
            'Pendidikan Terakhir',
            'Ibu Kandung',
            'No HP',
        ];
    }
}
