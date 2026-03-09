<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Check;
use App\Models\Employee;
use App\Models\ScheduleEmployee;
use Carbon\Carbon;

class MarchScanlogSeeder extends Seeder
{
    public function run()
    {
        // Raw data dari Kartu Scanlog 7-9 Maret 2026
        $rawData = [
            ["NASAR SUPRIYANTO", "07-03-2026", "07:39:55", "13:04:25", null],
            ["NASAR SUPRIYANTO", "09-03-2026", "07:46:34", null, null],
            ["KUSTORO", "07-03-2026", "07:57:41", "13:27:41", null],
            ["KUSTORO", "09-03-2026", "07:45:55", null, null],
            ["RAMLAN EFENDI", "07-03-2026", "07:33:04", null, null],
            ["RAMLAN EFENDI", "09-03-2026", "07:51:57", null, null],
            ["ARIFIN ZUHDI", "07-03-2026", "07:46:57", "13:11:40", null],
            ["ARIFIN ZUHDI", "08-03-2026", "07:45:32", "16:02:00", null],
            ["KHAERUL FUAD", "07-03-2026", "07:54:49", "13:09:40", null],
            ["KHAERUL FUAD", "08-03-2026", "07:55:15", "16:00:03", null],
            ["KARNAHUDIN", "07-03-2026", "07:59:14", null, null],
            ["KARNAHUDIN", "09-03-2026", "07:58:17", null, null],
            ["ISMAN SURANDARU", "07-03-2026", "07:58:45", "13:16:21", null],
            ["ISMAN SURANDARU", "09-03-2026", "07:59:49", null, null],
            ["NIZAR", "07-03-2026", "07:46:21", "14:30:07", null],
            ["SUHENDRI", "07-03-2026", "07:49:00", "13:00:22", null],
            ["SUHENDRI", "09-03-2026", "07:46:09", null, null],
            ["ATTHOUR ROHMAN", "07-03-2026", "07:52:14", "13:03:31", null],
            ["ATTHOUR ROHMAN", "09-03-2026", "07:49:51", null, null],
            ["ANDI SAPUTRA", "07-03-2026", "03:06:03", "12:52:43", "16:00:08"],
            ["ANDI SAPUTRA", "08-03-2026", "08:23:44", "16:00:49", null],
            ["AGUS SETIAWAN", "07-03-2026", "07:58:39", "14:51:51", null],
            ["AGUS SETIAWAN", "09-03-2026", "07:51:25", null, null],
            ["EKO P", "07-03-2026", "07:43:03", "13:38:22", null],
            ["EKO P", "09-03-2026", "07:35:52", null, null],
            ["WASMAN", "07-03-2026", "07:39:38", "13:06:39", null],
            ["WASMAN", "08-03-2026", "07:46:09", "16:00:06", null],
            ["WASMAN", "09-03-2026", "07:47:17", null, null],
            ["RISKI FAISAL", "07-03-2026", "07:59:00", "13:11:26", null],
            ["RISKI FAISAL", "08-03-2026", "07:56:21", "16:01:19", null],
            ["RISKI FAISAL", "09-03-2026", "07:56:20", null, null],
            ["ADI BAGUS", "07-03-2026", "07:53:25", "13:08:01", null],
            ["IBNU R", "07-03-2026", "07:49:34", "13:25:57", null],
            ["IBNU R", "09-03-2026", "07:47:47", null, null],
            ["MUHAIMIN", "07-03-2026", "07:05:32", "13:05:17", null],
            ["MUHAIMIN", "09-03-2026", "07:44:02", null, null],
            ["SAEFUL ROHMAN", "07-03-2026", "13:38:17", null, null],
            ["SAEFUL ROHMAN", "08-03-2026", "07:45:17", "16:00:17", null],
            ["UJANG WAHYUDIN", "07-03-2026", "08:01:06", "13:17:27", null],
            ["UJANG WAHYUDIN", "08-03-2026", "08:29:47", "16:00:11", null],
            ["UJANG WAHYUDIN", "09-03-2026", "07:54:32", null, null],
            ["SEPRI MAULADI", "09-03-2026", "07:27:07", null, null],
            ["ISWANTO", "07-03-2026", "03:02:01", "12:48:44", "16:01:04"],
            ["ISWANTO", "09-03-2026", "07:54:42", null, null],
            ["HABIB MAULANA N", "07-03-2026", "07:19:52", "13:05:06", null],
            ["LULU ISLAMIYAH", "07-03-2026", "07:28:12", "13:27:31", null],
            ["LULU ISLAMIYAH", "09-03-2026", "07:36:46", null, null],
            ["RIZKI SYAEFUL A", "07-03-2026", "07:30:19", "13:27:23", null],
            ["RIZKI SYAEFUL A", "09-03-2026", "07:36:58", null, null],
            ["ZAENUDIN", "07-03-2026", "07:36:56", "13:00:53", null],
            ["ZAENUDIN", "09-03-2026", "07:48:07", null, null],
            ["ZAINAL KABIB", "07-03-2026", "07:54:15", "13:01:39", null],
            ["ZAINAL KABIB", "08-03-2026", "07:47:14", "16:00:59", null],
            ["JULI", "07-03-2026", "07:36:26", "13:03:51", null],
            ["WIWIN SH", "07-03-2026", "07:55:52", "13:23:21", null],
            ["WIWIN SH", "09-03-2026", "07:44:15", null, null],
            ["IRFAN", "07-03-2026", "07:31:47", "15:03:58", null],
            ["IRFAN", "09-03-2026", "07:02:55", null, null],
            ["IMAM CNC", "07-03-2026", "08:04:06", "13:13:56", null],
            ["IMAM CNC", "09-03-2026", "08:00:04", null, null],
            ["AGUNG WIRECUT", "07-03-2026", "07:28:03", "13:01:16", null],
            ["AGUNG WIRECUT", "09-03-2026", "07:35:37", "07:39:29", null],
            ["ROVIN", "07-03-2026", "07:00:43", null, null],
            ["ROVIN", "09-03-2026", "06:56:21", null, null],
            ["NOSITA A", "07-03-2026", "07:34:30", "13:00:57", null],
            ["NOSITA A", "09-03-2026", "07:39:17", null, null],
            ["HENDI IRAWAN", "07-03-2026", "07:50:30", "13:02:34", null],
            ["HENDI IRAWAN", "09-03-2026", "07:48:04", null, null],
            ["FAROYI", "07-03-2026", "07:51:12", "15:30:22", null],
            ["FAROYI", "08-03-2026", "07:57:29", "16:06:49", null],
            ["FAROYI", "09-03-2026", "07:56:42", null, null],
            ["NURUL AMALIA", "07-03-2026", "07:47:05", "13:01:19", null],
            ["NURUL AMALIA", "09-03-2026", "07:44:59", null, null],
            ["TOSIN", "07-03-2026", "07:27:58", "13:00:49", null],
            ["TOSIN", "09-03-2026", "07:21:52", null, null],
            ["SYARIEF", "07-03-2026", "06:45:34", "16:03:18", null],
            ["SYARIEF", "08-03-2026", "06:52:08", "16:16:34", null],
            ["SYARIEF", "09-03-2026", "06:50:07", null, null],
            ["RUDI CNC", "07-03-2026", "07:51:29", "13:02:44", null],
            ["RUDI CNC", "08-03-2026", "07:49:22", "16:00:46", null],
            ["IRAWANTO", "07-03-2026", "07:56:45", "13:53:23", null],
            ["IRAWANTO", "08-03-2026", "07:59:19", "16:00:41", null],
            ["syaebudi", "07-03-2026", "05:01:45", "12:53:27", "16:00:13"],
            ["syaebudi", "09-03-2026", "07:14:00", null, null],
            ["AAM SETIAWAN", "07-03-2026", "03:01:48", "12:55:05", "16:01:20"],
            ["AAM SETIAWAN", "09-03-2026", "07:50:57", null, null],
            ["ALIFUDIN", "07-03-2026", "01:01:28", "12:55:37", "16:00:05"],
            ["ALIFUDIN", "09-03-2026", "07:57:01", null, null],
            ["TAFIP", "07-03-2026", "05:01:41", "12:57:58", "16:00:02"],
            ["TAFIP", "09-03-2026", "07:57:34", null, null],
            ["NANA", "07-03-2026", "06:59:57", "16:00:52", null],
            ["NANA", "09-03-2026", "07:08:46", null, null],
            ["DIMAS PK", "07-03-2026", "07:57:36", "14:03:17", null],
            ["DIMAS PK", "09-03-2026", "07:57:24", null, null],
            ["BARLIAN", "07-03-2026", "07:57:38", "13:02:55", null],
            ["BARLIAN", "09-03-2026", "07:57:19", null, null],
            ["BANGKITPKL", "07-03-2026", "07:58:00", "13:02:40", null],
            ["BANGKITPKL", "09-03-2026", "07:57:55", null, null],
        ];

        // Cache employees by name (case-insensitive)
        $employees = Employee::all()->keyBy(function($e) {
            return strtoupper(trim($e->name));
        });

        $inserted = 0;
        $notFound = [];

        foreach ($rawData as $row) {
            [$nama, $tanggal, $scan1, $scan2, $scan3] = $row;

            $namaUpper = strtoupper(trim($nama));
            $employee  = $employees->get($namaUpper);

            if (!$employee) {
                $notFound[] = $nama;
                continue;
            }

            // Parse tanggal
            $date = Carbon::createFromFormat('d-m-Y', $tanggal);
            $dateStr = $date->format('Y-m-d');
            $dayOfWeek = $date->dayOfWeek; // 0=Sun, 6=Sat

            // Tentukan schedule_ids berdasarkan hari
            if ($dayOfWeek === 0) {
                // Minggu = holiday/lembur
                $scheduleIds = [5, 6];
            } elseif ($dayOfWeek === 6) {
                // Sabtu
                $scheduleIds = [3, 4];
            } else {
                // Weekday
                $scheduleIds = [1, 2];
            }

            // Tentukan shift dari jam scan1
            $scan1Hour  = (int) substr($scan1, 0, 2);
            $isMalam    = $scan1Hour >= 16 || $scan1Hour < 6;
            $scheduleId = $isMalam ? $scheduleIds[1] : $scheduleIds[0];
            $scheduleId = $scheduleId ?? $scheduleIds[0]; // fallback

            // Assign schedule ke employee kalau belum ada
            $exists = \DB::table('schedule_employees')
                ->where('emp_id', $employee->id)
                ->where('schedule_id', $scheduleId)
                ->exists();
            if (!$exists) {
                \DB::table('schedule_employees')->insert([
                    'emp_id'      => $employee->id,
                    'schedule_id' => $scheduleId,
                ]);
            }

            // Tentukan scan in dan scan out
            if ($scan3) {
                // 3 scan: scan1=malam kemarin, scan2=pagi, scan3=pulang
                $attendanceTime = $dateStr . ' ' . $scan1;
                $leaveTime      = $dateStr . ' ' . $scan3;
            } elseif ($scan2) {
                $attendanceTime = $dateStr . ' ' . $scan1;
                // Overnight: scan1 malam, scan2 pagi berikutnya
                if ($isMalam && (int) substr($scan2, 0, 2) < 12) {
                    $nextDay   = $date->copy()->addDay()->format('Y-m-d');
                    $leaveTime = $nextDay . ' ' . $scan2;
                } else {
                    $leaveTime = $dateStr . ' ' . $scan2;
                }
            } else {
                $attendanceTime = $dateStr . ' ' . $scan1;
                $leaveTime      = null;
            }

            Check::create([
                'emp_id'          => $employee->id,
                'attendance_time' => $attendanceTime,
                'leave_time'      => $leaveTime,
            ]);

            $inserted++;
        }

        $this->command->info("Inserted: $inserted records");
        if ($notFound) {
            $unique = array_unique($notFound);
            $this->command->warn("Not found (" . count($unique) . "): " . implode(', ', $unique));
        }
    }
}