<?php
namespace App\Imports;
use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Imports\Concerns\ParsesExcelDates;

/**
 * Mapping kolom mengikuti header asli file Excel Buku Induk madrasah:
 * NIS, NIS Lokal, Nama Lengkap, NISN, Nomor Induk Kependudukan, Tempat
 * Lahir, Tanggal Lahir, L/P, Status dalam keluarga, Anak Ke, Jumlah
 * Saudara Kandung, No. Kartu Keluarga, Kepala Keluarga, data Ayah/Ibu/Wali
 * (Status, NIK, Nama, Tempat/Tanggal Lahir, Pendidikan Terakhir,
 * Pekerjaan, Penghasilan), No. HP, Alamat, Desa/Kelurahan, Kecamatan,
 * Kabupaten/Kota, Provinsi, Kode Pos, Jenis Sekolah, Status Sekolah,
 * NPSN/NSM, Nama Sekolah, Status Kepemilikan KIP, No. KIP, Pondok
 * Pesantren, Tanggal Masuk.
 *
 * PENTING soal nama key setelah header dibaca Laravel Excel: heading
 * diproses lewat Str::slug() yang MEMBUANG karakter "." dan "/" (bukan
 * menggantinya dengan underscore). Jadi header "L/P" jadi key "lp",
 * "Desa/Kelurahan" jadi "desakelurahan", "Kabupaten/Kota" jadi
 * "kabupatenkota", "NPSN/NSM" jadi "npsnnsm", "No. HP" jadi "no_hp", dst.
 * Beberapa alias ditambahkan untuk jaga-jaga jika header sedikit berbeda.
 */
class BukuIndukImport implements ToModel, WithHeadingRow
{
    use ParsesExcelDates;

    /**
     * Angkatan (tahun_ajaran_masuk_id) tujuan import. Wajib diisi supaya
     * siswa hasil import tercatat di angkatan yang benar dan muncul di
     * filter "Tahun Ajaran (Angkatan)" pada halaman Buku Induk — sebelumnya
     * kolom ini tidak pernah diisi sehingga siswa hasil import "hilang"
     * (tersimpan di database tapi tidak match filter angkatan manapun).
     */
    public function __construct(private ?int $tahunAjaranId = null)
    {
    }

