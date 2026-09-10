<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Services\SuratTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JenisSuratController extends Controller
{
    private const TEMPLATE_DISK = 'public';

    private const TEMPLATE_DIR = 'surat-templates';

    public function __construct(private SuratTemplateService $templateService)
    {
    }

    public function index(Request $request): View
    {
        $jenisSurat = JenisSurat::withCount('surat')
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->string('kategori')))
            ->orderBy('kategori')
            ->orderBy('nama')
            ->get();

        return view('jenis-surat.index', compact('jenisSurat'));
    }

    public function create(): View
    {
        return view('jenis-surat.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null, wajibTemplate: true);
        $data['aktif'] = $request->boolean('aktif');
        $data['format_nomor'] = SuratController::FORMAT_NOMOR_FIX;

        $this->applyTemplateUpload($request, $data);

        JenisSurat::create($data);

        return redirect()->route('jenis-surat.index')->with('success', 'Jenis surat berhasil ditambahkan.');
    }

    public function edit(JenisSurat $jenisSurat): View
    {
        return view('jenis-surat.edit', compact('jenisSurat'));
    }

    public function update(Request $request, JenisSurat $jenisSurat): RedirectResponse
    {
        $data = $this->validated($request, $jenisSurat->id, wajibTemplate: ! $jenisSurat->hasTemplate());
        $data['aktif'] = $request->boolean('aktif');
        $data['format_nomor'] = SuratController::FORMAT_NOMOR_FIX;

        $this->applyTemplateUpload($request, $data, $jenisSurat);

        $jenisSurat->update($data);

        return redirect()->route('jenis-surat.index')->with('success', 'Jenis surat berhasil diperbarui.');
    }

    public function destroy(JenisSurat $jenisSurat): RedirectResponse
    {
        if ($jenisSurat->surat()->exists()) {
            return back()->with('error', "Jenis surat \"{$jenisSurat->nama}\" tidak dapat dihapus karena sudah dipakai pada {$jenisSurat->surat()->count()} surat. Nonaktifkan saja jika sudah tidak dipakai.");
        }

        if ($jenisSurat->template_path) {
            Storage::disk(self::TEMPLATE_DISK)->delete($jenisSurat->template_path);
        }

        $nama = $jenisSurat->nama;
        $jenisSurat->delete();

        return redirect()->route('jenis-surat.index')->with('success', "Jenis surat {$nama} berhasil dihapus.");
    }

    private function validated(Request $request, ?int $ignoreId, bool $wajibTemplate): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9.\-]+$/', Rule::unique('jenis_surats')->ignore($ignoreId)],
            'kategori' => ['required', Rule::in(['keterangan', 'keputusan', 'undangan', 'tugas', 'sppd', 'lainnya'])],
            'template' => [$wajibTemplate ? 'required' : 'nullable', 'file', 'mimes:docx', 'max:10240'],
            'deskripsi' => ['nullable', 'string'],
        ], [
            'kode.regex' => 'Kode hanya boleh huruf kapital, angka, titik, dan tanda hubung (contoh: SKET-AKTIF).',
            'template.required' => 'Silakan upload file template surat (.docx).',
            'template.mimes' => 'Template surat harus berupa file Word (.docx).',
        ]);
    }

    /**
     * Jika ada file template baru diupload: simpan filenya, ekstrak daftar
     * placeholder ${...} di dalamnya, lalu masukkan ke $data yang akan
     * disimpan ke model. Template lama (jika ada, saat mode ubah) dihapus
     * setelah file baru berhasil disimpan.
     */
    private function applyTemplateUpload(Request $request, array &$data, ?JenisSurat $jenisSurat = null): void
    {
        unset($data['template']);

        if (! $request->hasFile('template')) {
            return;
        }

        $file = $request->file('template');
        $path = $file->store(self::TEMPLATE_DIR, self::TEMPLATE_DISK);
        $absolutePath = Storage::disk(self::TEMPLATE_DISK)->path($path);

        try {
    $variables = $this->templateService->extractPlaceholders($absolutePath);
} catch (\Throwable $e) {
    Storage::disk(self::TEMPLATE_DISK)->delete($path);

    throw ValidationException::withMessages([
        'template' => 'File docx tidak dapat dibaca. Pastikan file tidak rusak dan bukan hasil scan/gambar.',
    ]);
}

        if (empty($variables)) {
            Storage::disk(self::TEMPLATE_DISK)->delete($path);

            throw ValidationException::withMessages([
                'template' => 'Template tidak mengandung placeholder ${...}. Tambahkan minimal satu placeholder (mis. ${nama}) di file docx sebelum diupload.',
            ]);
        }

        if ($jenisSurat && $jenisSurat->template_path) {
            Storage::disk(self::TEMPLATE_DISK)->delete($jenisSurat->template_path);
        }

        $data['template_path'] = $path;
        $data['template_nama_asli'] = $file->getClientOriginalName();
        $data['template_variables'] = $variables;
    }
}
