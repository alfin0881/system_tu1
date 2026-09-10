<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Convert file docx (hasil generate surat) menjadi PDF secara otomatis,
 * TIDAK butuh Docker/Gotenberg — cukup dijalankan langsung di Laragon/XAMPP
 * ataupun VPS biasa.
 *
 * Strategi convert (dicoba berurutan):
 * 1) Microsoft Word yang SUDAH TERPASANG di komputer (lewat COM automation
 *    Windows) — cara PALING GAMPANG kalau di komputer/server-nya sudah ada
 *    Office, karena tidak perlu install apa pun lagi, dan hasilnya PASTI
 *    identik 100% dengan tampilan aslinya di Word (paling presisi dari
 *    semua opsi, karena Word yang convert file-nya sendiri). Cuma butuh 1x
 *    aktifkan ekstensi `com_dotnet` di php.ini (lihat catatan di bawah).
 * 2) LibreOffice yang terpasang LANGSUNG di OS (bukan lewat Docker), lewat
 *    perintah `soffice --headless --convert-to pdf`. Dipakai kalau Word
 *    tidak ada. Hasilnya juga presisi (kop surat/gambar, header/footer,
 *    tabel, margin tetap sama), tapi perlu install LibreOffice dulu.
 * 3) Kalau keduanya tidak ada/gagal, fallback ke PHPWord + Dompdf (murni
 *    PHP, tanpa dependency luar). Selalu tersedia tapi me-render ulang docx
 *    ke HTML dulu sehingga untuk layout kompleks (kop surat berupa gambar,
 *    tabel presisi, dsb) hasilnya bisa terlihat berbeda dari template asli.
 *
 * Cara pakai Microsoft Word (SEKALI SAJA, tanpa install apa pun kalau Word
 * sudah ada di komputer ini):
 * - Buka php.ini yang dipakai (Laragon: cek lewat `php --ini`), cari baris
 *   `;extension=com_dotnet`, hapus tanda `;` di depannya jadi
 *   `extension=com_dotnet`, lalu restart Apache/PHP-nya.
 * - Selesai — tidak ada langkah lain, tidak perlu Docker/LibreOffice.
 *
 * Cara pasang LibreOffice (opsional, kalau Word TIDAK ada):
 * - Windows (Laragon/XAMPP): download & install dari libreoffice.org,
 *   lalu (kalau tidak otomatis kedetek) set di .env:
 *   LIBREOFFICE_PATH="C:\Program Files\LibreOffice\program\soffice.exe"
 * - Linux/VPS: sudo apt install libreoffice (atau libreoffice-writer saja).
 */
