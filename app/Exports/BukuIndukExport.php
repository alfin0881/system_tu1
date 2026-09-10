<?php
namespace App\Exports;
use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BukuIndukExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return Siswa::orderBy('id')->get();
    }

    public function map($siswa): array
    {
        return [
            $siswa->nis,
            $siswa->nis_lokal,
            $siswa->nama,
            $siswa->nisn,
            $siswa->nik,
            $siswa->tempat_lahir,
            optional($siswa->tanggal_lahir)->format('d-m-Y'),
            $siswa->jenis_kelamin,
            $siswa->status_dalam_keluarga,
            $siswa->anak_ke,
            $siswa->jumlah_saudara_kandung,
            $siswa->no_kk,
            $siswa->kepala_keluarga,
            $siswa->status_ayah,
            $siswa->nik_ayah,
            $siswa->nama_ayah,
            $siswa->tempat_lahir_ayah,
            optional($siswa->tanggal_lahir_ayah)->format('d-m-Y'),
            $siswa->pendidikan_terakhir_ayah,
            $siswa->pekerjaan_ayah,
            $siswa->penghasilan_ayah,
            $siswa->status_ibu,
            $siswa->nik_ibu,
            $siswa->nama_ibu,
            $siswa->tempat_lahir_ibu,
            optional($siswa->tanggal_lahir_ibu)->format('d-m-Y'),
            $siswa->pendidikan_terakhir_ibu,
            $siswa->pekerjaan_ibu,
            $siswa->penghasilan_ibu,
            $siswa->status_wali,
            $siswa->nik_wali,
            $siswa->nama_wali,
            $siswa->tempat_lahir_wali,
            optional($siswa->tanggal_lahir_wali)->format('d-m-Y'),
            $siswa->pendidikan_terakhir_wali,
            $siswa->pekerjaan_wali,
            $siswa->penghasilan_wali,
            $siswa->no_hp_ortu,
            $siswa->alamat,
            $siswa->desa_kelurahan,
            $siswa->kecamatan,
            $siswa->kabupaten_kota,
            $siswa->provinsi,
            $siswa->kode_pos,
            $siswa->jenis_sekolah,
            $siswa->status_sekolah,
            $siswa->npsn_nsm,
            $siswa->nama_sekolah,
            $siswa->status_kepemilikan_kip,
            $siswa->no_kip,
            $siswa->pondok_pesantren,
            optional($siswa->tanggal_masuk)->format('d-m-Y'),
        ];
    }

    public function headings(): array
    {
        return [
            'NIS', 'NIS Lokal', 'Nama Lengkap', 'NISN', 'Nomor Induk Kependudukan',
            'Tempat Lahir', 'Tanggal Lahir', 'L/P',
            'Status dalam keluarga', 'Anak Ke', 'Jumlah Saudara Kandung', 'No. Kartu Keluarga', 'Kepala Keluarga',
            'Status Ayah', 'NIK Ayah', 'Nama Ayah', 'Tempat Lahir Ayah', 'Tanggal Lahir Ayah',
            'Pendidikan Terakhir Ayah', 'Pekerjaan Ayah', 'Penghasilan Ayah',
            'Status Ibu', 'NIK Ibu', 'Nama Ibu', 'Tempat Lahir Ibu', 'Tanggal Lahir Ibu',
            'Pendidikan Terakhir Ibu', 'Pekerjaan Ibu', 'Penghasilan Ibu',
            'Status Wali', 'NIK Wali', 'Nama Wali', 'Tempat Lahir Wali', 'Tanggal Lahir Wali',
            'Pendidikan Terakhir Wali', 'Pekerjaan Wali', 'Penghasilan Wali',
            'No. HP', 'Alamat', 'Desa/Kelurahan', 'Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Kode Pos',
            'Jenis Sekolah', 'Status Sekolah', 'NPSN/NSM', 'Nama Sekolah',
            'Status Kepemilikan KIP', 'No. KIP', 'Pondok Pesantren', 'Tanggal Masuk',
        ];
    }
}
