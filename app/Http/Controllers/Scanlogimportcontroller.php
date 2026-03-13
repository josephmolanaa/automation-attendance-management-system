<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Check;
use App\Models\Employee;

class ScanlogImportController extends Controller
{
    /**
     * Tampilkan halaman upload
     */
    public function index()
    {
        return view('scanlog_import');
    }

    /**
     * Proses import file Excel scanlog
     *
     * Format Excel yang diharapkan:
     * Kolom: Nama | Tanggal | Scan 1 | Scan 2 | Scan 3 (opsional)
     *
     * Logic penentuan IN/OUT berbasis jam (bukan posisi kolom):
     *   00:00–05:59 → timeout shift malam (cari open check kemarin/hari ini)
     *   06:00–11:59 → ambigu: cek open check kemarin → timeout, atau → time_in baru
     *   12:00–15:59 → cek open check hari ini → timeout, atau → time_in baru (shift sabtu siang)
     *   16:00–18:59 → cek open check hari ini → timeout (lembur), atau → time_in shift_2_friday
     *   19:00–23:59 → cek open check hari ini → timeout (lembur), atau → time_in shift_2
     *
     * Opsi A: scan masuk tanpa scan keluar → tetap dicatat, leave_time = NULL
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $path = $request->file('file')->getRealPath();

        // Baca Excel menggunakan PhpSpreadsheet
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows  = $sheet->toArray(null, true, true, true); // A=1, B=2, ...

        $logs    = [];   // hasil log per baris untuk ditampilkan ke user
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $flagged = [];   // anomali yang perlu perhatian

        // --- Deteksi baris header ---
        $headerRow = null;
        $colNama   = null;
        $colTgl    = null;
        $colScans  = []; // array index kolom scan

        foreach ($rows as $rowIdx => $row) {
            foreach ($row as $colIdx => $val) {
                $v = strtolower(trim((string) $val));
                if ($v === 'nama')   $colNama = $colIdx;
                if (in_array($v, ['tanggal', 'date'])) $colTgl = $colIdx;
                if (preg_match('/^scan\s*(\d)$/i', $v, $m)) {
                    $colScans[(int)$m[1]] = $colIdx;
                }
            }
            if ($colNama && $colTgl && count($colScans) >= 1) {
                $headerRow = $rowIdx;
                break;
            }
        }

        if (!$headerRow) {
            return back()->with('error', 'Format file tidak dikenali. Pastikan ada kolom Nama, Tanggal, Scan 1.');
        }

        ksort($colScans); // urut scan 1, 2, 3

        // --- Kumpulkan semua scan per (nama, tanggal) ---
        $grouped = []; // ['NAMA|2026-03-07'] => [timestamps...]

        foreach ($rows as $rowIdx => $row) {
            if ($rowIdx <= $headerRow) continue;

            $nama = trim((string)($row[$colNama] ?? ''));
            $tgl  = $row[$colTgl] ?? null;

            if (!$nama || !$tgl) continue;

            // Parse tanggal (bisa string dd-mm-yyyy atau Excel serial)
            $tanggal = $this->parseDate($tgl);
            if (!$tanggal) continue;

            $key = strtoupper($nama) . '|' . $tanggal->format('Y-m-d');

            foreach ($colScans as $scanNo => $colIdx) {
                $scanVal = trim((string)($row[$colIdx] ?? ''));
                if (!$scanVal) continue;

                $dt = $this->parseScanTime($scanVal, $tanggal);
                if ($dt) {
                    $grouped[$key][] = $dt;
                }
            }
        }

        // --- Proses setiap (nama, tanggal) ---
        foreach ($grouped as $key => $timestamps) {
            [$namaUpper, $tglStr] = explode('|', $key, 2);
            $tanggal = Carbon::parse($tglStr);

            // Cari employee by name (case-insensitive)
            $employee = Employee::whereRaw('UPPER(name) = ?', [$namaUpper])->first();
            if (!$employee) {
                $flagged[] = "[{$tglStr}] {$namaUpper}: karyawan tidak ditemukan di database, dilewati.";
                $skipped++;
                continue;
            }

            // Urutkan timestamp ascending
            sort($timestamps);

            foreach ($timestamps as $ts) {
                $result = $this->processScan($employee->id, $ts, $tanggal, $namaUpper);
                if ($result === 'created') $created++;
                elseif ($result === 'updated') $updated++;
                elseif ($result === 'duplicate') $skipped++;
                elseif (str_starts_with($result, 'FLAG:')) {
                    $flagged[] = "[{$tglStr}] {$namaUpper}: " . substr($result, 5);
                    $skipped++;
                }
            }
        }

        $summary = "Import selesai: {$created} check-in baru, {$updated} check-out diisi, {$skipped} dilewati.";
        return back()
            ->with('success', $summary)
            ->with('flagged', $flagged);
    }

    /**
     * Proses satu timestamp untuk satu karyawan.
     *
     * Return: 'created' | 'updated' | 'duplicate' | 'FLAG:...'
     */
    private function processScan(int $empId, Carbon $ts, Carbon $tanggal, string $namaLog): string
    {
        $hour = $ts->hour;

        // --- Duplikat: sudah ada scan dalam ±30 menit? ---
        $duplicate = Check::where('emp_id', $empId)
            ->where(function ($q) use ($ts) {
                $q->whereBetween('attendance_time', [
                    $ts->copy()->subMinutes(30),
                    $ts->copy()->addMinutes(30),
                ])->orWhereBetween('leave_time', [
                    $ts->copy()->subMinutes(30),
                    $ts->copy()->addMinutes(30),
                ]);
            })->exists();

        if ($duplicate) return 'duplicate';

        // --- KELOMPOK A: 00:00–05:59 → timeout shift malam ---
        if ($hour >= 0 && $hour < 6) {
            return $this->fillLeaveTime($empId, $ts, $tanggal, range: 'A');
        }

        // --- KELOMPOK B: 06:00–11:59 → ambigu, cek open check kemarin ---
        if ($hour >= 6 && $hour < 12) {
            // Cek apakah ada open check dari hari sebelumnya
            $openYesterday = $this->findOpenCheck($empId, $tanggal->copy()->subDay());
            if ($openYesterday) {
                // Ini timeout dari kemarin (misal: shift overnight yang baru pulang pagi)
                $openYesterday->leave_time = $ts;
                $openYesterday->save();
                return 'updated';
            }
            // Tidak ada open check kemarin → ini time_in pagi hari ini
            return $this->createCheckIn($empId, $ts);
        }

        // --- KELOMPOK C: 12:00–15:59 → cek open check hari ini ---
        if ($hour >= 12 && $hour < 16) {
            $openToday = $this->findOpenCheck($empId, $tanggal);
            if ($openToday) {
                // Ada open check hari ini → ini timeout (misal: SHIFT_1_WEEKEND pulang 13:00)
                $openToday->leave_time = $ts;
                $openToday->save();
                return 'updated';
            }
            // Tidak ada → ini time_in shift baru (misal: SHIFT_2_WEEKEND masuk 13:00)
            return $this->createCheckIn($empId, $ts);
        }

        // --- KELOMPOK D: 16:00–18:59 → cek open check hari ini ---
        if ($hour >= 16 && $hour < 19) {
            $openToday = $this->findOpenCheck($empId, $tanggal);
            if ($openToday) {
                // Ada open check pagi → ini timeout lembur dari SHIFT_1_WEEKDAY
                $openToday->leave_time = $ts;
                $openToday->save();
                return 'updated';
            }
            // Tidak ada → ini time_in SHIFT_2_FRIDAY atau SHIFT_2_WEEKEND
            return $this->createCheckIn($empId, $ts);
        }

        // --- KELOMPOK E: 19:00–23:59 → cek open check hari ini ---
        if ($hour >= 19) {
            $openToday = $this->findOpenCheck($empId, $tanggal);
            if ($openToday) {
                // Ada open check pagi → ini timeout lembur
                $openToday->leave_time = $ts;
                $openToday->save();
                return 'updated';
            }
            // Tidak ada open check → ini time_in SHIFT_2_WEEKDAY
            return $this->createCheckIn($empId, $ts);
        }

        return 'FLAG: tidak masuk kelompok manapun, timestamp=' . $ts->toDateTimeString();
    }

