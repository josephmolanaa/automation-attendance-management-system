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
use App\Services\ShiftDetectionService;
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
     * Optimized with eager loading and proper query building
     */
    public function ajaxData(Request $request)
    {
        // Build query with eager loading untuk menghindari N+1 problem
        $query = Check::with(['employee:id,emp_id,name', 'schedule:id,slug,time_in,time_out'])
            ->select('id', 'emp_id', 'attendance_time', 'leave_time', 'schedule_id')
            ->orderBy('attendance_time', 'desc');

        // Filter bulan - Fixed: gunakan where dengan closure untuk OR condition
        if ($request->bulan) {
            $query->where(function($q) use ($request) {
                $q->whereMonth('attendance_time', $request->bulan)
                  ->orWhereMonth('leave_time', $request->bulan);
            });
        }
        
        // Filter tahun - Fixed: gunakan where dengan closure untuk OR condition
        if ($request->tahun) {
            $query->where(function($q) use ($request) {
                $q->whereYear('attendance_time', $request->tahun)
                  ->orWhereYear('leave_time', $request->tahun);
            });
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
        
        // Cache schedules untuk menghindari query berulang
        $allSchedules = cache()->remember('all_schedules', 3600, function() {
            return Schedule::all();
        });

        $data = $checks->map(function ($check) use ($allSchedules) {
            $scanIn  = $check->attendance_time ? Carbon::parse($check->attendance_time) : null;
            $scanOut = $check->leave_time      ? Carbon::parse($check->leave_time)      : null;
            $refTime = $scanIn ?? $scanOut ?? now();
            $dateStr = $refTime->format('Y-m-d');

            // ── Shift detection: baca dari DB, fallback ke service ──
            $matchedSchedule = null;
            if ($check->schedule_id) {
                $matchedSchedule = $allSchedules->firstWhere('id', $check->schedule_id);
            }
            if (!$matchedSchedule) {
                $scanTime = $scanIn ? $scanIn->format('H:i:s') : null;
                $matchedSchedule = ShiftDetectionService::detectAsSchedule($dateStr, $scanTime);
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
            $statusBadge = '<span class="badge badge-secondary badge-pill" style="font-size:14px">' . __('app.no_scan_in') . '</span>';
            if ($scanIn && $matchedSchedule) {
                $schedIn   = Carbon::parse($dateStr . ' ' . $matchedSchedule->time_in);
                $toleranceSecs = 60;
                $diffMin       = $schedIn->diffInSeconds($scanIn, false);
                if ($diffMin <= $toleranceSecs) {
                    $statusBadge = '<span class="badge badge-success badge-pill" style="font-size:14px">' . __('app.on_time') . '</span>';
                } else {
                    $statusBadge = '<span class="badge badge-danger badge-pill" style="font-size:14px">' . __('app.late') . '</span>';
                }
            } elseif ($scanIn) {
                $statusBadge = '<span class="badge badge-success badge-pill" style="font-size:14px">' . __('app.on_time') . '</span>';
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
        // Cache schedules
        $allSchedules = cache()->remember('all_schedules', 3600, function() {
            return Schedule::all();
        });

        // Optimized query dengan eager loading dan select specific columns
        $query = Check::with(['employee:id,emp_id,name'])
            ->select('id', 'emp_id', 'attendance_time', 'leave_time', 'schedule_id')
            ->whereNotNull('attendance_time')
            ->orderBy('attendance_time', 'desc');

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

            // ── Shift detection: baca dari DB, fallback ke service ──
            $matchedSchedule = null;
            if ($check->schedule_id) {
                $matchedSchedule = $allSchedules->firstWhere('id', $check->schedule_id);
            }
            if (!$matchedSchedule) {
                $matchedSchedule = ShiftDetectionService::detectAsSchedule($dateStr, $scanIn->format('H:i:s'));
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
     * Optimized with eager loading and caching
     */
    public function overtimeData(Request $request)
    {
        // Optimized query dengan eager loading
        $query = Check::with(['employee:id,emp_id,name'])
            ->select('id', 'emp_id', 'attendance_time', 'leave_time', 'schedule_id')
            ->whereNotNull('leave_time');

        if ($request->bulan)  $query->whereMonth('leave_time', $request->bulan);
        if ($request->tahun)  $query->whereYear('leave_time', $request->tahun);
        if ($request->dari)   $query->whereDate('leave_time', '>=', $request->dari);
        if ($request->sampai) $query->whereDate('leave_time', '<=', $request->sampai);

        $checks = $query->orderBy('leave_time', 'desc')->get();
        
        // Cache schedules
        $allSchedules = cache()->remember('all_schedules', 3600, function() {
            return Schedule::all();
        });

        $data = [];

        foreach ($checks as $check) {
            $scanIn  = $check->attendance_time ? Carbon::parse($check->attendance_time) : null;
            $scanOut = Carbon::parse($check->leave_time);
            $dateStr = $scanIn ? $scanIn->format('Y-m-d') : $scanOut->format('Y-m-d');

            // ── Shift detection: baca dari DB, fallback ke service ──
            $matchedSchedule = null;
            if ($check->schedule_id) {
                $matchedSchedule = $allSchedules->firstWhere('id', $check->schedule_id);
            }
            if (!$matchedSchedule) {
                $scanTime = $scanIn ? $scanIn->format('H:i:s') : null;
                $matchedSchedule = ShiftDetectionService::detectAsSchedule($dateStr, $scanTime);
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
