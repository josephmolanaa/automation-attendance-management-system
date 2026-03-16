<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Employee;
use App\Models\Latetime;
use App\Models\Attendance;
use App\Models\Check;
use App\Models\Schedule;
use App\Models\HolidayOverride;
use App\Services\HolidayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AttendanceEmp;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('admin.attendance');
    }

    /**
     * AJAX endpoint untuk DataTables
     */
    public function ajaxData(Request $request)
    {
        $query = Check::with(['employee'])->orderBy('attendance_time', 'desc');

        // Filter bulan
        if ($request->bulan) {
            $query->whereMonth('attendance_time', $request->bulan)
                  ->orWhereMonth('leave_time', $request->bulan);
        }
        // Filter tahun
        if ($request->tahun) {
            $query->whereYear('attendance_time', $request->tahun)
                  ->orWhereYear('leave_time', $request->tahun);
        }
        // Filter dari tanggal
        if ($request->dari) {
            $query->whereDate('attendance_time', '>=', $request->dari);
        }
        // Filter sampai tanggal
        if ($request->sampai) {
            $query->whereDate('attendance_time', '<=', $request->sampai);
        }

        $checks = $query->get();

        $allSchedules = Schedule::all();

        $data = $checks->map(function ($check) use ($allSchedules) {
            $scanIn  = $check->attendance_time ? Carbon::parse($check->attendance_time) : null;
            $scanOut = $check->leave_time      ? Carbon::parse($check->leave_time)      : null;
            $refTime = $scanIn ?? $scanOut ?? now();
            $dateStr = $refTime->format('Y-m-d');

            $dayType    = HolidayService::getDayType($dateStr);
            $isFriday   = Carbon::parse($dateStr)->dayOfWeek === Carbon::FRIDAY && $dayType === 'weekday';
            $isSaturday = $dayType === 'saturday';
            $isHoliday  = $dayType === 'holiday';
            $isWeekday  = $dayType === 'weekday';

            $matchedSchedule = null;
            $scanHour = $scanIn ? (int) $scanIn->format('H') : null;

            $override = HolidayOverride::where('date', $dateStr)->first();

            if ($override && $override->schedule_id) {
                $matchedSchedule = Schedule::find($override->schedule_id);
            } else {
                foreach ($allSchedules as $schedule) {
                    $sDayType = $schedule->day_type ?? 'weekday';
                    $dayMatch = match($sDayType) {
                        'friday'   => $isFriday,
                        'saturday' => $isSaturday,
                        'holiday'  => $isHoliday,
                        'weekday'  => $isWeekday && !$isFriday,
                        default    => false,
                    };
                    if (!$dayMatch) continue;

                    if ($scanHour !== null) {
                        $schedHour = (int) Carbon::parse($schedule->time_in)->format('H');
                        $diff = abs($scanHour - $schedHour);
                        $diff = min($diff, 24 - $diff);
                        if ($diff <= 3) {
                            $matchedSchedule = $schedule;
                            break;
                        }
                    } else {
                        $matchedSchedule = $schedule;
                        break;
                    }
                }
                if (!$matchedSchedule) {
                   if ($isFriday){
                    $isNight = $scanHour !== null && $scanHour >= 16;
                    $matchedSchedule = $isNight
                        ? $allSchedules->where('slug', 'SHIFT_2_FRIDAY')->first()
                        : $allSchedules->where('slug', 'SHIFT_1_WEEKDAY')->first();
                   } else {
                    $matchedSchedule = $allSchedules->where('day_type', $dayType)->first();
                }
            }
        }

            $shiftSlug = optional($matchedSchedule)->slug ?? '-';

            $shiftColors = [
                'SHIFT_1_WEEKDAY' => '#4A90D9',
                'SHIFT_2_WEEKDAY' => '#1A3F6F',
                'SHIFT_2_FRIDAY'  => '#1A3F6F',
                'SHIFT_1_WEEKEND' => '#4CAF82',
                'SHIFT_2_WEEKEND' => '#1E6645',
                'LEMBUR_SHIFT_1'  => '#F0A500',
                'LEMBUR_SHIFT_2'  => '#A05A00',
            ];
            $color = $shiftColors[$shiftSlug] ?? '#888';
            $shiftBadge = "<span class='badge' style='background:{$color};color:#fff;padding:4px 8px;border-radius:4px;font-size:14px'>{$shiftSlug}</span>";

            // Status
            $statusBadge = '<span class="badge badge-secondary badge-pill" style="font-size:14px">No Scan In</span>';
            if ($scanIn && $matchedSchedule) {
                $schedIn   = Carbon::parse($dateStr . ' ' . $matchedSchedule->time_in);
                $toleranceSecs = 60;
                $diffMin       = $schedIn->diffInSeconds($scanIn, false);
                if ($diffMin <= $toleranceSecs) {
                    $statusBadge = '<span class="badge badge-success badge-pill" style="font-size:14px">On Time</span>';
                } else {
                    $statusBadge = '<span class="badge badge-danger badge-pill" style="font-size:14px">Late</span>';
                }
            } elseif ($scanIn) {
                $statusBadge = '<span class="badge badge-success badge-pill" style="font-size:14px">On Time</span>';
            }

            return [
                'emp_id'    => optional($check->employee)->emp_id ?? '-',
                'name'      => optional($check->employee)->name ?? '-',
                'shift'     => $shiftBadge,
                'status'    => $statusBadge,
                'date'      => $dateStr,
                'time_in'   => $scanIn  ? $scanIn->format('H:i:s')  : '-',
                'time_out'  => $scanOut ? $scanOut->format('H:i:s') : '-',
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function indexLatetime()
    {
        return view('admin.latetime');
    }

    public function lateTimeData(Request $request)
    {
        $allSchedules = Schedule::all();

        $query = Check::with(['employee'])->orderBy('attendance_time', 'desc');

        if ($request->bulan) {
            $query->whereMonth('attendance_time', $request->bulan);
        }
        if ($request->tahun) {
            $query->whereYear('attendance_time', $request->tahun);
        }
        if ($request->dari) {
            $query->whereDate('attendance_time', '>=', $request->dari);
        }
        if ($request->sampai) {
            $query->whereDate('attendance_time', '<=', $request->sampai);
        }

        $checks = $query->get();
        $data   = [];

        foreach ($checks as $check) {
            if (!$check->attendance_time) continue;

            $scanIn  = Carbon::parse($check->attendance_time);
            $dateStr = $scanIn->format('Y-m-d');

            $dayType    = HolidayService::getDayType($dateStr);
            $isFriday   = Carbon::parse($dateStr)->dayOfWeek === Carbon::FRIDAY && $dayType === 'weekday';
            $isSaturday = $dayType === 'saturday';
            $isHoliday  = $dayType === 'holiday';
            $isWeekday  = $dayType === 'weekday';

            $scanHour = (int) $scanIn->format('H');

            $matchedSchedule = null;
            $override = HolidayOverride::where('date', $dateStr)->first();

            if ($override && $override->schedule_id) {
                $matchedSchedule = Schedule::find($override->schedule_id);
            } else {
                foreach ($allSchedules as $schedule) {
                    $sDayType = $schedule->day_type ?? 'weekday';
                    $dayMatch = match($sDayType) {
                        'friday'   => $isFriday,
                        'saturday' => $isSaturday,
                        'holiday'  => $isHoliday,
                        'weekday'  => $isWeekday && !$isFriday,
                        default    => false,
                    };
                    if (!$dayMatch) continue;
                    $schedHour = (int) Carbon::parse($schedule->time_in)->format('H');
                    $diff = abs($scanHour - $schedHour);
                    $diff = min($diff, 24 - $diff);
                    if ($diff <= 3) {
                        $matchedSchedule = $schedule;
                        break;
                    }
                }
            }

            if (!$matchedSchedule) continue;

            // Hitung apakah Late
            $schedIn      = Carbon::parse($dateStr . ' ' . $matchedSchedule->time_in);
            $totalSeconds = $schedIn->diffInSeconds($scanIn, false);
            if ($totalSeconds <= 60) continue; // toleransi 60 detik

            $lateHours    = floor($totalSeconds / 3600);
            $lateMins     = floor(($totalSeconds % 3600) / 60);
            $lateSecs     = $totalSeconds % 60;
            $lateDuration = sprintf('%02d:%02d:%02d', $lateHours, $lateMins, $lateSecs);

            $emp     = $check->employee;
            $empId   = $emp ? ($emp->emp_id ?? '-') : '-';
            $name    = $emp ? $emp->name : '-';
            $timeIn  = $scanIn->format('H:i:s');
            $timeOut = $check->leave_time ? Carbon::parse($check->leave_time)->format('H:i:s') : '-';

            $data[] = [
                'date'          => $dateStr,
                'emp_id'        => $empId,
                'name'          => $name,
                'late_duration' => '<span class="badge badge-pill" style="background:#e74c4c;color:#fff;padding:4px 8px;border-radius:4px;font-size:14px">' . $lateDuration . '</span>',
                'time_in'       => $timeIn,
                'time_out'      => $timeOut,
            ];
        }

        return response()->json(['data' => $data]);
    }

    public static function lateTimeDevice($att_dateTime, Employee $employee)
    {
        $attendance_time = new DateTime($att_dateTime);
        $checkin         = new DateTime($employee->schedules->first()->time_in);
        $difference      = $checkin->diff($attendance_time)->format('%H:%I:%S');

        $latetime                = new Latetime();
        $latetime->emp_id        = $employee->id;
        $latetime->duration      = $difference;
        $latetime->latetime_date = date('Y-m-d', strtotime($att_dateTime));
        $latetime->save();
    }

    /**
     * AJAX endpoint untuk Overtime — hitung real-time dari checks
     */
    public function overtimeData(Request $request)
    {
        $query = Check::with(['employee'])->whereNotNull('leave_time');

        if ($request->bulan)  $query->whereMonth('leave_time', $request->bulan);
        if ($request->tahun)  $query->whereYear('leave_time', $request->tahun);
        if ($request->dari)   $query->whereDate('leave_time', '>=', $request->dari);
        if ($request->sampai) $query->whereDate('leave_time', '<=', $request->sampai);

        $checks       = $query->orderBy('leave_time', 'desc')->get();
        $allSchedules = Schedule::all();

        $data = [];

        foreach ($checks as $check) {
            $scanIn  = $check->attendance_time ? Carbon::parse($check->attendance_time) : null;
            $scanOut = Carbon::parse($check->leave_time);
            $dateStr = $scanOut->format('Y-m-d');

            $dayType    = HolidayService::getDayType($dateStr);
            $isFriday   = Carbon::parse($dateStr)->dayOfWeek === Carbon::FRIDAY && $dayType === 'weekday';
            $isSaturday = $dayType === 'saturday';
            $isHoliday  = $dayType === 'holiday';
            $isWeekday  = $dayType === 'weekday';

            $scanHour        = $scanIn ? (int) $scanIn->format('H') : null;
            $matchedSchedule = null;
            $override        = HolidayOverride::where('date', $dateStr)->first();

            if ($override && $override->schedule_id) {
                $matchedSchedule = Schedule::find($override->schedule_id);
            } else {
                foreach ($allSchedules as $schedule) {
                    $sDayType = $schedule->day_type ?? 'weekday';
                    $dayMatch = match($sDayType) {
                        'friday'   => $isFriday,
                        'saturday' => $isSaturday,
                        'holiday'  => $isHoliday,
                        'weekday'  => $isWeekday && !$isFriday,
                        default    => false,
                    };
                    if (!$dayMatch) continue;

                    if ($scanHour !== null) {
                        $schedHour = (int) Carbon::parse($schedule->time_in)->format('H');
                        $diff = min(abs($scanHour - $schedHour), 24 - abs($scanHour - $schedHour));
                        if ($diff <= 3) { $matchedSchedule = $schedule; break; }
                    } else {
                        $matchedSchedule = $schedule; break;
                    }
                }
                if (!$matchedSchedule) {
                    $matchedSchedule = $allSchedules->where('day_type', $isFriday ? 'friday' : $dayType)->first();
                }
            }

            if (!$matchedSchedule) continue;

            // Hitung overtime: leave_time vs schedule time_out
            $scheduleTimeOut = Carbon::parse($dateStr . ' ' . $matchedSchedule->time_out);

            // Handle overnight shift (time_out di hari berikutnya)
            if ($scheduleTimeOut->lt(Carbon::parse($dateStr . ' ' . ($matchedSchedule->time_in ?? '08:00:00')))) {
                $scheduleTimeOut->addDay();
            }

            $totalSeconds     = $scheduleTimeOut->diffInSeconds($scanOut, false);
            $toleranceSeconds = 55 * 60; // 55 menit

            if ($totalSeconds <= $toleranceSeconds) continue;

            $hours            = floor($totalSeconds / 3600);
            $minutes          = floor(($totalSeconds % 3600) / 60);
            $seconds          = $totalSeconds % 60;
            $overtimeDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

            $shiftSlug = optional($matchedSchedule)->slug ?? '-';
            $shiftColors = [
                'SHIFT_1_WEEKDAY' => '#4A90D9',
                'SHIFT_2_WEEKDAY' => '#1A3F6F',
                'SHIFT_2_FRIDAY'  => '#1A3F6F',
                'SHIFT_1_WEEKEND' => '#4CAF82',
                'SHIFT_2_WEEKEND' => '#1E6645',
                'LEMBUR_SHIFT_1'  => '#F0A500',
                'LEMBUR_SHIFT_2'  => '#A05A00',
            ];
            $color      = $shiftColors[$shiftSlug] ?? '#888';
            $shiftBadge = "<span class='badge' style='background:{$color};color:#fff;padding:4px 8px;border-radius:4px;font-size:14px'>{$shiftSlug}</span>";

            $overtimeBadge = "<span class='badge badge-warning' style='font-size:14px;padding:5px 10px'>{$overtimeDuration}</span>";

            $data[] = [
                'date'              => $dateStr,
                'emp_id'            => optional($check->employee)->emp_id ?? $check->emp_id ?? '-',
                'name'              => optional($check->employee)->name ?? '-',
                'shift'             => $shiftBadge,
                'schedule_time_out' => Carbon::parse($matchedSchedule->time_out)->format('H:i:s'),
                'actual_time_out'   => $scanOut->format('H:i:s'),
                'overtime_duration' => $overtimeBadge,
            ];
        }

        return response()->json(['data' => $data]);
    }
}