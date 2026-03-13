<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Check;
use Carbon\Carbon;

class CheckController extends Controller
{
    public function index()
    {
        return view('admin.check')->with(['employees' => Employee::all()]);
    }

    /**
     * ============================================================
     * Store manual attendance — tulis ke tabel checks
     * ============================================================
     *
     * Request format:
     *   time_in[Y-m-d][emp_id]  = "HH:MM"  (dari input jam)
     *   time_out[Y-m-d][emp_id] = "HH:MM"  (dari input jam)
     *
     * Logic:
     *   - Kalau time_in diisi → cari atau buat baris checks untuk emp+date
     *   - Kalau time_out diisi → isi leave_time di baris yang ada
     *   - Kalau keduanya kosong → hapus baris jika ada (uncheck)
     * ============================================================
     */
    public function CheckStore(Request $request)
    {
        $timeIns  = $request->input('time_in', []);   // [date => [emp_id => jam]]
        $timeOuts = $request->input('time_out', []);  // [date => [emp_id => jam]]

        // Kumpulkan semua tanggal & emp_id yang terlibat
        $allDates = array_unique(array_merge(array_keys($timeIns), array_keys($timeOuts)));

        foreach ($allDates as $date) {
            $insForDate  = $timeIns[$date]  ?? [];
            $outsForDate = $timeOuts[$date] ?? [];

            $allEmpIds = array_unique(array_merge(array_keys($insForDate), array_keys($outsForDate)));

            foreach ($allEmpIds as $empId) {
                $timeIn  = trim($insForDate[$empId]  ?? '');
                $timeOut = trim($outsForDate[$empId] ?? '');

                if ($timeIn === '' && $timeOut === '') {
                    // Keduanya kosong → hapus baris manual jika ada
                    Check::where('emp_id', $empId)
                        ->whereDate('attendance_time', $date)
                        ->delete();
                    continue;
                }

                // Cari baris existing untuk emp + date ini
                $existing = Check::where('emp_id', $empId)
                    ->whereDate('attendance_time', $date)
                    ->orderBy('attendance_time', 'asc')
                    ->first();

                $attendanceTimestamp = $timeIn !== ''
                    ? $date . ' ' . $timeIn . ':00'
                    : ($existing ? $existing->attendance_time : $date . ' 08:00:00');

                $leaveTimestamp = $timeOut !== ''
                    ? $date . ' ' . $timeOut . ':00'
                    : null;

                // Handle overnight: kalau time_out < time_in → time_out hari berikutnya
                if ($leaveTimestamp) {
                    $attCarbon   = Carbon::parse($attendanceTimestamp);
                    $leaveCarbon = Carbon::parse($leaveTimestamp);
                    if ($leaveCarbon->lt($attCarbon)) {
                        $leaveCarbon->addDay();
                        $leaveTimestamp = $leaveCarbon->toDateTimeString();
                    }
                }

                if ($existing) {
                    // Update baris yang sudah ada
                    if ($timeIn !== '') {
                        $existing->attendance_time = $attendanceTimestamp;
                    }
                    $existing->leave_time = $leaveTimestamp;
                    $existing->save();
                } else {
                    // Buat baris baru
                    Check::create([
                        'emp_id'          => $empId,
                        'attendance_time' => $attendanceTimestamp,
                        'leave_time'      => $leaveTimestamp,
                    ]);
                }
            }
        }

        flash()->success('Success', 'Data kehadiran berhasil disimpan!');
        return back();
    }

    public function sheetReport()
    {
        return view('admin.sheet-report');
    }
}