<?php

namespace App\Imports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Imports\Concerns\ParsesExcelDates;

class SiswaImport implements ToModel, WithHeadingRow
{
    use ParsesExcelDates;

    public function model(array $row): \Illuminate\Database\Eloquent\Model|array|null
    {
        // Ambil huruf pertama dari inputan jenis kelamin (Misal: "Laki-laki" -> "L", "Perempuan" -> "P")
        $jkRaw = $row['jenis_kelamin'] ?? 'L';
        $jk = strtoupper(substr(trim($jkRaw), 0, 1));
        if (!in_array($jk, ['L', 'P'])) {
            $jk = 'L'; // Default jika format tidak dikenali
        }

        return new Siswa([
            'nisn'          => $row['nisn'] ?? null,
            'nis'           => $row['nis'] ?? null,
            'nama'          => $row['nama_siswa'] ?? $row['nama'] ?? null,
            'tempat_lahir'  => $row['tempat_lahir'] ?? null,
            'tanggal_lahir' => $this->parseExcelDate($row['tanggal_lahir'] ?? null),
            'jenis_kelamin' => $jk,
            'agama'         => $row['agama'] ?? null,
            'alamat'        => $row['alamat'] ?? null,
            'nama_ayah'     => $row['nama_ayah'] ?? null,
            'nama_ibu'      => $row['nama_ibu'] ?? null,
            'nama_wali'     => $row['nama_wali'] ?? null,
            'no_hp_ortu'    => $row['no_hp_ortu'] ?? null,
            'kelas_id'      => $row['kelas_id'] ?? null,
            'status'        => $row['status'] ?? 'aktif',
            'tahun_masuk'   => $row['tahun_masuk'] ?? null,
        ]);
    }
}