    /**
     * Isi leave_time dari open check (untuk kelompok A: dini hari)
     * Cari di hari yang sama dulu, lalu hari sebelumnya.
     */
    private function fillLeaveTime(int $empId, Carbon $ts, Carbon $tanggal, string $range): string
    {
        // Cari open check hari yang sama (misal: scan masuk jam 23:xx, pulang 01:xx masih dihitung hari sama)
        $open = $this->findOpenCheck($empId, $tanggal);
        if (!$open) {
            // Cari hari sebelumnya
            $open = $this->findOpenCheck($empId, $tanggal->copy()->subDay());
        }

        if ($open) {
            $open->leave_time = $ts;
            $open->save();
            return 'updated';
        }

        // Tidak ada open check → anomali
        return 'FLAG: scan dini hari (' . $ts->format('H:i') . ') tapi tidak ada open check kemarin maupun hari ini.';
    }

    /**
     * Cari open check (leave_time = NULL) untuk emp_id di tanggal tertentu.
     * Cari berdasarkan DATE(attendance_time) = tanggal.
     */
    private function findOpenCheck(int $empId, Carbon $tanggal): ?Check
    {
        return Check::where('emp_id', $empId)
            ->whereNull('leave_time')
            ->whereDate('attendance_time', $tanggal->format('Y-m-d'))
            ->orderBy('attendance_time', 'desc')
            ->first();
    }

