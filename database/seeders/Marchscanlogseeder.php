<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MarchScanlogSeeder extends Seeder
{
    private array $nameMap = [
        'NASAR SUPRIANTO' => 'Nasar Suprianto',
        'KUSTORO'         => 'Kustoro',
        'RAMLAN EFENDI'   => 'Ramlan Efendi',
        'ARIFIN ZUHDI'    => 'Arifin Zuhdi',
        'AGUS SETIAWAN'   => 'Agus Setiawan',
        'ALIFFUDIN'       => 'Aliffudin',
        'KARNAHUDIN'      => 'Karnahudin',
        'ISMAN SURANDARU' => 'Isman Surandaru',
        'SUHENDRI'        => 'Suhendri',
        'EKO P'           => 'Eko P',
        'WASMAN'          => 'Wasman',
        'ADI BAGUS'       => 'Adi Bagus',
        'IBNU R'          => 'Ibnu R',
        'MUHAIMIN'        => 'Muhaimin',
        'SAEFUL ROHMAN'   => 'Saeful Rohman',
        'UJANG WAHYUDIN'  => 'Ujang Wahyudin',
        'SEPRI MAULADI'   => 'Sepri Mauladi',
        'ISWANTO'         => 'Iswanto',
        'HABIB MAULANA N' => 'Habib Maulana N',
        'LULU ISLAMIYYAH' => 'Lulu Islamiyyah',
        'RIZKI SYAEFUL A' => 'Rizki syaeful a',
        'ZAENUDIN'        => 'Zaenudin',
        'ZAINAL KABIB'    => 'Zainal Kabib',
        'JULI'            => 'Juli',
        'WIWIN SH'        => 'Wiwin SH',
        'IRFAN'           => 'Irfan',
        'IMAM CNC'        => 'Imam CNC',
        'AGUNG WIRECUT'   => 'Agung Wirecut',
        'ROVIN'           => 'Rovin',
        'NOSITA A'        => 'Nosita A',
        'HENDI IRAWAN'    => 'Hendi Irawan',
        'FAROYI'          => 'Faroyi',
        'NURUL AMALIA'    => 'Nurul Amalia',
        'TOSIN'           => 'Tosin',
        'SYARIEF'         => 'Syarief',
        'RUDI CNC'        => 'Rudi CNC',
        'IRAWANTO'        => 'Irawanto',
        'syaebudi'        => 'syaebudi',
        'AAM SETIAWAN'    => 'Aam Setiawan',
        'TAFIP'           => 'Tafip',
        'nana'            => 'Nana',
        'NIZAR'           => 'Nizar',
        'ANDI SAPUTRA'    => 'Andi Saputra',
        'KHAERUL FUAD'    => 'Khaerul Fuad',
        'ATTHOUR ROHMAN'  => 'Atthour Rohman',
        'DIMAS PK'        => 'Dimas PK',
        'BARLIAN'         => 'Barlian',
        'BANGKITPKL'      => 'Bangkitpkl',
        'ALI MAKHFUZD'    => 'Ali Makhfuzd',
        'RISKI FAISAL'    => 'Riski Faisal',
    ];

    public function run(): void
    {
        $filePath = database_path('seeders/data/scanlog_maret.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("File tidak ditemukan: {$filePath}");
            $this->command->info("Taruh file Excel di: database/seeders/data/scanlog_maret.xlsx");
            return;
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false);

        // Cache semua employee: UPPERCASE(name) => id
        $employees = DB::table('employees')
            ->select('id', 'name')
            ->get()
            ->keyBy(fn($e) => strtoupper(trim($e->name)));

        $inserted = 0;
        $skipped  = 0;
        $notFound = [];
        $inserts  = [];

        foreach ($rows as $rowIdx => $row) {
            if ($rowIdx < 5) continue;

            $namaExcel = trim((string)($row[3] ?? ''));
            $tglRaw    = $row[7] ?? null;
            $s1Raw     = $row[8] ?? null;
            $s2Raw     = $row[9] ?? null;

            if (!$namaExcel || !$tglRaw) continue;
            if (strtoupper($namaExcel) === 'NAMA') continue;

            // Parse tanggal
            $tanggal = $this->parseDate($tglRaw);
            if (!$tanggal) continue;

            // Resolve nama DB
            $namaDB  = $this->nameMap[$namaExcel] ?? $namaExcel;
            $empKey  = strtoupper(trim($namaDB));
            if (!isset($employees[$empKey])) {
                if (!in_array($namaExcel, $notFound)) $notFound[] = $namaExcel;
                $skipped++;
                continue;
            }
            $empId = $employees[$empKey]->id;

            // ── CASE: s1=NULL tapi s2 ada → buat row dengan attendance_time=NULL ──
            if (!$s1Raw && $s2Raw) {
                $s2 = $this->parseTime($s2Raw);
                if ($s2) {
                    $leaveTimeVal = $tanggal->copy()
                        ->setTime($s2->hour, $s2->minute, $s2->second)
                        ->toDateTimeString();

                    $inserts[] = [
                        'emp_id'          => $empId,
                        'attendance_time' => null,
                        'leave_time'      => $leaveTimeVal,
                        'created_at'      => now()->toDateTimeString(),
                        'updated_at'      => now()->toDateTimeString(),
                    ];
                    $inserted++;
                } else {
                    $skipped++;
                }
                continue;
            }

            // Skip tidak ada scan masuk sama sekali
            if (!$s1Raw) { $skipped++; continue; }

            // Parse scan 1
            $s1 = $this->parseTime($s1Raw);
            if (!$s1) { $skipped++; continue; }

            // Parse scan 2 (opsional)
            $s2 = $s2Raw ? $this->parseTime($s2Raw) : null;

            $attendanceTime = $tanggal->copy()->setTime($s1->hour, $s1->minute, $s1->second);

            // Leave time — overnight jika s2 < s1
            $leaveTime = null;
            if ($s2) {
                $leaveDate = $tanggal->copy();
                if ($s2->lt($s1)) $leaveDate->addDay();
                $leaveTime = $leaveDate->copy()->setTime($s2->hour, $s2->minute, $s2->second);
            }

            $inserts[] = [
                'emp_id'          => $empId,
                'attendance_time' => $attendanceTime->toDateTimeString(),
                'leave_time'      => $leaveTime?->toDateTimeString(),
                'created_at'      => now()->toDateTimeString(),
                'updated_at'      => now()->toDateTimeString(),
            ];
            $inserted++;

            if (count($inserts) >= 100) {
                DB::table('checks')->insert($inserts);
                $inserts = [];
            }
        }

        if (!empty($inserts)) {
            DB::table('checks')->insert($inserts);
        }

        $this->command->info("✅ Selesai: {$inserted} data dimasukkan, {$skipped} dilewati.");

        if (!empty($notFound)) {
            $this->command->warn("⚠️  Nama tidak ditemukan di DB:");
            foreach ($notFound as $n) {
                $this->command->warn("   - {$n}");
            }
        }
    }

    /**
     * Parse tanggal dari string "3/1/2026" (M/D/YYYY) atau datetime object
     */
    private function parseDate(mixed $val): ?Carbon
    {
        if (!$val) return null;

        if ($val instanceof \DateTimeInterface) {
            return Carbon::instance($val)->startOfDay();
        }

        $str = trim((string)$val);

        // Format M/D/YYYY atau MM/DD/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $str, $m)) {
            return Carbon::createFromDate((int)$m[3], (int)$m[1], (int)$m[2])->startOfDay();
        }

        // Format DD-MM-YYYY
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $str, $m)) {
            return Carbon::createFromDate((int)$m[3], (int)$m[2], (int)$m[1])->startOfDay();
        }

        // Format YYYY-MM-DD
        try {
            $c = Carbon::parse($str)->startOfDay();
            if ($c->year >= 2020 && $c->year <= 2030) return $c;
        } catch (\Exception $e) {}

        return null;
    }

    /**
     * Parse waktu dari string "7:44:44 AM", "17:00:00", atau time object
     * Return Carbon dengan tanggal hari ini (hanya jam yang penting)
     */
    private function parseTime(mixed $val): ?Carbon
    {
        if (!$val) return null;

        if ($val instanceof \DateTimeInterface) {
            return Carbon::instance($val);
        }

        $str = trim((string)$val);

        // Format dengan AM/PM: "7:44:44 AM" atau "5:57:55 PM"
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?\s*(AM|PM)$/i', $str, $m)) {
            $h = (int)$m[1];
            $i = (int)$m[2];
            $s = isset($m[3]) ? (int)$m[3] : 0;
            $p = strtoupper($m[4]);
            if ($p === 'PM' && $h !== 12) $h += 12;
            if ($p === 'AM' && $h === 12) $h = 0;
            return Carbon::today()->setTime($h, $i, $s);
        }

        // Format 24 jam: "17:00:00"
        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $str, $m)) {
            return Carbon::today()->setTime((int)$m[1], (int)$m[2], isset($m[3]) ? (int)$m[3] : 0);
        }

        return null;
    }
}