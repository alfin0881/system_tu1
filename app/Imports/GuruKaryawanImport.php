<?php
namespace App\Imports;
use App\Models\GuruKaryawan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Imports\Concerns\ParsesExcelDates;

class GuruKaryawanImport implements ToModel, WithHeadingRow
{
    use ParsesExcelDates;

    public function model(array $row): \Illuminate\Database\Eloquent\Model|array|null
    {
        $jkRaw = $row['jk_lp'] ?? $row['jenis_kelamin'] ?? 'L';
        $jk = strtoupper(substr(trim((string) $jkRaw), 0, 1));
        if (!in_array($jk, ['L', 'P'])) { $jk = 'L'; }

        $statusRaw = strtoupper(trim((string) ($row['status_pnsnon_pns'] ?? $row['status_kepegawaian'] ?? 'Non PNS')));
        $statusKepegawaian = str_starts_with($statusRaw, 'PNS') ? 'PNS' : 'Non PNS';

        return new GuruKaryawan([
            'nip_niy'             => $row['nip'] ?? $row['nipniy'] ?? $row['nip_niy'] ?? null,
            'no_ktp'              => $row['no_ktp'] ?? null,
            'nama'                => $row['nama'] ?? $row['nama_lengkap'] ?? null,
            'gelar_akademik'      => $row['gelar_akademik'] ?? null,
            'jenis_kelamin'       => $jk,
            'tempat_lahir'        => $row['tempat_lahir'] ?? null,
            'tanggal_lahir'       => $this->parseExcelDate($row['tanggal_lahir'] ?? null),
            'jabatan'             => $row['jabatan'] ?? 'Guru Mapel',
            'mengajar'            => $row['mengajar'] ?? null,
            'golongan_pangkat'    => $row['golongan'] ?? $row['golongan_pangkat'] ?? null,
            'alamat'              => $row['alamat_rumah_lengkap'] ?? $row['alamat'] ?? null,
            'kelurahan'           => $row['kelurahan'] ?? null,
            'kecamatan'           => $row['kecamatan'] ?? null,
            'pendidikan_terakhir' => $row['pendidikan_terakhir'] ?? null,
            'nama_ibu_kandung'    => $row['ibu_kandung'] ?? $row['nama_ibu_kandung'] ?? null,
            'no_hp'               => $row['no_hp'] ?? null,
            'email'               => $row['email'] ?? null,
            'status'              => $row['status'] ?? 'aktif',
            'status_kepegawaian'  => $statusKepegawaian,
            'tmt_madrasah'        => $this->parseExcelDate($row['tmt_di_madrasah'] ?? $row['tmt_madrasah'] ?? null),
            'tmt_pns'             => $this->parseExcelDate($row['tmt_pns'] ?? null),
            'tmt_golongan'        => $this->parseExcelDate($row['tmt_golongan'] ?? null),
        ]);
    }
}
