<?php
namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Check;

class AdminController extends Controller
{
    public function index()
    {
        $totalEmp       = Employee::count();
        $allAttendance  = Attendance::whereAttendance_date(date('Y-m-d'))->count();
        $ontimeEmp      = Attendance::whereAttendance_date(date('Y-m-d'))->where('status', 1)->count();
        $latetimeEmp    = Attendance::whereAttendance_date(date('Y-m-d'))->where('status', 0)->count();
        $percentageOntime = $allAttendance > 0 ? number_format(($ontimeEmp / $allAttendance) * 100, 1) : 0;

        // Recent attendance — 10 terbaru dari checks table
        $recentAttendance = Check::with('employee')
            ->whereNotNull('attendance_time')
            ->orderBy('attendance_time', 'desc')
            ->limit(10)
            ->get();

        $data = [$totalEmp, $ontimeEmp, $latetimeEmp, $percentageOntime];

        return view('admin.index')->with([
            'data'             => $data,
            'recentAttendance' => $recentAttendance,
        ]);
    }
}