<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\Surat;
use App\Services\SuratTemplateService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SuratController extends Controller
{
    private const FILE_DISK = 'public';

    private const FILE_DIR = 'surat-generated';

    /**
     * Format nomor surat FIX untuk seluruh jenis surat (tidak bisa diubah
     * lewat form Jenis Surat lagi). Contoh hasil: 087/MTs.18/G/L.PM/VIII/2026
     *   {nomor}         -> nomor urut berjalan (087)
     *   MTs.18          -> tetap, tidak berubah
     *   {kode}          -> kode jenis surat, isian per jenis surat (G)
     *   L.PM            -> tetap, tidak berubah
     *   {bulan_romawi}  -> bulan surat dalam angka romawi (VIII)
     *   {tahun}         -> tahun surat (2026)
     */
    public const FORMAT_NOMOR_FIX = '{nomor}/MTs.18/{kode}/L.PM/{bulan_romawi}/{tahun}';

    public function __construct(
        private SuratTemplateService $templateService
    ) {
    }

    /**
     * Nomor urut reset otomatis per jenis surat per tahun (lihat komentar
     * migration surats). TU boleh mengisi nomor_surat secara MANUAL (mis.
     * untuk menyesuaikan dengan nomor surat yang sudah berjalan di buku
     * agenda tata usaha sebelum sistem ini dipakai). Agar nomor urut
     * otomatis berikutnya melanjutkan dari nomor MANUAL terakhir (bukan
     * dari jumlah baris/ID), angka urut diambil dari angka di depan nomor
     * manual tsb (mis. "5" atau "5/DESA/V/2026" -> 5), lalu dibandingkan
     * dengan nomor_urut tertinggi yang sudah ada — mana yang lebih besar
     * itu yang dipakai, supaya nomor otomatis berikutnya = nomor terakhir + 1.
     */
    private function generateNomorSurat(JenisSurat $jenisSurat, Carbon $tanggal, ?string $nomorManual = null): array
    {
        $tahun = $tanggal->year;

        $nomorUrutTerakhir = (int) Surat::where('jenis_surat_id', $jenisSurat->id)
            ->whereYear('tanggal_surat', $tahun)
            ->max('nomor_urut');

        if (! empty($nomorManual)) {
            $nomorUrut = $nomorUrutTerakhir;

            if (preg_match('/^\s*0*(\d+)/', $nomorManual, $match)) {
                $nomorUrut = max($nomorUrutTerakhir, (int) $match[1]);
            }

            return [$nomorUrut, $nomorManual];
        }

        $nomorUrut = $nomorUrutTerakhir + 1;

        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$tanggal->month - 1];

        $nomorSurat = strtr(self::FORMAT_NOMOR_FIX, [
            '{nomor}' => sprintf('%03d', $nomorUrut),
            '{kode}' => $jenisSurat->kode,
            '{bulan_romawi}' => $romawi,
            '{tahun}' => $tahun,
        ]);

        return [$nomorUrut, $nomorSurat];
    }

    /**
     * Gabungkan isian custom dari form dengan beberapa nilai standar (nomor
     * surat, tanggal, perihal) sehingga jika template memakai placeholder
     * dengan nama tsb (mis. ${nomor_surat}, ${tanggal_surat}), nilainya
     * otomatis terisi tanpa perlu diketik ulang oleh pengguna.
     */
    private function mergedDataForTemplate(Surat $surat, array $dataIsian): array
    {
        return array_merge($dataIsian, [
            'nomor_surat' => $surat->nomor_surat,
            'tanggal_surat' => $surat->tanggal_surat->translatedFormat('d F Y'),
            'perihal' => $surat->perihal,
        ]);
    }

    /**
     * Generate ulang file docx surat dari template jenis suratnya, lalu simpan
     * path-nya. Halaman "Cetak Langsung" merender docx ini langsung di
     * browser lewat docx-preview (lihat resources/js/print-surat.js) — murni
     * client-side, tanpa proses konversi apa pun di server (tidak butuh
     * LibreOffice/Microsoft Word), supaya siap dipakai di lingkungan online
     * mana pun tanpa dependensi software tambahan di server.
     */
    private function generateFile(Surat $surat): void
    {
        $jenisSurat = $surat->jenisSurat;

        $templateAbsolutePath = Storage::disk('public')->path($jenisSurat->template_path);
        $basename = $surat->id.'-'.\Illuminate\Support\Str::slug($surat->nomor_surat);
        $filename = self::FILE_DIR.'/'.$basename.'.docx';
        $outputAbsolutePath = Storage::disk(self::FILE_DISK)->path($filename);

        Storage::disk(self::FILE_DISK)->makeDirectory(self::FILE_DIR);

        $this->templateService->generate(
            $templateAbsolutePath,
            $this->mergedDataForTemplate($surat, $surat->data_isian ?? []),
            $outputAbsolutePath
        );

        if ($surat->file_path && $surat->file_path !== $filename) {
            Storage::disk(self::FILE_DISK)->delete($surat->file_path);
        }

        $surat->update(['file_path' => $filename]);
    }

    public function index(Request $request): View
    {
        $surat = Surat::with(['jenisSurat'])
            ->when($request->filled('jenis_surat_id'), fn ($q) => $q->where('jenis_surat_id', $request->integer('jenis_surat_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(fn ($q2) => $q2->where('perihal', 'like', "%{$search}%")
                    ->orWhere('nomor_surat', 'like', "%{$search}%"));
            })
            ->orderByDesc('tanggal_surat')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $jenisSuratOptions = JenisSurat::orderBy('nama')->get();

        return view('surat.index', compact('surat', 'jenisSuratOptions'));
    }

    public function create(Request $request): View
    {
        $jenisSuratId = $request->integer('jenis_surat_id') ?: null;
        $jenisSurat = $jenisSuratId ? JenisSurat::aktif()->find($jenisSuratId) : null;

        $jenisSuratOptions = JenisSurat::aktif()->orderBy('kategori')->orderBy('nama')->get()->groupBy('kategori');
        $placeholders = $jenisSurat->template_variables ?? [];

        return view('surat.create', compact('jenisSurat', 'jenisSuratOptions', 'placeholders'));
    }

    public function store(Request $request): RedirectResponse
    {
        $jenisSurat = JenisSurat::aktif()->findOrFail($request->integer('jenis_surat_id'));

        $data = $request->validate([
            'tanggal_surat' => ['required', 'date'],
            'perihal' => ['required', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
            'data.*' => ['nullable', 'string'],
            'nomor_surat_manual' => ['nullable', 'string', 'max:255', Rule::unique('surats', 'nomor_surat')],
        ]);

        $tanggal = Carbon::parse($data['tanggal_surat']);
        [$nomorUrut, $nomorSurat] = $this->generateNomorSurat($jenisSurat, $tanggal, $data['nomor_surat_manual'] ?? null);

        try {
            $surat = DB::transaction(function () use ($jenisSurat, $data, $nomorUrut, $nomorSurat, $tanggal) {
                return Surat::create([
                    'jenis_surat_id' => $jenisSurat->id,
                    'nomor_surat' => $nomorSurat,
                    'nomor_urut' => $nomorUrut,
                    'tanggal_surat' => $tanggal,
                    'perihal' => $data['perihal'],
                    'data_isian' => $data['data'] ?? [],
                    'status' => 'draft',
                    'created_by' => Auth::id(),
                ]);
            });
        } catch (QueryException $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan surat, kemungkinan nomor surat bentrok karena diproses bersamaan. Silakan coba lagi.');
        }

        try {
            $this->generateFile($surat);
        } catch (\Throwable $e) {
            return redirect()->route('surat.show', $surat)->with('error', 'Surat berhasil dibuat sebagai draft, tetapi file docx gagal dibuat otomatis ('.$e->getMessage().'). Coba simpan ulang lewat halaman Ubah.');
        }

        return redirect()->route('surat.show', $surat)->with('success', "Surat berhasil dibuat sebagai draft dengan nomor {$surat->nomor_surat}.");
    }

    public function show(Surat $surat): View
    {
        $surat->load(['jenisSurat', 'dibuatOleh']);

        return view('surat.show', compact('surat'));
    }

    public function edit(Surat $surat): View
    {
        abort_unless($surat->status === 'draft', 403, 'Surat yang sudah final tidak dapat diubah.');

        $surat->load('jenisSurat');
        $placeholders = $surat->jenisSurat->template_variables ?? [];

        return view('surat.edit', compact('surat', 'placeholders'));
    }

    public function update(Request $request, Surat $surat): RedirectResponse
    {
        abort_unless($surat->status === 'draft', 403, 'Surat yang sudah final tidak dapat diubah.');

        $data = $request->validate([
            'nomor_surat' => ['required', 'string', 'max:255', Rule::unique('surats', 'nomor_surat')->ignore($surat->id)],
            'tanggal_surat' => ['required', 'date'],
            'perihal' => ['required', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
            'data.*' => ['nullable', 'string'],
        ]);

        // Jika nomor_surat diubah manual di sini, selaraskan juga nomor_urut
        // (angka di depan nomor_surat) supaya nomor otomatis berikutnya
        // tetap melanjutkan dari nomor terakhir ini, bukan dari ID/jumlah baris.
        $nomorUrut = $surat->nomor_urut;
        if (preg_match('/^\s*0*(\d+)/', $data['nomor_surat'], $match)) {
            $nomorUrut = (int) $match[1];
        }

        $surat->update([
            'nomor_surat' => $data['nomor_surat'],
            'nomor_urut' => $nomorUrut,
            'tanggal_surat' => $data['tanggal_surat'],
            'perihal' => $data['perihal'],
            'data_isian' => $data['data'] ?? [],
        ]);

        try {
            $this->generateFile($surat->fresh());
        } catch (\Throwable $e) {
            return redirect()->route('surat.show', $surat)->with('error', 'Data surat tersimpan, tetapi file docx gagal dibuat ulang ('.$e->getMessage().').');
        }

        return redirect()->route('surat.show', $surat)->with('success', 'Surat berhasil diperbarui.');
    }

    public function destroy(Surat $surat): RedirectResponse
    {
        abort_unless($surat->status === 'draft', 403, 'Surat yang sudah final tidak dapat dihapus, demi menjaga keutuhan nomor urut surat.');

        if ($surat->file_path) {
            Storage::disk(self::FILE_DISK)->delete($surat->file_path);
        }

        $nomor = $surat->nomor_surat;
        $surat->delete();

        return redirect()->route('surat.index')->with('success', "Draft surat {$nomor} berhasil dihapus.");
    }

    /** Unduh hasil surat (docx) yang sudah otomatis tersusun dari template jenis suratnya. */
    public function cetak(Surat $surat): StreamedResponse
    {
        abort_unless($surat->file_path && Storage::disk(self::FILE_DISK)->exists($surat->file_path), 404, 'File surat belum tersedia. Coba buka halaman Ubah lalu simpan ulang.');

        $downloadName = $surat->nomor_surat ? \Illuminate\Support\Str::slug($surat->nomor_surat).'.docx' : 'surat.docx';

        return Storage::disk(self::FILE_DISK)->download($surat->file_path, $downloadName);
    }

    /**
     * Halaman "Cetak Langsung". Docx surat dirender langsung di browser
     * lewat docx-preview (client-side, lihat resources/js/print-surat.js
     * & resources/js/docx-vml-fix.js + docx-anchor-fix.js untuk
     * perbaikan-perbaikan yang sudah diterapkan) — tidak ada proses
     * konversi di server, tidak butuh LibreOffice/Microsoft Word.
     */
    public function cetakLangsung(Surat $surat): View
    {
        abort_unless($surat->file_path && Storage::disk(self::FILE_DISK)->exists($surat->file_path), 404, 'File surat belum tersedia. Coba buka halaman Ubah lalu simpan ulang.');

        return view('surat.print', [
            'surat' => $surat,
            'docxUrl' => route('surat.cetak', $surat),
        ]);
    }

    public function finalisasi(Surat $surat): RedirectResponse
    {
        abort_unless($surat->status === 'draft', 403, 'Surat ini sudah final.');

        $surat->update(['status' => 'final']);

        return redirect()->route('surat.show', $surat)->with('success', 'Surat berhasil difinalisasi. Nomor surat terkunci dan surat tidak dapat diubah/dihapus lagi.');
    }
}