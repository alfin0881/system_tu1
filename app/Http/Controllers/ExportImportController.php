<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GuruKaryawan;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ExportImportController extends Controller
{
    // Menampilkan Halaman Khusus Export & Import
    public function index()
    {
        return view('export_import.index');
    }

    /**
     * Generic Export ke CSV
     */
    private function exportCsv($model, $filename)
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $data = $model::all();
        
        $callback = function() use($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM for UTF-8 Excel
            
            if($data->count() > 0) {
                fputcsv($file, array_keys($data->first()->toArray()), ';');
                foreach ($data as $row) {
                    fputcsv($file, $row->toArray(), ';');
                }
            } else {
                fputcsv($file, ['Data Kosong'], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generic Import dari CSV
     */
    private function importCsv($model, $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $fileHandle = fopen($file->getPathname(), 'r');
        
        $headerRaw = fgetcsv($fileHandle, 0, ';');
        $header = array_map(function($val) {
            return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $val);
        }, $headerRaw);
        
        DB::beginTransaction();
        try {
            $count = 0;
            while (($row = fgetcsv($fileHandle, 0, ';')) !== false) {
                if(empty(array_filter($row))) continue;
                
                if(count($header) == count($row)) {
                    $data = array_combine($header, $row);
                    $id = $data['id'] ?? null;
                    unset($data['id'], $data['created_at'], $data['updated_at']);
                    
                    if ($id) {
                        $model::updateOrCreate(['id' => $id], $data);
                    } else {
                        $model::create($data);
                    }
                    $count++;
                }
            }
            DB::commit();
            fclose($fileHandle);
            return back()->with('success', "Berhasil mengimpor $count data.");
        } catch (\Exception $e) {
            DB::rollback();
            fclose($fileHandle);
            return back()->with('error', 'Gagal mengimpor data. Pastikan format CSV sesuai (Pemisah titik koma/semicolon). Error: ' . $e->getMessage());
        }
    }

    // ================= DATA INDUK =================
    public function exportDataInduk() { return $this->exportCsv(Siswa::class, 'data_induk.csv'); }
    public function importDataInduk(Request $request) { return $this->importCsv(Siswa::class, $request); }

    // ================= GURU KARYAWAN =================
    public function exportGuruKaryawan() { return $this->exportCsv(GuruKaryawan::class, 'guru_karyawan.csv'); }
    public function importGuruKaryawan(Request $request) { return $this->importCsv(GuruKaryawan::class, $request); }

    // ================= KELAS =================
    public function exportKelas() { return $this->exportCsv(Kelas::class, 'data_kelas.csv'); }

    // ================= SISWA =================
    public function exportSiswa() { return $this->exportCsv(Siswa::class, 'data_siswa.csv'); }
}
