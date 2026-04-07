<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ScanlogUploadController extends Controller
{
    /**
     * Halaman upload PDF CamScanner
     */
    public function index()
    {
        return view('admin.scanlog-upload');
    }

    /**
     * Health check endpoint — cek apakah Python & Tesseract tersedia
     */
    public function debugInfo()
    {
        $pythonBin = $this->getPythonBin();

        $pythonVersion    = shell_exec("{$pythonBin} --version 2>&1") ?? 'tidak ditemukan';
        $tesseractVersion = shell_exec('tesseract --version 2>&1') ?? 'tidak ditemukan';
        $poppler          = shell_exec('which pdftoppm 2>&1') ?? 'tidak ditemukan';
        $scriptPath       = base_path('ocr_service/extract_scanlog.py');

        return response()->json([
            'python_bin'         => $pythonBin,
            'python_version'     => trim($pythonVersion),
            'tesseract_version'  => substr(trim($tesseractVersion), 0, 100),
            'poppler'            => trim($poppler),
            'script_exists'      => file_exists($scriptPath),
            'script_path'        => $scriptPath,
            'storage_writable'   => is_writable(storage_path('app')),
            'exec_enabled'       => function_exists('exec'),
            'shell_exec_enabled' => function_exists('shell_exec'),
        ]);
    }

    /**
     * Debug OCR: upload PDF dan kembalikan raw text hasil OCR/pdftotext
     */
    public function debugOcr(Request $request)
    {
        $request->validate(['pdf_file' => 'required|file|mimes:pdf|max:20480']);

        $fileName = 'debug_' . now()->format('YmdHis') . '.pdf';
        $pdfPath  = $request->file('pdf_file')->storeAs('scanlog_uploads', $fileName);
        $fullPath = storage_path('app/' . $pdfPath);

        $pythonScript = base_path('ocr_service/extract_scanlog.py');
        $pythonBin    = $this->getPythonBin();
        $escapedPdf   = escapeshellarg($fullPath);
        $escapedScript = escapeshellarg($pythonScript);

        $command   = "{$pythonBin} {$escapedScript} {$escapedPdf} --debug 2>&1";
        $rawOutput = shell_exec($command) ?? '';

        // Try to parse JSON from output
        if (preg_match('/(\{.*\})/s', $rawOutput, $m)) {
            $data = json_decode($m[1], true);
            if ($data) return response()->json($data);
        }

        return response()->json(['raw_output' => substr($rawOutput, 0, 3000)]);
    }

    /**
     * Proses upload PDF dan jalankan OCR via Python
     */
    public function upload(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:20480',
        ], [
            'pdf_file.required' => 'File PDF wajib diupload.',
            'pdf_file.mimes'    => 'File harus berformat PDF.',
            'pdf_file.max'      => 'Ukuran file maksimal 20MB.',
        ]);

        // Simpan PDF ke storage
        $fileName = 'scanlog_' . now()->format('YmdHis') . '_' . Str::random(6) . '.pdf';
        $pdfPath  = $request->file('pdf_file')->storeAs('scanlog_uploads', $fileName);
        $fullPath = storage_path('app/' . $pdfPath);

        // Path ke Python script
        $pythonScript = base_path('ocr_service/extract_scanlog.py');
        $pythonBin    = $this->getPythonBin();

        if (!file_exists($pythonScript)) {
            return response()->json([
                'success' => false,
                'message' => 'Script OCR tidak ditemukan di server. Pastikan deployment sudah selesai.',
            ], 500);
        }

        // Jalankan OCR Python script
        $escapedPdf    = escapeshellarg($fullPath);
        $escapedScript = escapeshellarg($pythonScript);
        $command       = "{$pythonBin} {$escapedScript} {$escapedPdf} 2>&1";

        $rawOutput = shell_exec($command) ?? '';

        Log::info('[Scanlog OCR] Command: ' . $command);
        Log::info('[Scanlog OCR] Output: ' . substr($rawOutput, 0, 500));

        // Parse hasil JSON dari Python
        $employees = null;
        if (preg_match('/(\[.*\])/s', $rawOutput, $m)) {
            $employees = json_decode($m[1], true);
        }
        if (!$employees && preg_match('/(\{"error".*?\})/s', $rawOutput, $m2)) {
            $decoded = json_decode($m2[1], true);
            return response()->json([
                'success' => false,
                'message' => 'OCR Error: ' . ($decoded['error'] ?? 'Unknown error'),
                'debug'   => ['raw_output' => substr($rawOutput, 0, 500)],
            ], 422);
        }

        if (empty($employees)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang berhasil dibaca. Info: ' . substr(preg_replace('/\s+/', ' ', strip_tags($rawOutput)), 0, 250),
                'debug'   => ['raw_output' => substr($rawOutput, 0, 1000)],
            ], 422);
        }

        return response()->json([
            'success'   => true,
            'employees' => $employees,
            'message'   => count($employees) . ' karyawan berhasil dibaca dari PDF.',
        ]);
    }

    /**
     * Generate dan download file Excel Lembur Harian
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2099',
            'data'  => 'required|string',
        ]);

        $bulan    = $request->input('bulan');
        $tahun    = $request->input('tahun');
        $jsonData = $request->input('data');

        $employees = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($employees)) {
            return response()->json(['error' => 'Data tidak valid.'], 422);
        }

        $templatePath = base_path('TEMPLATE/2026 LEMBUR HARIAN PT G2B(REKAP MARET).xlsx');
        $outputDir    = storage_path('app/scanlog_exports');
        $outputFile   = "Lembur_Harian_{$tahun}_" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '_' . now()->format('His') . '.xlsx';
        $outputPath   = $outputDir . '/' . $outputFile;

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $pythonScript    = base_path('ocr_service/generate_excel.py');
        $pythonBin       = $this->getPythonBin();
        $escapedJson     = escapeshellarg($jsonData);
        $escapedScript   = escapeshellarg($pythonScript);
        $escapedTemplate = escapeshellarg($templatePath);
        $escapedOutput   = escapeshellarg($outputPath);

        $command   = "{$pythonBin} {$escapedScript} {$escapedJson} {$escapedTemplate} {$escapedOutput} {$bulan} {$tahun} 2>&1";
        $rawOutput = shell_exec($command) ?? '';

        Log::info('[Scanlog Excel] Output: ' . substr($rawOutput, 0, 300));

        if (!file_exists($outputPath)) {
            return response()->json([
                'error' => 'Gagal membuat file Excel. Detail: ' . substr($rawOutput, 0, 300),
            ], 500);
        }

        return response()->download($outputPath, $outputFile, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Tentukan path Python binary
     */
    private function getPythonBin(): string
    {
        $candidates = ['/usr/bin/python3', '/usr/local/bin/python3', 'python3', 'python'];
        foreach ($candidates as $cmd) {
            $result = shell_exec("which {$cmd} 2>/dev/null");
            if (!empty(trim($result ?? ''))) {
                return $cmd;
            }
        }
        return 'python3';
    }
}
