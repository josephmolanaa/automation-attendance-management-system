<?php

namespace App\Http\Controllers;

use App\Models\Check;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\HolidayOverride;
use App\Models\IzinDanCuti;
use App\Services\HolidayService;
use Illuminate\Http\Request;
use App\Exports\SheetReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class SheetReportController extends Controller
{
    public function index()
    {
        return view('admin.sheet-report');
    }

    /**
     * ============================================================
     * AJAX endpoint untuk DataTables sheet-report
     * ============================================================
     *
     * Kolom output:
     *   emp_id, name, position, hari, tanggal,
     *   scan_1, scan_2, scan_3,
     *   normal, double, minggu, izin_cuti
     *
     * Logic scan per tanggal per karyawan:
     *
     *   KASUS 1 — Shift pagi / normal (1 sesi):
     *     scan_1 = attendance_time sesi 1
     *     scan_2 = leave_time sesi 1
     *     scan_3 = -
     *
     *   KASUS 2 — Double shift (2 sesi dalam 1 hari, misal Sabtu):
     *     scan_1 = leave_time sesi overnight (dari shift Jumat malam)
     *     scan_2 = attendance_time sesi 2 (shift Sabtu)
     *     scan_3 = leave_time sesi 2
     *
     *   KASUS 3 — Shift 2 Friday (di baris tanggal Jumat):
     *     scan_1 = attendance_time shift malam Jumat
     *     scan_2 = - (leave_time ada di Sabtu)
     *     scan_3 = -
     *
     * Overnight detection:
     *   Jika leave_time jatuh di tanggal berbeda dari attendance_time,
     *   maka leave_time tersebut ditampilkan di baris tanggal leave_time (bukan attendance_time).
     * ============================================================
     */
    public function ajaxData(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $employees    = Employee::all();
        $allSchedules = Schedule::all();

        // Ambil semua checks bulan ini + overnight dari bulan sebelumnya
        // (ambil satu bulan penuh + 3 hari sebelum untuk handle overnight)
        $startDate = Carbon::createFromDate($tahun, $bulan, 1)->subDays(3)->startOfDay();
        $endDate   = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->endOfDay();

        $allChecks = Check::whereBetween('attendance_time', [$startDate, $endDate])
            ->orWhereBetween('leave_time', [
                Carbon::createFromDate($tahun, $bulan, 1)->startOfDay(),
                $endDate,
            ])
            ->orderBy('attendance_time', 'asc')
            ->get()
            ->groupBy('emp_id');

        // Semua izin/cuti bulan ini
        $allLeaves = IzinDanCuti::whereYear('leave_date', $tahun)
            ->whereMonth('leave_date', $bulan)
            ->get()
            ->groupBy('emp_id');

        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
        $data = [];

        foreach ($employees as $employee) {
            $empChecks = $allChecks->get($employee->id, collect());
            $empLeaves = $allLeaves->get($employee->id, collect());

            // Buat lookup izin/cuti by date
            $leaveByDate = $empLeaves->keyBy(function ($l) {
                return Carbon::parse($l->leave_date)->format('Y-m-d');
            });

            // Loop setiap hari dalam bulan
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr  = Carbon::createFromDate($tahun, $bulan, $d)->format('Y-m-d');
                $dateObj  = Carbon::parse($dateStr);
                $dayName  = $dateObj->locale('id')->isoFormat('dddd');
                $dayOfWeek = $dateObj->dayOfWeek; // 0=Minggu, 6=Sabtu

                // ── Kumpulkan sesi yang relevan untuk tanggal ini ──
                //
                // Sesi "milik" tanggal ini = checks dimana:
                //   A. attendance_time di tanggal ini (sesi normal / shift malam mulai hari ini)
                //   B. leave_time di tanggal ini tapi attendance_time di hari sebelumnya (overnight)

                $sessionsThisDay = collect();

                foreach ($empChecks as $check) {
                    $attDate   = $check->attendance_time
                        ? Carbon::parse($check->attendance_time)->format('Y-m-d')
                        : null;
                    $leaveDate = $check->leave_time
                        ? Carbon::parse($check->leave_time)->format('Y-m-d')
                        : null;

                    if ($attDate === $dateStr) {
                        // Sesi yang mulai hari ini
                        $sessionsThisDay->push([
                            'type'       => 'normal',
                            'check'      => $check,
                            'att_time'   => $check->attendance_time,
                            'leave_time' => $check->leave_time,
                        ]);
                    } elseif ($leaveDate === $dateStr && $attDate !== $dateStr) {
                        // Overnight: sesi mulai kemarin, berakhir hari ini
                        $sessionsThisDay->push([
                            'type'       => 'overnight_end',
                            'check'      => $check,
                            'att_time'   => null, // mulai kemarin, tidak ditampilkan di baris ini
                            'leave_time' => $check->leave_time,
                        ]);
                    }
                }

                $izinCutiData = $leaveByDate->get($dateStr);
                $izinCuti     = '-';
                if ($izinCutiData) {
                    $izinCuti = ucfirst($izinCutiData->reason ?? 'Izin');
                }

                // Skip hari tanpa data sama sekali
                if ($sessionsThisDay->isEmpty() && !$izinCutiData) {
                    continue;
                }

                // ── Susun scan_1, scan_2, scan_3 ──
                //
                // Urutan prioritas tampilan:
                //   1. Kalau ada overnight_end → scan_1 = leave_time overnight
                //   2. Sesi normal pertama → scan_1 (atau scan_2 jika overnight sudah pakai scan_1)
                //   3. Sesi normal kedua → scan berikutnya

                $scan1 = '-';
                $scan2 = '-';
                $scan3 = '-';

                $overnightEnd = $sessionsThisDay->firstWhere('type', 'overnight_end');
                $normalSessions = $sessionsThisDay->where('type', 'normal')->values();

                if ($overnightEnd) {
                    // scan_1 = time_out dari shift semalam
                    $scan1 = Carbon::parse($overnightEnd['leave_time'])->format('H:i:s');

                    // Sesi normal hari ini (shift sabtu / shift pagi)
                    $sesi1 = $normalSessions->get(0);
                    if ($sesi1) {
                        $scan2 = $sesi1['att_time']
                            ? Carbon::parse($sesi1['att_time'])->format('H:i:s')
                            : '-';
                        $scan3 = $sesi1['leave_time']
                            ? Carbon::parse($sesi1['leave_time'])->format('H:i:s')
                            : '-';
                    }
                } else {
                    // Tidak ada overnight — sesi normal biasa
                    $sesi1 = $normalSessions->get(0);
                    $sesi2 = $normalSessions->get(1);

                    if ($sesi1) {
                        $scan1 = $sesi1['att_time']
                            ? Carbon::parse($sesi1['att_time'])->format('H:i:s')
                            : '-';
                        $scan2 = $sesi1['leave_time']
                            ? Carbon::parse($sesi1['leave_time'])->format('H:i:s')
                            : '-';
                    }

                    if ($sesi2) {
                        $scan3 = $sesi2['att_time']
                            ? Carbon::parse($sesi2['att_time'])->format('H:i:s')
                            : '-';
                        // leave_time sesi2 tidak ada kolom, tapi bisa ditambah nanti
                    }
                }

                // ── Hitung Normal / Double / Minggu ──
                $normal = 0;
                $double = 0;
                $minggu = 0;

                $dayType = HolidayService::getDayType($dateStr);
                $isFriday = $dayOfWeek === Carbon::FRIDAY && $dayType === 'weekday';

                if ($dayOfWeek === 0 || $dayType === 'holiday') {
                    // Hari Minggu / tanggal merah → hitung sebagai minggu
                    if ($sessionsThisDay->isNotEmpty()) {
                        $minggu = 1;
                    }
                } else {
                    // Hitung overtime dari sesi normal pertama
                    $sesi1 = $normalSessions->get(0);
                    if ($sesi1 && $sesi1['att_time'] && $sesi1['leave_time']) {
                        $scanIn  = Carbon::parse($sesi1['att_time']);
                        $scanOut = Carbon::parse($sesi1['leave_time']);

                        // Deteksi schedule
                        $matchedSchedule = $this->detectSchedule(
                            $allSchedules, $dateStr, $dayType, $isFriday, $dayOfWeek,
                            (int) $scanIn->format('H')
                        );

                        if ($matchedSchedule) {
                            $schedOut = Carbon::parse($dateStr . ' ' . $matchedSchedule->time_out);
                            // Handle overnight schedule
                            if ($schedOut->lt(Carbon::parse($dateStr . ' ' . $matchedSchedule->time_in))) {
                                $schedOut->addDay();
                            }
                            $diffMin = $schedOut->diffInMinutes($scanOut, false);

                            if ($diffMin > 15) {
                                $totalHours = floor($diffMin / 60);
                                if ($totalHours <= 3) {
                                    $normal = $totalHours;
                                } else {
                                    $normal = 3;
                                    $double = $totalHours - 3;
                                }
                            }
                        }
                    }
                }

                $data[] = [
                    'emp_id'    => $employee->emp_id ?? $employee->id,
                    'name'      => $employee->name,
                    'position'  => $employee->position ?? '-',
                    'hari'      => $dayName,
                    'tanggal'   => $dateStr,
                    'scan_1'    => $scan1,
                    'scan_2'    => $scan2,
                    'scan_3'    => $scan3,
                    'normal'    => $normal ?: '-',
                    'double'    => $double ?: '-',
                    'minggu'    => $minggu ?: '-',
                    'izin_cuti' => $izinCuti,
                ];
            }
        }

        // Sort by tanggal desc lalu emp_id
        usort($data, function ($a, $b) {
            $cmp = strcmp($b['tanggal'], $a['tanggal']);
            return $cmp !== 0 ? $cmp : strcmp($a['name'], $b['name']);
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Detect schedule yang cocok untuk tanggal & jam scan tertentu
     */
    private function detectSchedule($allSchedules, $dateStr, $dayType, $isFriday, $dayOfWeek, $scanHour)
    {
        $override = HolidayOverride::where('date', $dateStr)->first();
        if ($override && $override->schedule_id) {
            return Schedule::find($override->schedule_id);
        }

        $isSaturday = $dayOfWeek === 6;
        $isHoliday  = $dayType === 'holiday';
        $isWeekday  = $dayType === 'weekday';

        $matched = null;

        foreach ($allSchedules as $schedule) {
            $sDayType = $schedule->day_type ?? 'weekday';
            $dayMatch = match ($sDayType) {
                'friday'   => $isFriday,
                'saturday' => $isSaturday,
                'holiday'  => $isHoliday,
                'weekday'  => $isWeekday && !$isFriday,
                default    => false,
            };
            if (!$dayMatch) continue;

            $schedHour = (int) Carbon::parse($schedule->time_in)->format('H');
            $diff      = min(abs($scanHour - $schedHour), 24 - abs($scanHour - $schedHour));
            if ($diff <= 3) {
                $matched = $schedule;
                break;
            }
        }

        if (!$matched) {
            // Fallback
            if ($isFriday) {
                $isNight = $scanHour >= 16;
                $matched = $isNight
                    ? $allSchedules->where('slug', 'SHIFT_2_FRIDAY')->first()
                    : $allSchedules->where('slug', 'SHIFT_1_WEEKDAY')->first();
            } else {
                $matched = $allSchedules->where('day_type', $dayType)->first();
            }
        }

        return $matched;
    }

    public function export(Request $request)
    {
        $bulan    = $request->bulan ?? date('m');
        $tahun    = $request->tahun ?? date('Y');
        $filename = 'Sheet_Report_' . $tahun . '_' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '.xlsx';
        return Excel::download(new SheetReportExport($bulan, $tahun), $filename);
    }
}