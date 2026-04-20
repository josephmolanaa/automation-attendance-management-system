<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Models\Employee;
use App\Models\Check;

class ScanlogUploadController extends Controller
{
    /**
     * Halaman Import Data Absensi (CSV / Manual)
     */
    public function index()
    {
        return view('admin.scanlog-upload');
    }

    /**
     * Download template CSV kosong untuk diisi user
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_absensi.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            // BOM untuk Excel agar bisa baca UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header — posisi opsional, boleh dikosongkan
            fputcsv($handle, ['nama', 'posisi', 'tanggal', 'scan_masuk', 'scan_keluar'], ';');

            // Contoh data
            $examples = [
                // Shift pagi normal
                ['AGUS SETIAWAN',  'Operator',    '2026-04-01', '07:30:00', '17:05:00'],
                ['AGUS SETIAWAN',  '',            '2026-04-02', '07:28:00', '17:10:00'],
                // Shift malam (scan_keluar dikosongkan, akan di-merge otomatis dengan baris berikutnya)
                ['RUDI CNC',       'Operator',    '2026-04-13', '18:37:00', ''],
                // Baris ini = departure shift malam (scan1 < 07:00) -> otomatis jadi scan_keluar tgl 13
                ['RUDI CNC',       '',            '2026-04-14', '05:02:00', ''],
                // Scan masuk normal Sabtu
                ['NASAR SUPRIANTO','Supervisor',  '2026-04-05', '08:00:00', '13:00:00'],
            ];

            foreach ($examples as $row) {
                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Parse CSV yang diupload user.
     * Return: preview JSON berisi employees + records + DB match status.
     *
     * Format CSV yang diterima (separator ; atau ,):
     *   nama ; tanggal ; scan_masuk ; scan_keluar
     *   AGUS SETIAWAN ; 2026-04-01 ; 07:30:00 ; 17:05:00
     */
    public function parseCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'csv_file.required' => 'File CSV wajib diupload.',
            'csv_file.mimes'    => 'File harus berformat CSV atau TXT.',
            'csv_file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        $path    = $request->file('csv_file')->getRealPath();
        $content = file_get_contents($path);

        // Hapus BOM jika ada
        $content = ltrim($content, "\xEF\xBB\xBF");

        // Deteksi separator: ';' atau ','
        $firstLine = strtok($content, "\n");
        $separator = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';

        $lines  = preg_split('/\r?\n/', trim($content));
        $header = null;
        $rawRows = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            $cols = array_map('trim', str_getcsv($line, $separator));

            if ($header === null) {
                // Baris pertama = header
                $header = array_map('strtolower', $cols);
                // Normalisasi nama kolom
                $header = array_map(fn($h) => str_replace([' ', '-'], '_', $h), $header);
                continue;
            }

