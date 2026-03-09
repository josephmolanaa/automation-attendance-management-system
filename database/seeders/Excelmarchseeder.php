<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Check;
use App\Models\Employee;
use Carbon\Carbon;

class ExcelMarchSeeder extends Seeder
{
    public function run()
    {
        // Hapus data Maret 2026 yang sudah ada
        Check::whereYear('attendance_time', 2026)
             ->whereMonth('attendance_time', 3)
             ->delete();
        Check::whereYear('leave_time', 2026)
             ->whereMonth('leave_time', 3)
             ->delete();

        $rawData = [
            ["NASSAR SUPRIANTO", "2026-03-02", "SENIN", "07:44:44", null],
            ["NASSAR SUPRIANTO", "2026-03-03", "SELASA", "07:52:40", "17:57:55"],
            ["NASSAR SUPRIANTO", "2026-03-04", "RABU", "07:45:22", "17:03:12"],
            ["NASSAR SUPRIANTO", "2026-03-05", "KAMIS", "07:49:39", "17:05:41"],
            ["NASSAR SUPRIANTO", "2026-03-06", "JUMAT", "07:48:03", "17:03:52"],
            ["NASSAR SUPRIANTO", "2026-03-07", "SABTU", "07:39:55", "13:04:25"],
            ["NASSAR SUPRIANTO", "2026-03-09", "SENIN", "07:46:34", null],
            ["KUSTORO", "2026-03-03", "SELASA", "07:56:30", "17:01:09"],
            ["KUSTORO", "2026-03-04", "RABU", "07:52:28", "17:04:19"],
            ["KUSTORO", "2026-03-05", "KAMIS", "07:46:06", "17:01:45"],
            ["KUSTORO", "2026-03-06", "JUMAT", "07:47:02", "17:01:37"],
            ["KUSTORO", "2026-03-07", "SABTU", "07:57:41", "13:27:41"],
            ["KUSTORO", "2026-03-09", "SENIN", "07:45:55", null],
            ["RAMLAN EFENDI", "2026-03-02", "SENIN", "07:27:57", null],
            ["RAMLAN EFENDI", "2026-03-03", "SELASA", "07:39:14", null],
            ["RAMLAN EFENDI", "2026-03-04", "RABU", "07:33:05", null],
            ["RAMLAN EFENDI", "2026-03-05", "KAMIS", "07:40:09", null],
            ["RAMLAN EFENDI", "2026-03-06", "JUMAT", "07:40:09", null],
            ["RAMLAN EFENDI", "2026-03-07", "SABTU", "07:33:04", null],
            ["RAMLAN EFENDI", "2026-03-09", "SENIN", "07:51:57", null],
            ["ARIFIN ZUHDI", "2026-03-02", "SENIN", "07:57:06", null],
            ["ARIFIN ZUHDI", "2026-03-03", "SELASA", "07:58:15", "19:23:25"],
            ["ARIFIN ZUHDI", "2026-03-04", "RABU", "07:49:18", "17:01:50"],
            ["ARIFIN ZUHDI", "2026-03-05", "KAMIS", "07:56:10", "20:28:08"],
            ["ARIFIN ZUHDI", "2026-03-06", "JUMAT", "07:54:43", "17:10:07"],
            ["ARIFIN ZUHDI", "2026-03-07", "SABTU", "07:46:57", "13:11:40"],
            ["ARIFIN ZUHDI", "2026-03-08", "MINGGU", "07:45:32", "16:02:00"],
            ["ARIFIN ZUHDI", "2026-03-09", "SENIN", "08:14:00", null],
            ["AGUS SETIAWAN", "2026-03-01", "MINGGU", "08:00:43", "16:00:17"],
            ["AGUS SETIAWAN", "2026-03-02", "SENIN", "08:00:11", null],
            ["AGUS SETIAWAN", "2026-03-03", "SELASA", "07:59:42", "17:01:06"],
            ["AGUS SETIAWAN", "2026-03-04", "RABU", "07:58:51", "17:00:44"],
            ["AGUS SETIAWAN", "2026-03-05", "KAMIS", "08:12:44", "17:01:41"],
            ["AGUS SETIAWAN", "2026-03-06", "JUMAT", "07:58:39", null],
            ["AGUS SETIAWAN", "2026-03-07", "SABTU", "07:58:39", "14:51:51"],
            ["AGUS SETIAWAN", "2026-03-09", "SENIN", "07:51:25", null],
            ["ALIFUDIN", "2026-03-01", "MINGGU", "07:53:23", "16:00:33"],
            ["ALIFUDIN", "2026-03-03", "SELASA", "18:53:25", "03:00:45"],
            ["ALIFUDIN", "2026-03-04", "RABU", "16:55:03", "03:00:54"],
            ["ALIFUDIN", "2026-03-06", "JUMAT", "16:54:31", "01:01:28"],
            ["ALIFUDIN", "2026-03-07", "SABTU", "12:55:37", "16:00:05"],
            ["ALIFUDIN", "2026-03-09", "SENIN", "07:57:01", null],
            ["KARNAHUDIN", "2026-03-02", "SENIN", "07:59:51", null],
            ["KARNAHUDIN", "2026-03-03", "SELASA", "07:58:47", null],
            ["KARNAHUDIN", "2026-03-04", "RABU", "07:58:44", null],
            ["KARNAHUDIN", "2026-03-05", "KAMIS", "07:57:42", "20:18:02"],
            ["KARNAHUDIN", "2026-03-06", "JUMAT", "07:58:43", "19:59:35"],
            ["KARNAHUDIN", "2026-03-07", "SABTU", "07:59:14", null],
            ["KARNAHUDIN", "2026-03-09", "SENIN", "07:58:17", null],
            ["ISMAN SURNDARU", "2026-03-02", "SENIN", "07:57:25", null],
            ["ISMAN SURNDARU", "2026-03-03", "SELASA", "07:57:30", "17:02:07"],
            ["ISMAN SURNDARU", "2026-03-04", "RABU", "07:56:09", "17:05:32"],
            ["ISMAN SURNDARU", "2026-03-05", "KAMIS", "07:59:00", "17:05:28"],
            ["ISMAN SURNDARU", "2026-03-07", "SABTU", "07:58:45", "13:16:21"],
            ["ISMAN SURNDARU", "2026-03-09", "SENIN", "07:59:49", null],
            ["SUHENDRI", "2026-03-03", "SELASA", "07:51:00", "17:00:15"],
            ["SUHENDRI", "2026-03-04", "RABU", "07:47:37", "17:01:33"],
            ["SUHENDRI", "2026-03-05", "KAMIS", "07:55:13", "17:00:21"],
            ["SUHENDRI", "2026-03-06", "JUMAT", "07:59:24", "17:00:33"],
            ["SUHENDRI", "2026-03-07", "SABTU", "07:49:00", "13:00:22"],
            ["SUHENDRI", "2026-03-09", "SENIN", "07:46:09", null],
            ["RISKY SAEFUL ANWAR", "2026-03-02", "SENIN", "07:33:06", null],
            ["RISKY SAEFUL ANWAR", "2026-03-03", "SELASA", "07:27:35", "17:01:03"],
            ["RISKY SAEFUL ANWAR", "2026-03-04", "RABU", "07:22:39", "17:01:12"],
            ["RISKY SAEFUL ANWAR", "2026-03-05", "KAMIS", "07:23:14", "17:01:06"],
            ["RISKY SAEFUL ANWAR", "2026-03-06", "JUMAT", "07:29:08", "17:01:12"],
            ["RISKY SAEFUL ANWAR", "2026-03-07", "SABTU", "07:30:19", "13:27:23"],
            ["RISKY SAEFUL ANWAR", "2026-03-09", "SENIN", "07:36:58", null],
            ["ATTHOUR ROHMAN", "2026-03-02", "SENIN", "07:51:30", null],
            ["ATTHOUR ROHMAN", "2026-03-03", "SELASA", "07:53:37", "17:02:01"],
            ["ATTHOUR ROHMAN", "2026-03-04", "RABU", "07:55:00", "17:03:45"],
            ["ATTHOUR ROHMAN", "2026-03-05", "KAMIS", "07:50:40", "19:15:56"],
            ["ATTHOUR ROHMAN", "2026-03-06", "JUMAT", "07:46:43", "17:02:58"],
            ["ATTHOUR ROHMAN", "2026-03-07", "SABTU", "07:52:14", "13:03:31"],
            ["ATTHOUR ROHMAN", "2026-03-09", "SENIN", "07:49:51", null],
            ["ANDI SAPUTRA", "2026-03-03", "SELASA", "16:59:03", "03:00:29"],
            ["ANDI SAPUTRA", "2026-03-04", "RABU", "17:38:32", "03:00:14"],
            ["ANDI SAPUTRA", "2026-03-05", "KAMIS", "16:51:18", "03:00:36"],
            ["ANDI SAPUTRA", "2026-03-06", "JUMAT", "16:56:04", "03:06:03"],
            ["ANDI SAPUTRA", "2026-03-07", "SABTU", "12:52:43", "16:00:08"],
            ["ANDI SAPUTRA", "2026-03-08", "MINGGU", "08:23:44", "16:00:49"],
            ["TAFIP RINANTO", "2026-03-01", "MINGGU", "07:56:27", "16:00:51"],
            ["TAFIP RINANTO", "2026-03-03", "SELASA", "18:58:20", "05:01:14"],
            ["TAFIP RINANTO", "2026-03-04", "RABU", "18:58:01", "05:02:04"],
            ["TAFIP RINANTO", "2026-03-05", "KAMIS", "18:58:36", "05:08:12"],
            ["TAFIP RINANTO", "2026-03-06", "JUMAT", "16:59:52", "05:01:41"],
            ["TAFIP RINANTO", "2026-03-07", "SABTU", "12:57:58", "16:00:02"],
            ["TAFIP RINANTO", "2026-03-09", "SENIN", "07:57:34", null],
            ["EKO PRAYITNO", "2026-03-01", "MINGGU", "07:40:01", "16:02:11"],
            ["EKO PRAYITNO", "2026-03-02", "SENIN", "07:40:14", null],
            ["EKO PRAYITNO", "2026-03-03", "SELASA", "07:38:04", "17:01:57"],
            ["EKO PRAYITNO", "2026-03-04", "RABU", "07:27:01", "17:01:58"],
            ["EKO PRAYITNO", "2026-03-05", "KAMIS", "07:36:59", "17:01:19"],
            ["EKO PRAYITNO", "2026-03-06", "JUMAT", "07:32:46", null],
            ["EKO PRAYITNO", "2026-03-07", "SABTU", "07:43:03", "13:38:22"],
            ["EKO PRAYITNO", "2026-03-09", "SENIN", "07:35:52", null],
            ["WASMAN", "2026-03-01", "MINGGU", "07:29:12", "16:00:25"],
            ["WASMAN", "2026-03-02", "SENIN", "07:42:42", null],
            ["WASMAN", "2026-03-03", "SELASA", "07:48:30", "20:15:03"],
            ["WASMAN", "2026-03-04", "RABU", "07:16:38", "17:01:54"],
            ["WASMAN", "2026-03-05", "KAMIS", "07:45:22", "17:00:34"],
            ["WASMAN", "2026-03-06", "JUMAT", "07:48:18", null],
            ["WASMAN", "2026-03-07", "SABTU", "07:39:38", "13:06:39"],
            ["WASMAN", "2026-03-08", "MINGGU", "07:46:09", "16:00:06"],
            ["WASMAN", "2026-03-09", "SENIN", "07:47:17", null],
            ["IBNU ROMADHAN", "2026-03-02", "SENIN", "07:26:20", null],
            ["IBNU ROMADHAN", "2026-03-03", "SELASA", "07:45:40", "20:16:33"],
            ["IBNU ROMADHAN", "2026-03-04", "RABU", "07:56:13", "17:03:49"],
            ["IBNU ROMADHAN", "2026-03-05", "KAMIS", "07:47:38", "20:25:51"],
            ["IBNU ROMADHAN", "2026-03-06", "JUMAT", "07:44:15", "19:17:58"],
            ["IBNU ROMADHAN", "2026-03-07", "SABTU", "07:49:34", "13:25:57"],
            ["IBNU ROMADHAN", "2026-03-09", "SENIN", "07:47:47", null],
            ["ADI BAGUS SAPUTRA", "2026-03-02", "SENIN", "08:00:13", null],
            ["ADI BAGUS SAPUTRA", "2026-03-03", "SELASA", "08:49:23", "17:07:04"],
            ["ADI BAGUS SAPUTRA", "2026-03-04", "RABU", "07:56:24", "17:04:47"],
            ["ADI BAGUS SAPUTRA", "2026-03-05", "KAMIS", "07:56:51", "17:02:18"],
            ["ADI BAGUS SAPUTRA", "2026-03-06", "JUMAT", "07:55:36", "17:00:48"],
            ["ADI BAGUS SAPUTRA", "2026-03-07", "SABTU", "07:53:25", "13:06:01"],
            ["KHAIRUL FUADI", "2026-03-02", "SENIN", "07:54:34", null],
            ["KHAIRUL FUADI", "2026-03-03", "SELASA", "07:55:19", "19:17:26"],
            ["KHAIRUL FUADI", "2026-03-04", "RABU", "07:56:34", "17:00:03"],
            ["KHAIRUL FUADI", "2026-03-05", "KAMIS", "07:54:29", "19:15:06"],
            ["KHAIRUL FUADI", "2026-03-06", "JUMAT", "07:54:27", "17:05:56"],
            ["KHAIRUL FUADI", "2026-03-07", "SABTU", "07:54:49", "13:09:40"],
            ["KHAIRUL FUADI", "2026-03-08", "MINGGU", "07:55:15", "16:00:03"],
            ["MUHAEMIN", "2026-03-02", "SENIN", "07:33:45", null],
            ["MUHAEMIN", "2026-03-03", "SELASA", "06:59:30", "17:01:44"],
            ["MUHAEMIN", "2026-03-04", "RABU", "06:53:48", "17:03:02"],
            ["MUHAEMIN", "2026-03-05", "KAMIS", "07:50:23", "17:00:55"],
            ["MUHAEMIN", "2026-03-06", "JUMAT", "07:24:45", "17:01:20"],
            ["MUHAEMIN", "2026-03-07", "SABTU", "07:05:32", "13:05:17"],
            ["MUHAEMIN", "2026-03-09", "SENIN", "07:44:02", null],
            ["SAEFUL ROHMAN", "2026-03-02", "SENIN", "07:49:06", null],
            ["SAEFUL ROHMAN", "2026-03-03", "SELASA", "07:50:27", "19:20:19"],
            ["SAEFUL ROHMAN", "2026-03-04", "RABU", "07:50:55", "17:06:05"],
            ["SAEFUL ROHMAN", "2026-03-05", "KAMIS", "07:54:12", "19:17:38"],
            ["SAEFUL ROHMAN", "2026-03-06", "JUMAT", "07:50:48", "17:04:11"],
            ["SAEFUL ROHMAN", "2026-03-08", "MINGGU", "07:45:17", "16:00:17"],
            ["UJANG WAHYUDIN", "2026-03-01", "MINGGU", "07:56:54", "16:00:45"],
            ["UJANG WAHYUDIN", "2026-03-02", "SENIN", "08:05:00", null],
            ["UJANG WAHYUDIN", "2026-03-03", "SELASA", "07:54:56", "20:17:30"],
            ["UJANG WAHYUDIN", "2026-03-04", "RABU", "07:53:34", "17:02:02"],
            ["UJANG WAHYUDIN", "2026-03-05", "KAMIS", "07:58:16", "20:17:03"],
            ["UJANG WAHYUDIN", "2026-03-06", "JUMAT", "08:01:06", null],
            ["UJANG WAHYUDIN", "2026-03-07", "SABTU", "08:01:06", "13:17:27"],
            ["UJANG WAHYUDIN", "2026-03-08", "MINGGU", "08:29:47", "16:00:11"],
            ["UJANG WAHYUDIN", "2026-03-09", "SENIN", "07:54:32", null],
            ["SEPRI MAULADI", "2026-03-02", "SENIN", "07:37:51", null],
            ["SEPRI MAULADI", "2026-03-03", "SELASA", "07:42:54", "19:15:15"],
            ["SEPRI MAULADI", "2026-03-04", "RABU", "07:42:11", "17:02:16"],
            ["SEPRI MAULADI", "2026-03-05", "KAMIS", "07:35:13", "17:09:51"],
            ["SEPRI MAULADI", "2026-03-06", "JUMAT", "07:27:34", "18:11:25"],
            ["SEPRI MAULADI", "2026-03-09", "SENIN", "07:27:07", null],
            ["NIZAR", "2026-03-02", "SENIN", "07:50:55", null],
            ["NIZAR", "2026-03-03", "SELASA", "07:56:00", "17:04:43"],
            ["NIZAR", "2026-03-04", "RABU", "07:52:24", "17:00:49"],
            ["NIZAR", "2026-03-05", "KAMIS", "07:53:55", "19:16:06"],
            ["NIZAR", "2026-03-06", "JUMAT", "07:51:27", "18:10:41"],
            ["NIZAR", "2026-03-07", "SABTU", "07:46:21", "14:30:07"],
            ["ISWANTO", "2026-03-01", "MINGGU", "07:54:01", "16:00:35"],
            ["ISWANTO", "2026-03-03", "SELASA", "18:53:42", "05:02:53"],
            ["ISWANTO", "2026-03-04", "RABU", "16:55:51", "03:02:57"],
            ["ISWANTO", "2026-03-05", "KAMIS", "18:53:59", "03:00:46"],
            ["ISWANTO", "2026-03-06", "JUMAT", "16:55:35", "03:02:01"],
            ["ISWANTO", "2026-03-07", "SABTU", "12:48:44", "16:01:04"],
            ["ISWANTO", "2026-03-09", "SENIN", "07:54:42", null],
            ["HABIB MAULANA N", "2026-03-02", "SENIN", "07:23:41", null],
            ["HABIB MAULANA N", "2026-03-03", "SELASA", "07:19:08", "17:01:01"],
            ["HABIB MAULANA N", "2026-03-04", "RABU", "07:22:10", "17:08:22"],
            ["HABIB MAULANA N", "2026-03-05", "KAMIS", "07:41:24", "17:02:46"],
            ["HABIB MAULANA N", "2026-03-06", "JUMAT", "07:26:18", "17:01:04"],
            ["HABIB MAULANA N", "2026-03-07", "SABTU", "07:19:52", "13:05:06"],
            ["LULU ISLAMIYAH", "2026-03-02", "SENIN", "07:33:02", null],
            ["LULU ISLAMIYAH", "2026-03-03", "SELASA", "07:27:31", "17:04:09"],
            ["LULU ISLAMIYAH", "2026-03-04", "RABU", "07:22:32", null],
            ["LULU ISLAMIYAH", "2026-03-05", "KAMIS", "07:23:08", "17:02:40"],
            ["LULU ISLAMIYAH", "2026-03-06", "JUMAT", "07:29:03", "17:01:51"],
            ["LULU ISLAMIYAH", "2026-03-07", "SABTU", "07:28:12", "13:27:31"],
            ["LULU ISLAMIYAH", "2026-03-09", "SENIN", "07:36:46", null],
            ["AAM SETIAWAN", "2026-03-01", "MINGGU", "08:13:00", "16:01:57"],
            ["AAM SETIAWAN", "2026-03-03", "SELASA", "18:56:11", "05:02:23"],
            ["AAM SETIAWAN", "2026-03-04", "RABU", "16:55:17", "03:02:17"],
            ["AAM SETIAWAN", "2026-03-05", "KAMIS", "18:59:11", "03:00:39"],
            ["AAM SETIAWAN", "2026-03-06", "JUMAT", "17:02:21", "03:01:48"],
            ["AAM SETIAWAN", "2026-03-07", "SABTU", "12:55:05", "16:01:20"],
            ["AAM SETIAWAN", "2026-03-09", "SENIN", "07:50:57", null],
            ["ZAENUDIN", "2026-03-02", "SENIN", "07:48:49", null],
            ["ZAENUDIN", "2026-03-03", "SELASA", "07:49:52", "17:00:42"],
            ["ZAENUDIN", "2026-03-04", "RABU", "07:52:39", "17:01:43"],
            ["ZAENUDIN", "2026-03-05", "KAMIS", "07:40:20", "17:00:41"],
            ["ZAENUDIN", "2026-03-06", "JUMAT", "07:47:28", "17:00:57"],
            ["ZAENUDIN", "2026-03-07", "SABTU", "07:36:56", "13:00:53"],
            ["ZAENUDIN", "2026-03-09", "SENIN", "07:48:07", null],
            ["ZAINAL KABIB", "2026-03-02", "SENIN", "08:00:50", "13:10:25"],
            ["ZAINAL KABIB", "2026-03-03", "SELASA", "07:53:34", "19:18:58"],
            ["ZAINAL KABIB", "2026-03-04", "RABU", "07:55:39", "17:08:47"],
            ["ZAINAL KABIB", "2026-03-05", "KAMIS", "07:56:57", "19:17:59"],
            ["ZAINAL KABIB", "2026-03-06", "JUMAT", "07:54:24", "17:03:15"],
            ["ZAINAL KABIB", "2026-03-07", "SABTU", "07:54:15", "13:01:39"],
            ["ZAINAL KABIB", "2026-03-08", "MINGGU", "07:47:14", "16:00:59"],
            ["JULI", "2026-03-02", "SENIN", "07:39:42", null],
            ["JULI", "2026-03-03", "SELASA", "07:35:33", "17:00:50"],
            ["JULI", "2026-03-04", "RABU", "07:34:16", "17:02:52"],
            ["JULI", "2026-03-05", "KAMIS", "07:40:30", "17:00:25"],
            ["JULI", "2026-03-06", "JUMAT", "07:35:42", "17:00:27"],
            ["JULI", "2026-03-07", "SABTU", "07:36:26", "13:03:51"],
            ["IMAM", "2026-03-02", "SENIN", "07:59:14", null],
            ["IMAM", "2026-03-03", "SELASA", "08:10:06", "19:16:23"],
            ["IMAM", "2026-03-04", "RABU", "07:58:57", "17:00:47"],
            ["IMAM", "2026-03-05", "KAMIS", "07:58:12", "17:02:05"],
            ["IMAM", "2026-03-06", "JUMAT", "08:01:32", "19:16:44"],
            ["IMAM", "2026-03-07", "SABTU", "08:04:06", "13:13:56"],
            ["IMAM", "2026-03-09", "SENIN", "08:00:04", null],
            ["AGUNG WIRECUT", "2026-03-02", "SENIN", "07:19:06", null],
            ["AGUNG WIRECUT", "2026-03-03", "SELASA", "07:26:17", "17:01:48"],
            ["AGUNG WIRECUT", "2026-03-04", "RABU", "07:26:15", "17:00:18"],
            ["AGUNG WIRECUT", "2026-03-05", "KAMIS", "07:28:25", "17:00:13"],
            ["AGUNG WIRECUT", "2026-03-06", "JUMAT", "07:27:42", "17:00:44"],
            ["AGUNG WIRECUT", "2026-03-07", "SABTU", "07:28:03", "13:01:16"],
            ["AGUNG WIRECUT", "2026-03-09", "SENIN", "07:35:37", null],
            ["ROVIN", "2026-03-02", "SENIN", "06:50:51", null],
            ["ROVIN", "2026-03-03", "SELASA", "07:00:22", "17:00:46"],
            ["ROVIN", "2026-03-04", "RABU", "06:45:46", "17:06:57"],
            ["ROVIN", "2026-03-05", "KAMIS", "06:55:10", "17:01:01"],
            ["ROVIN", "2026-03-06", "JUMAT", "06:52:49", null],
            ["ROVIN", "2026-03-07", "SABTU", "07:00:43", null],
            ["ROVIN", "2026-03-09", "SENIN", "06:56:21", null],
            ["WIWIN", "2026-03-02", "SENIN", "07:50:40", null],
            ["WIWIN", "2026-03-03", "SELASA", "07:55:34", "17:04:05"],
            ["WIWIN", "2026-03-04", "RABU", "07:30:03", "19:29:12"],
            ["WIWIN", "2026-03-05", "KAMIS", "07:45:08", "17:06:08"],
            ["WIWIN", "2026-03-06", "JUMAT", "07:41:39", "17:03:40"],
            ["WIWIN", "2026-03-07", "SABTU", "07:55:52", "13:23:21"],
            ["WIWIN", "2026-03-09", "SENIN", "07:44:15", null],
            ["SYAEBUDI", "2026-03-03", "SELASA", "18:53:14", "05:08:25"],
            ["SYAEBUDI", "2026-03-04", "RABU", "16:52:27", "01:20:23"],
            ["SYAEBUDI", "2026-03-05", "KAMIS", "18:46:58", "05:04:54"],
            ["SYAEBUDI", "2026-03-06", "JUMAT", "16:52:34", "05:01:45"],
            ["SYAEBUDI", "2026-03-07", "SABTU", "12:53:27", "16:00:13"],
            ["SYAEBUDI", "2026-03-09", "SENIN", "07:14:00", null],
            ["IRAWANTO", "2026-03-02", "SENIN", "07:55:13", null],
            ["IRAWANTO", "2026-03-03", "SELASA", "07:59:44", "19:29:27"],
            ["IRAWANTO", "2026-03-04", "RABU", "07:55:29", "19:17:28"],
            ["IRAWANTO", "2026-03-05", "KAMIS", "07:55:17", "19:20:39"],
            ["IRAWANTO", "2026-03-07", "SABTU", "07:56:45", "13:53:23"],
            ["IRAWANTO", "2026-03-08", "MINGGU", "07:59:19", "16:00:41"],
            ["IRFAN", "2026-03-02", "SENIN", "07:38:29", null],
            ["IRFAN", "2026-03-03", "SELASA", "07:56:59", "17:58:24"],
            ["IRFAN", "2026-03-04", "RABU", "06:30:46", "17:00:38"],
            ["IRFAN", "2026-03-05", "KAMIS", "07:34:58", "17:00:16"],
            ["IRFAN", "2026-03-06", "JUMAT", "06:47:56", "17:03:56"],
            ["IRFAN", "2026-03-07", "SABTU", "07:31:47", "15:03:58"],
            ["IRFAN", "2026-03-09", "SENIN", "07:02:55", null],
            ["NOSITA ATMANEGARA", "2026-03-02", "SENIN", "07:38:01", null],
            ["NOSITA ATMANEGARA", "2026-03-03", "SELASA", "07:40:18", "17:00:32"],
            ["NOSITA ATMANEGARA", "2026-03-04", "RABU", "07:39:42", "17:01:04"],
            ["NOSITA ATMANEGARA", "2026-03-05", "KAMIS", "07:38:47", "17:00:32"],
            ["NOSITA ATMANEGARA", "2026-03-06", "JUMAT", "07:33:13", "17:00:55"],
            ["NOSITA ATMANEGARA", "2026-03-07", "SABTU", "07:34:30", "13:00:57"],
            ["NOSITA ATMANEGARA", "2026-03-09", "SENIN", "07:39:17", null],
            ["HENDI IRAWAN", "2026-03-02", "SENIN", "07:48:56", null],
            ["HENDI IRAWAN", "2026-03-03", "SELASA", "08:01:18", "17:01:13"],
            ["HENDI IRAWAN", "2026-03-04", "RABU", "07:52:21", "17:00:44"],
            ["HENDI IRAWAN", "2026-03-05", "KAMIS", "07:54:04", "17:01:22"],
            ["HENDI IRAWAN", "2026-03-06", "JUMAT", "07:49:33", null],
            ["HENDI IRAWAN", "2026-03-07", "SABTU", "07:50:30", "13:02:34"],
            ["HENDI IRAWAN", "2026-03-09", "SENIN", "07:48:04", null],
            ["FAROYI", "2026-03-02", "SENIN", "07:58:51", null],
            ["FAROYI", "2026-03-03", "SELASA", "07:56:34", "18:03:41"],
            ["FAROYI", "2026-03-04", "RABU", "08:00:26", null],
            ["FAROYI", "2026-03-05", "KAMIS", "07:56:42", "20:30:29"],
            ["FAROYI", "2026-03-06", "JUMAT", "07:57:33", "19:25:42"],
            ["FAROYI", "2026-03-07", "SABTU", "07:51:12", "15:30:22"],
            ["FAROYI", "2026-03-08", "MINGGU", "07:57:29", "16:06:49"],
            ["FAROYI", "2026-03-09", "SENIN", "07:56:42", null],
            ["M.RISKY FAISAL", "2026-03-01", "MINGGU", "07:55:31", "16:00:54"],
            ["M.RISKY FAISAL", "2026-03-02", "SENIN", "07:56:41", null],
            ["M.RISKY FAISAL", "2026-03-03", "SELASA", "07:57:17", "20:15:58"],
            ["M.RISKY FAISAL", "2026-03-04", "RABU", "07:52:44", "17:01:28"],
            ["M.RISKY FAISAL", "2026-03-05", "KAMIS", "07:55:55", "20:16:50"],
            ["M.RISKY FAISAL", "2026-03-06", "JUMAT", "07:57:44", "20:15:23"],
            ["M.RISKY FAISAL", "2026-03-07", "SABTU", "07:59:00", "13:11:26"],
            ["M.RISKY FAISAL", "2026-03-08", "MINGGU", "07:56:21", "16:01:19"],
            ["M.RISKY FAISAL", "2026-03-09", "SENIN", "07:56:20", null],
            ["NURUL AMALIA", "2026-03-02", "SENIN", "07:46:19", null],
            ["NURUL AMALIA", "2026-03-03", "SELASA", "07:51:57", "17:03:33"],
            ["NURUL AMALIA", "2026-03-04", "RABU", "07:48:44", null],
            ["NURUL AMALIA", "2026-03-05", "KAMIS", "07:40:48", "17:02:44"],
            ["NURUL AMALIA", "2026-03-06", "JUMAT", "07:41:45", "17:03:10"],
            ["NURUL AMALIA", "2026-03-07", "SABTU", "07:47:05", "13:01:19"],
            ["NURUL AMALIA", "2026-03-09", "SENIN", "07:44:59", null],
            ["TOSIN", "2026-03-02", "SENIN", "07:24:49", null],
            ["TOSIN", "2026-03-03", "SELASA", "07:34:55", "17:00:56"],
            ["TOSIN", "2026-03-04", "RABU", "07:31:57", "17:09:46"],
            ["TOSIN", "2026-03-05", "KAMIS", "07:24:57", "17:00:38"],
            ["TOSIN", "2026-03-06", "JUMAT", "07:18:12", "17:00:42"],
            ["TOSIN", "2026-03-07", "SABTU", "07:27:58", "13:00:49"],
            ["TOSIN", "2026-03-09", "SENIN", "07:21:52", null],
            ["SYARIF", "2026-03-01", "MINGGU", "07:24:47", "16:12:09"],
            ["SYARIF", "2026-03-02", "SENIN", "06:37:40", null],
            ["SYARIF", "2026-03-03", "SELASA", "06:49:16", "20:30:51"],
            ["SYARIF", "2026-03-04", "RABU", "06:41:17", "20:57:14"],
            ["SYARIF", "2026-03-05", "KAMIS", "06:49:12", "20:30:03"],
            ["SYARIF", "2026-03-06", "JUMAT", "06:48:36", "20:31:18"],
            ["SYARIF", "2026-03-07", "SABTU", "06:45:34", "16:03:18"],
            ["SYARIF", "2026-03-08", "MINGGU", "06:52:08", "16:16:34"],
            ["SYARIF", "2026-03-09", "SENIN", "06:50:07", null],
            ["RUDI", "2026-03-02", "SENIN", "07:36:12", null],
            ["RUDI", "2026-03-03", "SELASA", "07:38:09", "19:20:45"],
            ["RUDI", "2026-03-04", "RABU", "07:35:13", "17:03:27"],
            ["RUDI", "2026-03-06", "JUMAT", "07:34:44", "17:02:55"],
            ["RUDI", "2026-03-07", "SABTU", "07:51:29", "13:02:44"],
            ["RUDI", "2026-03-08", "MINGGU", "07:49:22", "16:00:46"],
            ["RUDI", "2026-03-09", "SENIN", "08:30:00", null],
        ];

        $employees = Employee::all()->keyBy(function($e) {
            return strtoupper(trim($e->name));
        });

        $inserted = 0;
        $notFound = [];

        foreach ($rawData as [$nama, $tanggal, $hari, $scan1, $scan2]) {
            $employee = $employees->get($nama);
            if (!$employee) {
                $notFound[] = $nama;
                continue;
            }

            $date    = Carbon::parse($tanggal);
            $dateStr = $date->format('Y-m-d');
            $dow     = $date->dayOfWeek; // 0=Sun, 6=Sat

            $scan1Hour = (int) substr($scan1, 0, 2);
            $isMalam   = $scan1Hour >= 16;

            // Hitung leave_time
            if ($scan2) {
                $scan2Hour = (int) substr($scan2, 0, 2);
                // Kalau masuk malam dan scan2 dini hari -> next day
                if ($isMalam && $scan2Hour < 12) {
                    $leaveTime = $date->copy()->addDay()->format('Y-m-d') . ' ' . $scan2;
                } else {
                    $leaveTime = $dateStr . ' ' . $scan2;
                }
            } else {
                $leaveTime = null;
            }

            // Assign schedule_employees
            if ($dow === 0) {
                $schedIds = [5, 6]; // holiday/lembur
            } elseif ($dow === 6) {
                $schedIds = [3, 4]; // sabtu
            } else {
                $schedIds = [1, 2]; // weekday
            }
            $schedId = $isMalam ? $schedIds[1] : $schedIds[0];

            $exists = \DB::table('schedule_employees')
                ->where('emp_id', $employee->id)
                ->where('schedule_id', $schedId)
                ->exists();
            if (!$exists) {
                \DB::table('schedule_employees')->insert([
                    'emp_id'      => $employee->id,
                    'schedule_id' => $schedId,
                ]);
            }

            Check::create([
                'emp_id'          => $employee->id,
                'attendance_time' => $dateStr . ' ' . $scan1,
                'leave_time'      => $leaveTime,
            ]);

            $inserted++;
        }

        $this->command->info("Inserted: $inserted records");
        if ($notFound) {
            $unique = array_unique($notFound);
            sort($unique);
            $this->command->warn("Not found (" . count($unique) . "): " . implode(', ', $unique));
        }
    }
}