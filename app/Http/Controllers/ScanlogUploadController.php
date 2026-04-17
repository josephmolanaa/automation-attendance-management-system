<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Check;

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

        // Enrichment: tandai setiap karyawan apakah ada di database
        $employees = $this->enrichWithDbStatus($employees);

        return response()->json([
            'success'   => true,
            'employees' => $employees,
            'message'   => count($employees) . ' karyawan berhasil dibaca dari PDF.',
        ]);
    }

    /**
     * Enrichment: untuk setiap employee dari OCR, tandai status di DB.
     * - found_in_db: true/false
     * - db_emp_id: id di tabel employees (jika ditemukan)
     * - db_name: nama resmi di DB
     */
    private function enrichWithDbStatus(array $employees): array
    {
        $allEmployees = Employee::all();

        foreach ($employees as &$emp) {
            $matched = $this->matchEmployeeByName($emp['nama'], $allEmployees);
            if ($matched) {
                $emp['found_in_db'] = true;
                $emp['db_emp_id']   = $matched->id;
                $emp['db_name']     = $matched->name;
            } else {
                $emp['found_in_db'] = false;
                $emp['db_emp_id']   = null;
                $emp['db_name']     = null;
            }
        }
        unset($emp);

        return $employees;
    }

    /**
     * Cocokkan nama OCR ke Employee di database.
     * Strategi: exact → contains → word overlap → similar_text
     */
    private function matchEmployeeByName(string $ocrName, $allEmployees): ?Employee
    {
        $ocrUpper = strtoupper(trim($ocrName));

        // 1. Exact match
        foreach ($allEmployees as $emp) {
            if (strtoupper(trim($emp->name)) === $ocrUpper) {
                return $emp;
            }
        }

        // 2. OCR name contains DB name, or vice versa
        foreach ($allEmployees as $emp) {
            $dbUpper = strtoupper(trim($emp->name));
            if (strlen($ocrUpper) >= 4 && str_contains($dbUpper, $ocrUpper)) return $emp;
            if (strlen($dbUpper) >= 4 && str_contains($ocrUpper, $dbUpper)) return $emp;
        }

        // 3. Word overlap: cek berapa kata yang sama (minimal 1 kata >= 4 huruf)
        $ocrWords = array_filter(explode(' ', $ocrUpper), fn($w) => strlen($w) > 3);
        $bestOverlap = 0;
        $bestMatch   = null;
        foreach ($allEmployees as $emp) {
            $dbWords = array_filter(explode(' ', strtoupper($emp->name)), fn($w) => strlen($w) > 3);
            $overlap  = count(array_intersect($ocrWords, $dbWords));
            if ($overlap > $bestOverlap) {
                $bestOverlap = $overlap;
                $bestMatch   = $emp;
            }
        }
        if ($bestOverlap >= 1) return $bestMatch;

        // 4. PHP similar_text (fuzzy — threshold 70%)
        $bestScore = 0;
        $bestFuzzy = null;
        foreach ($allEmployees as $emp) {
            similar_text($ocrUpper, strtoupper($emp->name), $pct);
            if ($pct > $bestScore) {
                $bestScore = $pct;
                $bestFuzzy = $emp;
            }
        }
        if ($bestScore >= 70) return $bestFuzzy;

        return null;
    }

    /**
     * Import data OCR ke database (tabel checks).
     *
     * Request body:
     *   data  — JSON string dari employees OCR (sudah enriched dengan db_emp_id)
     *
     * Logic per record:
     *   - Jika db_emp_id ada → cek apakah check sudah ada untuk (emp_id + tanggal)
     *     - Belum ada → INSERT
     *     - Ada + leave_time null + ada scan2 → UPDATE leave_time
     *     - Ada + sudah lengkap → SKIP
     *   - Jika db_emp_id null → SKIP (catat sebagai not_found)
     *
     * Return: ringkasan per karyawan + total inserted/updated/skipped
     */
    public function importToDb(Request $request)
    {
        $request->validate(['data' => 'required|string']);

        $employees = json_decode($request->input('data'), true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($employees)) {
            return response()->json(['success' => false, 'message' => 'Data JSON tidak valid.'], 422);
        }

        $totalInserted = 0;
        $totalUpdated  = 0;
        $totalSkipped  = 0;
        $totalNotFound = 0;
        $details       = [];

        foreach ($employees as $emp) {
            $empName  = $emp['nama'] ?? '-';
            $dbEmpId  = $emp['db_emp_id'] ?? null;
            $records  = $emp['records']   ?? [];

            if (!$dbEmpId) {
                $totalNotFound++;
                $details[] = [
                    'nama'    => $empName,
                    'status'  => 'not_found',
                    'message' => 'Karyawan tidak ditemukan di database.',
                    'records' => [],
                ];
                continue;
            }

            $empDetails   = [];
            $empInserted  = 0;
            $empUpdated   = 0;
            $empSkipped   = 0;

            foreach ($records as $rec) {
                $tanggal = $rec['tanggal'] ?? null; // format YYYY-MM-DD
                $scan1   = $rec['scan1']   ?? null; // HH:MM:SS
                $scan2   = $rec['scan2']   ?? null;

                if (!$tanggal) {
                    $empSkipped++;
                    continue;
                }

                // Buat datetime string
                $attTime  = $scan1 ? ($tanggal . ' ' . $scan1) : null;
                $leaveTime = $scan2 ? ($tanggal . ' ' . $scan2) : null;

                // Cek existing check untuk emp_id + tanggal ini
                $existing = Check::where('emp_id', $dbEmpId)
                    ->whereDate('attendance_time', $tanggal)
                    ->first();

                if (!$existing) {
                    // INSERT baru
                    Check::create([
                        'emp_id'          => $dbEmpId,
                        'attendance_time' => $attTime  ?? ($tanggal . ' 00:00:00'),
                        'leave_time'      => $leaveTime,
                    ]);
                    $empInserted++;
                    $totalInserted++;
                    $empDetails[] = ['tanggal' => $tanggal, 'action' => 'inserted'];

                } elseif (!$existing->leave_time && $leaveTime) {
                    // UPDATE: isi leave_time yang masih null
                    $existing->update(['leave_time' => $leaveTime]);
                    $empUpdated++;
                    $totalUpdated++;
                    $empDetails[] = ['tanggal' => $tanggal, 'action' => 'updated_leave'];

                } else {
                    // SKIP: sudah ada dan lengkap
                    $empSkipped++;
                    $totalSkipped++;
                    $empDetails[] = ['tanggal' => $tanggal, 'action' => 'skipped'];
                }
            }

            $details[] = [
                'nama'     => $empName,
                'db_name'  => $emp['db_name'] ?? $empName,
                'status'   => 'found',
                'inserted' => $empInserted,
                'updated'  => $empUpdated,
                'skipped'  => $empSkipped,
                'records'  => $empDetails,
            ];
        }

        Log::info('[Scanlog Import] inserted=' . $totalInserted . ' updated=' . $totalUpdated
            . ' skipped=' . $totalSkipped . ' not_found=' . $totalNotFound);

        return response()->json([
            'success'    => true,
            'summary' => [
                'inserted'  => $totalInserted,
                'updated'   => $totalUpdated,
                'skipped'   => $totalSkipped,
                'not_found' => $totalNotFound,
            ],
            'details'    => $details,
            'message'    => "Import selesai: {$totalInserted} ditambahkan, {$totalUpdated} diperbarui, "
                          . "{$totalSkipped} dilewati, {$totalNotFound} tidak ditemukan di database.",
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
