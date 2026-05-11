<?php
namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\Check;
use App\Models\Schedule;
use App\Services\ShiftDetectionService;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        $totalEmp       = Employee::count();
        $todayStr       = date('Y-m-d');
        
        $checksToday    = Check::whereDate('attendance_time', $todayStr)->get();
        $allSchedules   = Schedule::all();
        
        $allAttendance  = $checksToday->count();
        $ontimeEmp      = 0;
        $latetimeEmp    = 0;

        foreach ($checksToday as $check) {
            $scanIn = Carbon::parse($check->attendance_time);
            $matchedSchedule = null;
            if ($check->schedule_id) {
                $matchedSchedule = $allSchedules->firstWhere('id', $check->schedule_id);
            }
            if (!$matchedSchedule) {
                $matchedSchedule = ShiftDetectionService::detectAsSchedule($todayStr, $scanIn->format('H:i:s'));
            }

            if ($matchedSchedule) {
                $schedIn   = Carbon::parse($todayStr . ' ' . $matchedSchedule->time_in);
                $diffSecs  = $schedIn->diffInSeconds($scanIn, false);
                if ($diffSecs <= 60) {
                    $ontimeEmp++;
                } else {
                    $latetimeEmp++;
                }
            } else {
                $ontimeEmp++; // Default to on time if no schedule found
            }
        }
        
        $percentageOntime = $allAttendance > 0 ? number_format(($ontimeEmp / $allAttendance) * 100, 1) : 0;

        // Recent attendance
        $recentAttendance = Check::with('employee')
            ->whereNotNull('attendance_time')
            ->orderBy('attendance_time', 'desc')
            ->limit(10)
            ->get();

        // Chart Data (Current Month up to today)
        $daysInMonth = Carbon::now()->day; 
        $chartLabels = [];
        $chartData   = [];
        
        $monthlyChecks = Check::whereMonth('attendance_time', Carbon::now()->month)
                              ->whereYear('attendance_time', Carbon::now()->year)
                              ->get()
                              ->groupBy(function($val) {
                                  return Carbon::parse($val->attendance_time)->format('d');
                              });

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dayStr = str_pad($i, 2, '0', STR_PAD_LEFT);
            $chartLabels[] = $i;
            $chartData[] = isset($monthlyChecks[$dayStr]) ? $monthlyChecks[$dayStr]->count() : 0;
        }

        $data = [$totalEmp, $allAttendance, $latetimeEmp, $percentageOntime];

        return view('admin.index')->with([
            'data'             => $data,
            'recentAttendance' => $recentAttendance,
            'chartLabels'      => $chartLabels,
            'chartData'        => $chartData,
        ]);
    }
}