<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuruKaryawanSeeder extends Seeder
{
    /**
     * Revisi TU #5: kolom nama sudah digabung dengan gelar depan & belakang
     * dalam satu field, mis. "Drs. H. Ahmad Fauzi, M.Pd.".
     */
    public function run(): void
    {
        DB::table('guru_karyawans')->insert([
            [
                'nip_niy'           => '196804121994031005',
                'nama'              => 'Drs. H. Ahmad Fauzi, M.Pd.',
                'jenis_kelamin'     => 'L',
                'tempat_lahir'      => 'Bogor',
                'tanggal_lahir'     => '1968-04-12',
                'jabatan'           => 'Kepala Sekolah',
                'golongan_pangkat'  => 'Pembina Tk. I / IV.b',
                'alamat'            => 'Jl. Melati No. 12, Cibinong, Bogor',
                'no_hp'             => '081234500001',
                'email'             => 'ahmad.fauzi@smacendekiabangsa.sch.id',
                'status'            => 'aktif',
                'foto'              => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'nip_niy'           => '198002152005012009',
                'nama'              => 'Siti Nurhaliza, S.Pd.',
                'jenis_kelamin'     => 'P',
                'tempat_lahir'      => 'Bandung',
                'tanggal_lahir'     => '1980-02-15',
                'jabatan'           => 'Guru Matematika',
                'golongan_pangkat'  => 'Penata / III.c',
                'alamat'            => 'Jl. Anggrek No. 5, Cibinong, Bogor',
                'no_hp'             => '081234500002',
                'email'             => 'siti.nurhaliza@smacendekiabangsa.sch.id',
                'status'            => 'aktif',
                'foto'              => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'nip_niy'           => '202001234',
                'nama'              => 'Budi Santoso, S.Pd.',
                'jenis_kelamin'     => 'L',
                'tempat_lahir'      => 'Sukabumi',
                'tanggal_lahir'     => '1990-07-20',
                'jabatan'           => 'Guru Bahasa Indonesia',
                'golongan_pangkat'  => '-',
                'alamat'            => 'Jl. Kenanga No. 8, Cibinong, Bogor',
                'no_hp'             => '081234500003',
                'email'             => 'budi.santoso@smacendekiabangsa.sch.id',
                'status'            => 'aktif',
                'foto'              => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'nip_niy'           => '198512102010012011',
                'nama'              => 'Rina Marlina, S.Pd., M.M.',
                'jenis_kelamin'     => 'P',
                'tempat_lahir'      => 'Depok',
                'tanggal_lahir'     => '1985-12-10',
                'jabatan'           => 'Guru Kimia',
                'golongan_pangkat'  => 'Penata Muda Tk. I / III.b',
                'alamat'            => 'Jl. Mawar No. 20, Cibinong, Bogor',
                'no_hp'             => '081234500004',
                'email'             => 'rina.marlina@smacendekiabangsa.sch.id',
                'status'            => 'aktif',
                'foto'              => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'nip_niy'           => '201803456',
                'nama'              => 'Agus Salim, A.Md.',
                'jenis_kelamin'     => 'L',
                'tempat_lahir'      => 'Bogor',
                'tanggal_lahir'     => '1992-03-05',
                'jabatan'           => 'Staff Tata Usaha',
                'golongan_pangkat'  => '-',
                'alamat'            => 'Jl. Dahlia No. 3, Cibinong, Bogor',
                'no_hp'             => '081234500005',
                'email'             => 'agus.salim@smacendekiabangsa.sch.id',
                'status'            => 'aktif',
                'foto'              => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}
