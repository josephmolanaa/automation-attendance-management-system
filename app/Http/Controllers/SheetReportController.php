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

    public function ajaxData(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $employees    = Employee::all();
        $allSchedules = Schedule::all();

        // Semua checks bulan & tahun ini
        $checks = Check::whereYear('attendance_time', $tahun)
            ->whereMonth('attendance_time', $bulan)
            ->get()
            ->groupBy('emp_id');

        // Semua izin/cuti bulan & tahun ini
        $leaves = IzinDanCuti::whereYear('leave_date', $tahun)
            ->whereMonth('leave_date', $bulan)
            ->get()
            ->groupBy('emp_id');

        $data = [];

        foreach ($employees as $employee) {
            $empChecks = $checks->get($employee->id, collect());
            $empLeaves = $leaves->get($employee->id, collect());

            // Group checks by date
            $checksByDate = $empChecks->groupBy(function($c) {
                return Carbon::parse($c->attendance_time)->format('Y-m-d');
            });

            // Group leaves by date
            $leavesByDate = $empLeaves->keyBy(function($l) {
                return Carbon::parse($l->leave_date)->format('Y-m-d');
            });

            // Loop setiap hari dalam bulan
            $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr  = Carbon::createFromDate($tahun, $bulan, $d)->format('Y-m-d');
                $dateObj  = Carbon::parse($dateStr);
                $dayName  = $dateObj->locale('id')->dayName;
                $dayOfWeek = $dateObj->dayOfWeek; // 0=Minggu, 6=Sabtu

                $dayChecks = $checksByDate->get($dateStr, collect());
                $leave     = $leavesByDate->get($dateStr);

                // Scan 1 & Scan 2
                $scan1 = $dayChecks->sortBy('attendance_time')->first();
                $scan2 = $dayChecks->sortBy('attendance_time')->last();

                $scan1Time = $scan1 && $scan1->attendance_time
                    ? Carbon::parse($scan1->attendance_time)->format('H:i:s') : '-';
                $scan2Time = ($scan1 && $scan1->leave_time)
                    ? Carbon::parse($scan1->leave_time)->format('H:i:s') : '-';

                $scan3Time = ($scan1 && $scan1->second_leave_time)
                    ? Carbon::parse($scan1->second_leave_time)->format('H:i:s') : '-';

                // Hitung overtime untuk Normal/Double/Minggu
                $normal = 0; $double = 0; $minggu = 0;
                $izinCuti = '-';

                if ($leave) {
                    $izinCuti = ucfirst($leave->reason ?? 'Izin');
                } elseif ($scan1 && $scan1->leave_time) {
                    $scanIn  = Carbon::parse($scan1->attendance_time);
                    $scanOut = Carbon::parse($scan1->leave_time);
                    $dateStr2 = $scanIn->format('Y-m-d');

                    $dayType    = HolidayService::getDayType($dateStr2);
                    $isSunday   = $dayOfWeek === 0;

                    // Cari matched schedule
                    $matchedSchedule = null;
                    $override = HolidayOverride::where('date', $dateStr2)->first();
                    if ($override && $override->schedule_id) {
                        $matchedSchedule = Schedule::find($override->schedule_id);
                    } else {
                        $scanHour = (int) $scanIn->format('H');
                        foreach ($allSchedules as $schedule) {
                            $sDayType = $schedule->day_type ?? 'weekday';
                            $dayMatch = match($sDayType) {
                                'saturday' => $dayType === 'saturday',
                                'holiday'  => $dayType === 'holiday',
                                'weekday'  => $dayType === 'weekday',
                                default    => false,
                            };
                            if (!$dayMatch) continue;
                            $schedHour = (int) Carbon::parse($schedule->time_in)->format('H');
                            $diff = min(abs($scanHour - $schedHour), 24 - abs($scanHour - $schedHour));
                            if ($diff <= 3) { $matchedSchedule = $schedule; break; }
                        }
                    }

                    if ($matchedSchedule) {
                        $schedOut = Carbon::parse($dateStr2 . ' ' . $matchedSchedule->time_out);
                        if ($schedOut->lt(Carbon::parse($dateStr2 . ' ' . $matchedSchedule->time_in))) {
                            $schedOut->addDay();
                        }
                        $diffMin = $schedOut->diffInMinutes($scanOut, false);

                        if ($isSunday) {
                            $minggu = 1;
                        } elseif ($diffMin > 15) {
                            $totalOvertimeHours = floor($diffMin / 60);
                            if ($totalOvertimeHours <= 3) {
                                $normal = $totalOvertimeHours;
                            } else {
                                $normal = 3;
                                $double = $totalOvertimeHours - 3;
                            }
                        }
                    }
                }

                // Skip hari tanpa data sama sekali
                if ($scan1Time === '-' && !$leave) continue;

                $data[] = [
                    'emp_id'   => $employee->emp_id ?? $employee->id,
                    'name'     => $employee->name,
                    'position' => $employee->position ?? '-',
                    'hari'     => ucfirst($dayName),
                    'tanggal'  => $dateStr,
                    'scan_1'   => $scan1Time,
                    'scan_2'   => $scan2Time,
                    'scan_3'   => $scan3Time,
                    'normal'   => $normal ?: 0,
                    'double'   => $double ?: 0,
                    'minggu'   => $minggu ?: 0,
                    'izin_cuti'=> $izinCuti,
                ];
            }
        }

        // Sort by tanggal desc
        usort($data, fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));

        return response()->json(['data' => $data]);
    }

    public function export(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $filename = 'Sheet_Report_' . $tahun . '_' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '.xlsx';
        return Excel::download(new SheetReportExport($bulan, $tahun), $filename);
    }
}