class DocxToPdfConverter
{
    public function __construct()
    {
        Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
        Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));
    }

    /**
     * Saklar untuk MEMATIKAN SAMA SEKALI percobaan Microsoft Word (COM) &
     * LibreOffice, tanpa perlu utak-atik kode — berguna kalau:
     * - Server production nanti (hosting/cPanel biasa) memang TIDAK akan
     *   pernah punya LibreOffice/Word terpasang, jadi lebih baik langsung
     *   pakai jalur cetak "docx-preview" di semua lingkungan (lokal MAUPUN
     *   production) supaya perilakunya konsisten & gampang ditest — tidak
     *   ada kejutan "di laptop saya jalan beda dengan di server".
     * - LibreOffice/Word di komputer lagi rusak/error (mis. pesan
     *   "The application cannot be started... bootstrap.ini is corrupt")
     *   — daripada nunggu proses gagal/timeout dulu (bisa sampai 2 menit
     *   per percobaan cetak), matikan saja lewat saklar ini.
     *
     * Set di .env: SURAT_GUNAKAN_KONVERSI_OFFICE=false
     * (default: true — tidak mengubah perilaku lama kalau tidak di-set).
     */
    private function officeConverterDiizinkan(): bool
    {
        return filter_var(config('services.surat_pdf.gunakan_konversi_office', true), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Convert $docxAbsolutePath menjadi PDF, disimpan sebagai $outputAbsolutePath.
     *
     * @param  bool  $izinkanFallbackKurangAkurat  Kalau Word COM & LibreOffice
     *         dua-duanya tidak ada/gagal, method ini BOLEH atau TIDAK BOLEH
     *         lanjut ke fallback PHPWord+Dompdf (murni PHP, tapi hasilnya
     *         terbukti bisa jauh berbeda dari template asli — mis. gambar
     *         kop surat bisa membesar tidak proporsional, garis/tabel
     *         presisi bisa hilang). Set FALSE ketika hasil dipakai sebagai
     *         pratinjau/cetak resmi yang HARUS identik dengan template
     *         (dipakai oleh SuratController) — kalau Word & LibreOffice
     *         gagal, method ini akan gagal juga (throw) daripada diam-diam
     *         menghasilkan PDF yang tampilannya berbeda dari aslinya.
     *
     * @throws RuntimeException jika semua metode convert yang diizinkan gagal.
     */
    public function convert(string $docxAbsolutePath, string $outputAbsolutePath, bool $izinkanFallbackKurangAkurat = true): void
    {
        if (! is_file($docxAbsolutePath)) {
            throw new RuntimeException("File docx tidak ditemukan: {$docxAbsolutePath}");
        }

        $outputDirectory = dirname($outputAbsolutePath);
        if (! is_dir($outputDirectory)) {
            @mkdir($outputDirectory, 0755, true);
        }

        if (! $this->officeConverterDiizinkan()) {
            Log::info('Convert docx surat ke PDF: Word/LibreOffice sengaja dimatikan lewat SURAT_GUNAKAN_KONVERSI_OFFICE=false di .env, langsung pakai jalur cadangan.', ['docx' => $docxAbsolutePath]);

            if (! $izinkanFallbackKurangAkurat) {
                throw new RuntimeException(
                    'Convert ke PDF dilewati: SURAT_GUNAKAN_KONVERSI_OFFICE=false di .env (Word/LibreOffice '
                    .'sengaja tidak dipakai). Sistem akan pakai pratinjau docx-preview di browser.'
                );
            }

            $this->convertViaPhpWord($docxAbsolutePath, $outputAbsolutePath);
            Log::info('Convert docx surat ke PDF: berhasil pakai fallback PHPWord+Dompdf (hasil bisa berbeda dari template).', ['docx' => $docxAbsolutePath]);

            return;
        }

        try {
            $this->convertViaWordCom($docxAbsolutePath, $outputAbsolutePath);
            Log::info('Convert docx surat ke PDF: berhasil pakai Microsoft Word (COM).', ['docx' => $docxAbsolutePath]);

            return;
        } catch (\Throwable $e) {
            Log::warning('Convert docx surat ke PDF via Microsoft Word gagal, coba LibreOffice: '.$e->getMessage(), ['docx' => $docxAbsolutePath]);
        }

        try {
            $this->convertViaLibreOffice($docxAbsolutePath, $outputAbsolutePath);
            Log::info('Convert docx surat ke PDF: berhasil pakai LibreOffice.', ['docx' => $docxAbsolutePath]);

            return;
        } catch (\Throwable $e) {
            Log::warning('Convert docx surat ke PDF via LibreOffice gagal'.($izinkanFallbackKurangAkurat ? ', pakai fallback PHPWord+Dompdf: ' : ' (fallback PHPWord+Dompdf tidak diizinkan untuk pemakaian ini): ').$e->getMessage(), ['docx' => $docxAbsolutePath]);

            if (! $izinkanFallbackKurangAkurat) {
                throw new RuntimeException(
                    'Convert ke PDF gagal: Microsoft Word maupun LibreOffice tidak tersedia/gagal di server ini. '
                    .'Fallback PHPWord+Dompdf sengaja tidak dipakai untuk pratinjau resmi karena hasilnya bisa '
                    .'berbeda dari template (mis. ukuran gambar kop surat jadi tidak proporsional).',
                    previous: $e
                );
            }
        }

        $this->convertViaPhpWord($docxAbsolutePath, $outputAbsolutePath);
        Log::info('Convert docx surat ke PDF: berhasil pakai fallback PHPWord+Dompdf (hasil bisa berbeda dari template).', ['docx' => $docxAbsolutePath]);
    }

    /**
     * Convert pakai Microsoft Word yang sudah terpasang di komputer, lewat
     * COM automation Windows (Word yang buka file docx-nya sendiri lalu
     * "Save As PDF") — paling presisi karena bukan hasil render ulang oleh
     * library lain, melainkan Word asli. Hanya bisa jalan di Windows dan
     * butuh ekstensi PHP `com_dotnet` aktif (lihat catatan di atas class).
     */
    private function convertViaWordCom(string $docxAbsolutePath, string $outputAbsolutePath): void
    {
        if (! (PHP_OS_FAMILY === 'Windows' && class_exists(\COM::class))) {
            throw new RuntimeException('Microsoft Word (COM) cuma bisa dipakai di Windows dengan ekstensi com_dotnet aktif.');
        }

        $word = null;
        $document = null;

        try {
            $word = new \COM('Word.Application');
            $word->Visible = false;
            $word->DisplayAlerts = 0;

            $document = $word->Documents->Open($docxAbsolutePath, false, true);
            // wdFormatPDF = 17
            $document->SaveAs($outputAbsolutePath, 17);
            $document->Close(false);
        } catch (\Throwable $e) {
            throw new RuntimeException('Convert via Microsoft Word gagal: '.$e->getMessage(), previous: $e);
        } finally {
            $document = null;

            if ($word) {
                try {
                    $word->Quit(false);
                } catch (\Throwable) {
                    // Abaikan — yang penting proses Word tidak nge-hang.
                }
            }
            $word = null;
        }

        if (! is_file($outputAbsolutePath)) {
            throw new RuntimeException('Convert via Microsoft Word selesai tetapi file PDF tidak ditemukan.');
        }
    }

    /** Cari lokasi binary LibreOffice yang terpasang LANGSUNG di OS (bukan Docker). */
    private function findLibreOfficeBinary(): ?string
    {
        $configured = env('LIBREOFFICE_PATH');
        if ($configured && is_file($configured)) {
            return $configured;
        }

        // Lokasi instalasi default di Windows, jaga-jaga kalau belum ada di PATH.
        $commonWindowsPaths = [
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
        ];
        foreach ($commonWindowsPaths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return (new ExecutableFinder)->find('soffice') ?? (new ExecutableFinder)->find('libreoffice');
    }

    /**
     * Convert pakai LibreOffice headless (hasil presisi, sama seperti file docx aslinya).
     *
     * PENTING: LibreOffice butuh folder "profil user" yang BISA DITULIS untuk
     * bisa jalan sama sekali. Kalau dijalankan lewat web server (Apache/Nginx
     * dengan user www-data, atau IIS/Laragon), user tsb biasanya TIDAK punya
     * folder HOME yang bisa ditulis — akibatnya soffice gagal start dengan
     * error "User installation could not be completed" (exit code 77),
     * walaupun kalau dites manual lewat terminal (sebagai user biasa/admin
     * yang HOME-nya jelas) hasilnya sukses. Ini penyebab paling umum fitur
     * cetak "kelihatannya sudah benar tapi tetap gagal" di server asli.
     *
     * Makanya di sini kita paksa LibreOffice pakai folder profil sendiri
     * yang PASTI ada & bisa ditulis (di dalam storage/app milik Laravel,
     * yang memang wajib writable), unik per proses convert supaya beberapa
     * surat yang dicetak bersamaan tidak rebutan/nge-lock folder yang sama.
     */
    private function convertViaLibreOffice(string $docxAbsolutePath, string $outputAbsolutePath): void
    {
        $binary = $this->findLibreOfficeBinary();

        if (! $binary) {
            throw new RuntimeException('Binary LibreOffice (soffice) tidak ditemukan di server ini.');
        }

        $outputDirectory = dirname($outputAbsolutePath);

        // LibreOffice menamai file hasil sesuai basename docx-nya sendiri di
        // dalam --outdir, jadi konversi dilakukan ke folder sementara supaya
        // tidak bentrok nama lalu dipindah/di-rename ke $outputAbsolutePath.
        $tempDir = $outputDirectory.DIRECTORY_SEPARATOR.'tmp-'.uniqid();
        @mkdir($tempDir, 0755, true);

        // Folder profil LibreOffice yang unik & writable untuk proses ini
        // saja (lihat penjelasan panjang di atas method). Ditaruh di dalam
        // storage/app/libreoffice-profile (bukan di system temp), supaya
        // pasti berada di disk yang sama yang izinnya sudah diatur untuk
        // Laravel (storage wajib writable oleh user web server).
        $profileDir = storage_path('app/libreoffice-profile/profile-'.uniqid());
        @mkdir($profileDir, 0755, true);

        try {
            $process = new Process([
                $binary,
                '--headless',
                '--invisible',
                '--nologo',
                '--nofirststartwizard',
                '--norestore',
                '-env:UserInstallation=file://'.str_replace('\\', '/', $profileDir),
                '--convert-to', 'pdf',
                '--outdir', $tempDir,
                $docxAbsolutePath,
            ]);
            // HOME/USERPROFILE dipaksa ke folder yang writable juga, sebagai
            // jaga-jaga tambahan di luar -env:UserInstallation (beberapa
            // versi LibreOffice masih coba baca $HOME untuk hal lain, mis.
            // folder cache dconf di Linux).
            $process->setEnv([
                'HOME' => $profileDir,
                'USERPROFILE' => $profileDir,
            ]);
            $process->setTimeout(120);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException(
                    'Perintah LibreOffice gagal (exit code '.$process->getExitCode().'): '
                    .trim($process->getErrorOutput().' '.$process->getOutput())
                );
            }

            $generated = $tempDir.DIRECTORY_SEPARATOR.pathinfo($docxAbsolutePath, PATHINFO_FILENAME).'.pdf';

            if (! is_file($generated)) {
                throw new RuntimeException('LibreOffice selesai tetapi file PDF hasilnya tidak ditemukan. Output: '.trim($process->getOutput().' '.$process->getErrorOutput()));
            }

            rename($generated, $outputAbsolutePath);
        } finally {
            @array_map('unlink', glob($tempDir.DIRECTORY_SEPARATOR.'*') ?: []);
            @rmdir($tempDir);

            $this->deleteDirectoryRecursively($profileDir);
        }
    }

    /** Hapus folder profil LibreOffice sementara (rekursif) supaya tidak menumpuk di server. */
    private function deleteDirectoryRecursively(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($directory);
    }

    /**
     * Fallback convert murni pakai PHP (PHPWord + Dompdf), tanpa LibreOffice
     * ataupun Docker. Selalu tersedia, tapi PHPWord me-render ULANG isi
     * dokumen ke HTML lalu ke PDF (bukan "mencetak" file docx aslinya
     * persis), jadi untuk layout yang presisi/kompleks hasilnya bisa
     * sedikit berbeda dari tampilan aslinya di template.
     */
    private function convertViaPhpWord(string $docxAbsolutePath, string $outputAbsolutePath): void
    {
        if (! is_dir(base_path('vendor/dompdf/dompdf'))) {
            throw new RuntimeException(
                'Package dompdf/dompdf belum terpasang. Jalankan "composer require dompdf/dompdf" lalu coba lagi.'
            );
        }

        try {
            $phpWord = IOFactory::load($docxAbsolutePath, 'Word2007');
            $writer = IOFactory::createWriter($phpWord, 'PDF');
            $writer->save($outputAbsolutePath);
        } catch (\Throwable $e) {
            throw new RuntimeException('Convert ke PDF gagal: '.$e->getMessage(), previous: $e);
        }

        if (! is_file($outputAbsolutePath)) {
            throw new RuntimeException('Convert ke PDF selesai tetapi file PDF hasilnya tidak ditemukan.');
        }
    }
}