            // Map ke kolom header
            $row = [];
            foreach ($header as $i => $col) {
                $row[$col] = $cols[$i] ?? '';
            }
            // Juga simpan baris asli untuk kolom yang mungkin tidak ada di header
            $rawRows[] = $row;
        }

        if (empty($rawRows)) {
            return response()->json([
                'success' => false,
                'message' => 'File CSV kosong atau format tidak valid. Pastikan menggunakan template yang benar.',
            ], 422);
        }

        // Group rows by nama
        $grouped  = []; // [namaUpper => ['posisi'=>..., 'records'=>[...]]]
        foreach ($rawRows as $row) {
            // Support variasi nama kolom
            $nama    = trim($row['nama']       ?? $row['name']    ?? '');
            $posisi  = trim($row['posisi']     ?? $row['jabatan'] ?? $row['position'] ?? '');
            $tanggal = trim($row['tanggal']    ?? $row['date']    ?? $row['tgl'] ?? '');
            $scan1   = trim($row['scan_masuk'] ?? $row['scan1']   ?? $row['masuk']  ?? $row['time_in']  ?? '');
            $scan2   = trim($row['scan_keluar']?? $row['scan2']   ?? $row['keluar'] ?? $row['time_out'] ?? '');
            $scan3   = trim($row['scan_3'] ?? $row['scan3'] ?? $row['scan_masuk_2'] ?? '');

            if (empty($nama) || empty($tanggal)) continue;

            // Normalisasi tanggal ke Y-m-d
            $tanggal = $this->normalizeDate($tanggal);
            if (!$tanggal) continue;

            // Normalisasi waktu ke HH:MM:SS
            $scan1 = $scan1 ? $this->normalizeTime($scan1) : null;
            $scan2 = $scan2 ? $this->normalizeTime($scan2) : null;
            $scan3 = $scan3 ? $this->normalizeTime($scan3) : null;

            $namaUpper = strtoupper($nama);
            if (!isset($grouped[$namaUpper])) {
                $grouped[$namaUpper] = ['posisi' => '', 'records' => []];
            }
            // Simpan posisi pertama yang tidak kosong
            if ($posisi && !$grouped[$namaUpper]['posisi']) {
                $grouped[$namaUpper]['posisi'] = $posisi;
            }
            $grouped[$namaUpper]['records'][] = [
                'tanggal' => $tanggal,
                'scan1'   => $scan1,
                'scan2'   => $scan2,
                'scan3'   => $scan3,
            ];
        }

        // ── Overnight detection ────────────────────────────────────────────────
        // Gunakan AttendanceImportService baru untuk memproses preview data
        $importService = app(\App\Services\AttendanceImportService::class);

        foreach ($grouped as $namaUpper => &$empData) {
            $recs = $empData['records'];
            usort($recs, fn($a, $b) => strcmp($a['tanggal'], $b['tanggal']));

            $empData['records'] = $importService->parsePreviewMode(collect($recs));
        }
        unset($empData); // clear reference
        // ─────────────────────────────────────────────────────────────────────

        if (empty($grouped)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada baris data yang valid. Cek format tanggal (YYYY-MM-DD) dan nama kolom.',
            ], 422);
        }

        // Build employees array + DB enrichment
        $allEmployees = Employee::all();
        $employees    = [];

        foreach ($grouped as $namaUpper => $data) {
            $matched = $this->matchEmployeeByName($namaUpper, $allEmployees);

            $employees[] = [
                'nama'        => $namaUpper,
                'posisi'      => $data['posisi'],
                'records'     => $data['records'],
                'found_in_db' => $matched !== null,
                'db_emp_id'   => $matched?->id,
                'db_name'     => $matched?->name,
            ];
        }

        // Urutkan: yang found_in_db dulu
        usort($employees, fn($a, $b) => ($b['found_in_db'] ? 1 : 0) - ($a['found_in_db'] ? 1 : 0));

        $totalRows     = array_sum(array_map(fn($e) => count($e['records']), $employees));
        $foundCount    = count(array_filter($employees, fn($e) => $e['found_in_db']));
        $notFoundCount = count($employees) - $foundCount;

        return response()->json([
            'success'         => true,
            'employees'       => $employees,
            'total_rows'      => $totalRows,
            'found_count'     => $foundCount,
            'not_found_count' => $notFoundCount,
            'message'         => count($employees) . ' karyawan, ' . $totalRows . ' record berhasil dibaca dari CSV.',
        ]);
    }

    /**
     * Import data ke database (tabel checks).
     * Request body: data = JSON string employees (sama format seperti output parseCsv)
     */
    public function importToDb(Request $request)
    {
        $request->validate(['data' => 'required|string']);

        $employees      = json_decode($request->input('data'), true);
        $createNew      = (bool) $request->input('create_employees', false);

        if (json_last_error() !== JSON_ERROR_NONE || empty($employees)) {
            return response()->json(['success' => false, 'message' => 'Data JSON tidak valid.'], 422);
        }

        $totalInserted    = 0;
        $totalUpdated     = 0;
        $totalSkipped     = 0;
        $totalNotFound    = 0;
        $totalEmpCreated  = 0;
        $details          = [];

        foreach ($employees as $emp) {
            $empName  = $emp['nama']      ?? '-';
            $empPosisi= $emp['posisi']    ?? '';
            $dbEmpId  = $emp['db_emp_id'] ?? null;
            $records  = $emp['records']   ?? [];

            // Jika karyawan tidak ada di DB tapi create_employees aktif → buat dulu
            if (!$dbEmpId && $createNew) {
                $newEmp           = new \App\Models\Employee();
                $newEmp->name     = ucwords(strtolower($empName));
                $newEmp->position = $empPosisi ?: 'Karyawan';
                $newEmp->save();
                $dbEmpId = $newEmp->id;
                $totalEmpCreated++;
                $emp['db_emp_id'] = $dbEmpId;
                $emp['db_name']   = $newEmp->name;
            }

            if (!$dbEmpId) {
                $totalNotFound++;
                $details[] = [
                    'nama'    => $empName,
                    'status'  => 'not_found',
                    'message' => 'Karyawan tidak ditemukan di database.',
                ];
                continue;
            }

            $empInserted = 0;
            $empUpdated  = 0;
            $empSkipped  = 0;

            foreach ($records as $rec) {
                $tanggal   = $rec['tanggal'] ?? null;
                $scan1     = $rec['scan1']   ?? null;
                $scan2     = $rec['scan2']   ?? null;

                if (!$tanggal) { $empSkipped++; $totalSkipped++; continue; }

                $attTime = $scan1 ? ($tanggal . ' ' . $scan1) : ($tanggal . ' 00:00:00');

                // Hitung leave_time — deteksi overnight: jika scan2 < scan1 (jam), +1 hari
                $leaveTime = null;
                if ($scan2) {
                    $leaveDate = $tanggal;
                    if ($scan1 && $scan2 < $scan1) {
                        // Overnight: pulang hari berikutnya
                        $leaveDate = date('Y-m-d', strtotime($tanggal . ' +1 day'));
                    }
                    $leaveTime = $leaveDate . ' ' . $scan2;
                }

                $existing = Check::where('emp_id', $dbEmpId)
                    ->whereDate('attendance_time', $tanggal)
                    ->first();

                if (!$existing) {
                    Check::create([
                        'emp_id'          => $dbEmpId,
                        'attendance_time' => $attTime,
                        'leave_time'      => $leaveTime,
                    ]);
                    $empInserted++; $totalInserted++;

                } elseif (!$existing->leave_time && $leaveTime) {
                    $existing->update(['leave_time' => $leaveTime]);
                    $empUpdated++; $totalUpdated++;

                } else {
                    $empSkipped++; $totalSkipped++;
                }
            }

            $details[] = [
                'nama'          => $empName,
                'db_name'       => $emp['db_name'] ?? $empName,
                'status'        => 'found',
                'newly_created' => ($totalEmpCreated > 0 && isset($newEmp) && $newEmp->id == $dbEmpId),
                'inserted'      => $empInserted,
                'updated'       => $empUpdated,
                'skipped'       => $empSkipped,
            ];
        }

        Log::info('[CSV Import] emp_created=' . $totalEmpCreated . ' inserted=' . $totalInserted
            . ' updated=' . $totalUpdated . ' skipped=' . $totalSkipped . ' not_found=' . $totalNotFound);

        $msg = "Import selesai: {$totalInserted} ditambahkan, {$totalUpdated} diperbarui, {$totalSkipped} dilewati";
        if ($totalEmpCreated > 0) $msg .= ", {$totalEmpCreated} karyawan baru dibuat";
        if ($totalNotFound    > 0) $msg .= ", {$totalNotFound} tidak ditemukan di database";

        return response()->json([
            'success' => true,
            'summary' => [
                'emp_created' => $totalEmpCreated,
                'inserted'    => $totalInserted,
                'updated'     => $totalUpdated,
                'skipped'     => $totalSkipped,
                'not_found'   => $totalNotFound,
            ],
            'details' => $details,
            'message' => $msg,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Normalisasi format tanggal ke Y-m-d.
     * Support: 2026-04-01, 01/04/2026, 01-04-2026, 1-4-2026
     */
    private function normalizeDate(string $raw): ?string
    {
        $raw = trim($raw);
        if (empty($raw)) return null;

        // Sudah format Y-m-d
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $raw, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }
        // Format d/m/Y atau d-m-Y
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $raw, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        // Coba strtotime sebagai fallback
        $ts = strtotime($raw);
        if ($ts !== false) return date('Y-m-d', $ts);

        return null;
    }

    /**
     * Normalisasi format waktu ke H:i:s.
     * Support: 07:30, 07:30:00, 730, 7.30
     */
    private function normalizeTime(string $raw): ?string
    {
        $raw = trim($raw);
        if (empty($raw)) return null;

        // H:i:s atau H:i
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $raw, $m)) {
            $h = max(0, min(23, (int)$m[1]));
            $i = max(0, min(59, (int)$m[2]));
            $s = max(0, min(59, (int)($m[3] ?? '00')));
            return sprintf('%02d:%02d:%02d', $h, $i, $s);
        }
        // Titik: 7.30
        if (preg_match('/^(\d{1,2})\.(\d{2})$/', $raw, $m)) {
            $h = max(0, min(23, (int)$m[1]));
            $i = max(0, min(59, (int)$m[2]));
            return sprintf('%02d:%02d:00', $h, $i);
        }
        return null;
    }

    /**
     * Cocokkan nama CSV ke Employee di database.
     * Strategi: exact → contains → word overlap → similar_text (70%)
     */
    private function matchEmployeeByName(string $ocrName, $allEmployees): ?Employee
    {
        $ocrUpper = strtoupper(trim($ocrName));

        foreach ($allEmployees as $emp) {
            if (strtoupper(trim($emp->name)) === $ocrUpper) return $emp;
        }

        foreach ($allEmployees as $emp) {
            $dbUpper = strtoupper(trim($emp->name));
            if (strlen($ocrUpper) >= 4 && str_contains($dbUpper, $ocrUpper)) return $emp;
            if (strlen($dbUpper) >= 4  && str_contains($ocrUpper, $dbUpper)) return $emp;
        }

        $ocrWords    = array_filter(explode(' ', $ocrUpper), fn($w) => strlen($w) > 3);
        $bestOverlap = 0;
        $bestMatch   = null;
        foreach ($allEmployees as $emp) {
            $dbWords = array_filter(explode(' ', strtoupper($emp->name)), fn($w) => strlen($w) > 3);
            $overlap = count(array_intersect($ocrWords, $dbWords));
            if ($overlap > $bestOverlap) { $bestOverlap = $overlap; $bestMatch = $emp; }
        }
        if ($bestOverlap >= 1) return $bestMatch;

        $bestScore = 0;
        $bestFuzzy = null;
        foreach ($allEmployees as $emp) {
            similar_text($ocrUpper, strtoupper($emp->name), $pct);
            if ($pct > $bestScore) { $bestScore = $pct; $bestFuzzy = $emp; }
        }
        if ($bestScore >= 70) return $bestFuzzy;

        return null;
    }
}
