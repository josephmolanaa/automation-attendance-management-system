<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
     * Proses upload PDF dan jalankan OCR via Python
     */
    public function upload(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:20480', // max 20MB
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

        // Jalankan OCR Python script
        $escapedPath = escapeshellarg($fullPath);
        $pythonBin   = $this->getPythonBin();
        $command     = "{$pythonBin} {$pythonScript} {$escapedPath} 2>&1";

        $output   = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);
        $rawOutput = implode("\n", $output);

        // Parse hasil JSON dari Python
        $jsonStart = strpos($rawOutput, '[');
        $jsonStr   = $jsonStart !== false ? substr($rawOutput, $jsonStart) : $rawOutput;
        $employees = json_decode($jsonStr, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($employees)) {
            // Kembalikan error beserta raw output untuk debugging
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca PDF. Pastikan PDF berisi teks yang jelas.',
                'debug'   => [
                    'raw_output' => substr($rawOutput, 0, 1000),
                    'exit_code'  => $exitCode,
                ]
            ], 422);
        }

        // Simpan path PDF di session untuk dipakai saat export
        session(['scanlog_pdf_path' => $fullPath]);
        session(['scanlog_employees' => $employees]);

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
            'bulan'  => 'required|integer|min:1|max:12',
            'tahun'  => 'required|integer|min:2020|max:2099',
            'data'   => 'required|string', // JSON string
        ]);

        $bulan    = $request->input('bulan');
        $tahun    = $request->input('tahun');
        $jsonData = $request->input('data');

        // Validasi JSON
        $employees = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($employees)) {
            return response()->json(['error' => 'Data tidak valid.'], 422);
        }

        // Path template dan output
        $templatePath = base_path('TEMPLATE/2026 LEMBUR HARIAN PT G2B(REKAP MARET).xlsx');
        $outputDir    = storage_path('app/scanlog_exports');
        $outputFile   = "Lembur_Harian_{$tahun}_" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '_' . now()->format('His') . '.xlsx';
        $outputPath   = $outputDir . '/' . $outputFile;

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Jalankan Python generate_excel.py
        $pythonScript = base_path('ocr_service/generate_excel.py');
        $pythonBin    = $this->getPythonBin();

        $escapedJson     = escapeshellarg($jsonData);
        $escapedTemplate = escapeshellarg($templatePath);
        $escapedOutput   = escapeshellarg($outputPath);

        $command = "{$pythonBin} {$pythonScript} {$escapedJson} {$escapedTemplate} {$escapedOutput} {$bulan} {$tahun} 2>&1";

        $output   = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);
        $rawOutput = implode("\n", $output);

        // Cek apakah file berhasil dibuat
        if (!file_exists($outputPath)) {
            return response()->json([
                'error'  => 'Gagal membuat file Excel.',
                'debug'  => $rawOutput,
            ], 500);
        }

        // Download file
        return response()->download($outputPath, $outputFile, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Tentukan path Python yang tersedia di sistem
     */
    private function getPythonBin(): string
    {
        // Railway (Linux): /usr/bin/python3
        // Windows dev: python atau python3
        $candidates = ['python3', 'python', '/usr/bin/python3', '/usr/local/bin/python3'];
        foreach ($candidates as $cmd) {
            exec("which {$cmd} 2>/dev/null || where {$cmd} 2>NUL", $out, $code);
            if ($code === 0 && !empty($out)) {
                return $cmd;
            }
        }
        return 'python3'; // default
    }
}