    /**
     * Buat baris check-in baru.
     * Opsi A: leave_time = NULL (tidak masalah kalau tidak scan keluar)
     */
    private function createCheckIn(int $empId, Carbon $ts): string
    {
        Check::create([
            'emp_id'          => $empId,
            'attendance_time' => $ts,
            'leave_time'      => null,
        ]);
        return 'created';
    }

    /**
     * Parse tanggal dari berbagai format:
     * - dd-mm-yyyy
     * - yyyy-mm-dd
     * - Excel serial number
     * - Carbon/DateTime object
     */
    private function parseDate(mixed $val): ?Carbon
    {
        if (!$val) return null;

        // Sudah Carbon atau DateTime
        if ($val instanceof \DateTimeInterface) {
            return Carbon::instance($val);
        }

        // Excel serial number (integer)
        if (is_numeric($val) && !str_contains((string)$val, '-') && !str_contains((string)$val, '/')) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$val);
                return Carbon::instance($date);
            } catch (\Exception $e) {}
        }

        // String: dd-mm-yyyy atau dd/mm/yyyy
        if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', trim((string)$val), $m)) {
            return Carbon::createFromDate($m[3], $m[2], $m[1]);
        }

        // String: yyyy-mm-dd
        try {
            return Carbon::parse((string)$val);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse waktu scan dan gabungkan dengan tanggal.
     * Format: HH:MM atau HH:MM:SS
     */
    private function parseScanTime(string $val, Carbon $tanggal): ?Carbon
    {
        $val = trim($val);
        if (!$val) return null;

        // Format HH:MM:SS atau HH:MM
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $val, $m)) {
            $h = (int)$m[1];
            $i = (int)$m[2];
            $s = isset($m[3]) ? (int)$m[3] : 0;
            return $tanggal->copy()->setTime($h, $i, $s);
        }

        // Excel time serial (float antara 0-1)
        if (is_numeric($val)) {
            $frac = fmod((float)$val, 1);
            $totalSeconds = round($frac * 86400);
            $h = intdiv($totalSeconds, 3600);
            $i = intdiv($totalSeconds % 3600, 60);
            $s = $totalSeconds % 60;
            return $tanggal->copy()->setTime($h, $i, $s);
        }

        return null;
    }
}