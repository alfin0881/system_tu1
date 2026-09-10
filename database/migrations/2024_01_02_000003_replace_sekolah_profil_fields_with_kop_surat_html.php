<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revisi TU #6 (lanjutan): fitur Profil Sekolah dengan field-field kaku
     * (nama_yayasan, alamat, kelurahan, kecamatan, dst.) dihapus dan diganti
     * dengan SATU kanvas kop surat bebas (`kop_surat_html`) yang bisa
     * didesain sendiri oleh TU — mirip mengedit header di Microsoft Word:
     * bebas atur teks, perataan, ukuran huruf, dan menyisipkan logo.
     *
     * `nama_sekolah` tetap dipertahankan karena dipakai di luar kop surat
     * (judul halaman login, nama aplikasi di sidebar).
     *
     * Data lama dirangkum otomatis menjadi HTML awal di `kop_surat_html`
     * supaya sekolah yang sudah mengisi profil sebelumnya tidak kehilangan
     * datanya — tinggal dirapikan lagi lewat editor bebas yang baru.
     */
    public function up(): void
    {
        Schema::table('sekolah_profils', function (Blueprint $table) {
            $table->longText('kop_surat_html')->nullable()->after('nama_sekolah');
        });

        DB::table('sekolah_profils')->select([
            'id', 'nama_yayasan', 'nama_sekolah', 'npsn', 'alamat', 'kelurahan',
            'kecamatan', 'kabupaten_kota', 'provinsi', 'kode_pos', 'telepon',
            'email', 'website', 'akreditasi',
        ])->orderBy('id')->each(function ($row) {
            $alamatLengkap = collect([
                $row->alamat, $row->kelurahan, $row->kecamatan,
                $row->kabupaten_kota, $row->provinsi, $row->kode_pos,
            ])->filter()->implode(', ');

            $kontak = collect([
                $row->npsn ? "NPSN: {$row->npsn}" : null,
                $row->telepon ? "Telp: {$row->telepon}" : null,
                $row->email ?: null,
                $row->website ?: null,
            ])->filter()->implode(' &middot; ');

            $html = '<div style="text-align:center;">';
            if (! empty($row->nama_yayasan)) {
                $html .= '<p style="font-weight:600;text-transform:uppercase;margin:0;">'.e($row->nama_yayasan).'</p>';
            }
            $html .= '<p style="font-weight:700;font-size:1.25rem;text-transform:uppercase;margin:0;">'.e($row->nama_sekolah).'</p>';
            if ($alamatLengkap) {
                $html .= '<p style="font-size:0.75rem;margin:0;">'.e($alamatLengkap).'</p>';
            }
            if ($kontak) {
                $html .= '<p style="font-size:0.75rem;margin:0;">'.$kontak.'</p>';
            }
            $html .= '</div>';

            DB::table('sekolah_profils')->where('id', $row->id)->update(['kop_surat_html' => $html]);
        });

        Schema::table('sekolah_profils', function (Blueprint $table) {
            $table->dropColumn([
                'nama_yayasan', 'npsn', 'alamat', 'kelurahan', 'kecamatan',
                'kabupaten_kota', 'provinsi', 'kode_pos', 'telepon', 'email',
                'website', 'logo', 'akreditasi', 'nama_kepala_sekolah', 'nip_kepala_sekolah',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('sekolah_profils', function (Blueprint $table) {
            $table->string('nama_yayasan')->nullable();
            $table->string('npsn', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten_kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('akreditasi', 5)->nullable();
            $table->string('nama_kepala_sekolah')->nullable();
            $table->string('nip_kepala_sekolah')->nullable();
            $table->dropColumn('kop_surat_html');
        });
    }
};