    /**
     * Potong nilai string agar tidak melebihi panjang kolom di database,
     * supaya proses import tidak gagal total hanya karena satu sel Excel
     * berisi data yang terlalu panjang (mis. NPSN dan NSM digabung).
     */
    private function truncate(mixed $value, int $maxLength): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, $maxLength);
    }

    public function model(array $row): \Illuminate\Database\Eloquent\Model|array|null
    {
        // Lewati baris yang NIS dan NIS Lokal-nya sama-sama kosong (mis.
        // baris kosong di akhir file Excel, baris pemisah, atau baris yang
        // belum diisi lengkap). Kolom nis wajib diisi (NOT NULL & unique)
        // di database. Sebagian file sumber sekolah tidak memiliki kolom
        // "NIS" nasional terpisah — hanya "NIS Lokal" — sehingga NIS Lokal
        // dipakai sebagai cadangan (fallback) pengisi kolom nis.
        $nisAsli = trim((string) ($row['nis'] ?? ''));
        $nisLokal = trim((string) ($row['nis_lokal'] ?? ''));
        $nis = $nisAsli !== '' ? $nisAsli : $nisLokal;
        if ($nis === '') {
            return null;
        }

        $jkRaw = $row['lp'] ?? $row['l_p'] ?? $row['jenis_kelamin'] ?? 'L';
        $jk = strtoupper(substr(trim((string) $jkRaw), 0, 1));
        if (!in_array($jk, ['L', 'P'])) {
            $jk = 'L';
        }

        return new Siswa([
            'nis'                       => $nis,
            'nis_lokal'                 => $nisLokal !== '' ? $nisLokal : null,
            'nama'                      => $row['nama_lengkap'] ?? $row['nama_siswa'] ?? $row['nama'] ?? null,
            'nisn'                      => $row['nisn'] ?? null,
            'nik'                       => $this->truncate($row['nomor_induk_kependudukan'] ?? $row['nik'] ?? null, 50),
            'tempat_lahir'              => $row['tempat_lahir'] ?? null,
            'tanggal_lahir'             => $this->parseExcelDate($row['tanggal_lahir'] ?? null),
            'jenis_kelamin'             => $jk,

            'status_dalam_keluarga'     => $row['status_dalam_keluarga'] ?? null,
            'anak_ke'                   => $row['anak_ke'] ?? null,
            'jumlah_saudara_kandung'    => $row['jumlah_saudara_kandung'] ?? null,
            'no_kk'                     => $this->truncate($row['no_kartu_keluarga'] ?? $row['no_kk'] ?? null, 50),
            'kepala_keluarga'           => $row['kepala_keluarga'] ?? null,

            'nama_ayah'                 => $row['nama_ayah'] ?? null,
            'status_ayah'               => $row['status_ayah'] ?? null,
            'nik_ayah'                  => $this->truncate($row['nik_ayah'] ?? null, 50),
            'tempat_lahir_ayah'         => $row['tempat_lahir_ayah'] ?? null,
            'tanggal_lahir_ayah'        => $this->parseExcelDate($row['tanggal_lahir_ayah'] ?? null),
            'pendidikan_terakhir_ayah'  => $row['pendidikan_terakhir_ayah'] ?? null,
            'pekerjaan_ayah'            => $row['pekerjaan_ayah'] ?? null,
            'penghasilan_ayah'          => $row['penghasilan_ayah'] ?? null,

            'nama_ibu'                  => $row['nama_ibu'] ?? null,
            'status_ibu'                => $row['status_ibu'] ?? null,
            'nik_ibu'                   => $this->truncate($row['nik_ibu'] ?? null, 50),
            'tempat_lahir_ibu'          => $row['tempat_lahir_ibu'] ?? null,
            'tanggal_lahir_ibu'         => $this->parseExcelDate($row['tanggal_lahir_ibu'] ?? null),
            'pendidikan_terakhir_ibu'   => $row['pendidikan_terakhir_ibu'] ?? null,
            'pekerjaan_ibu'             => $row['pekerjaan_ibu'] ?? null,
            'penghasilan_ibu'           => $row['penghasilan_ibu'] ?? null,

            'nama_wali'                 => $row['nama_wali'] ?? null,
            'status_wali'               => $row['status_wali'] ?? null,
            'nik_wali'                  => $this->truncate($row['nik_wali'] ?? null, 50),
            'tempat_lahir_wali'         => $row['tempat_lahir_wali'] ?? null,
            'tanggal_lahir_wali'        => $this->parseExcelDate($row['tanggal_lahir_wali'] ?? null),
            'pendidikan_terakhir_wali'  => $row['pendidikan_terakhir_wali'] ?? null,
            'pekerjaan_wali'            => $row['pekerjaan_wali'] ?? null,
            'penghasilan_wali'          => $row['penghasilan_wali'] ?? null,

            'no_hp_ortu'                => $this->truncate($row['no_hp'] ?? $row['no_hp_ortu'] ?? null, 50),
            'alamat'                    => $row['alamat'] ?? null,
            'desa_kelurahan'            => $row['desakelurahan'] ?? $row['desa_kelurahan'] ?? $row['kelurahan'] ?? null,
            'kecamatan'                 => $row['kecamatan'] ?? null,
            'kabupaten_kota'            => $row['kabupatenkota'] ?? $row['kabupaten_kota'] ?? null,
            'provinsi'                  => $row['provinsi'] ?? null,
            'kode_pos'                  => $row['kode_pos'] ?? null,

            'jenis_sekolah'             => $row['jenis_sekolah'] ?? null,
            'status_sekolah'            => $row['status_sekolah'] ?? null,
            'npsn_nsm'                  => $this->truncate($row['npsnnsm'] ?? $row['npsn_nsm'] ?? null, 50),
            'nama_sekolah'              => $row['nama_sekolah'] ?? null,
            'status_kepemilikan_kip'    => $row['status_kepemilikan_kip'] ?? null,
            'no_kip'                    => $this->truncate($row['no_kip'] ?? null, 50),
            'pondok_pesantren'          => $row['pondok_pesantren'] ?? null,

            'status'                    => $row['status'] ?? 'aktif',
            'tanggal_masuk'             => $this->parseExcelDate($row['tanggal_masuk'] ?? null),
            'tahun_ajaran_masuk_id'     => $this->tahunAjaranId,
        ]);
    }
}
