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

    private function normalizeTimeInput($value)
    {
        $time = trim((string) $value);

        if ($time === '') {
            return '';
        }

        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time)) {
            return $time . ':00';
        }

        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/', $time)) {
            return $time;
        }

        return null;
    }

    private function findCheckForDate($empId, $date)
    {
        return Check::where('emp_id', $empId)
            ->where(function ($query) use ($date) {
                $query->whereDate('attendance_time', $date)
                    ->orWhere(function ($query) use ($date) {
                        $query->whereNull('attendance_time')
                            ->whereDate('leave_time', $date);
                    });
            })
            ->orderByRaw('COALESCE(attendance_time, leave_time) asc')
            ->first();
    }

    private function deleteCheckForDate($empId, $date)
    {
        Check::where('emp_id', $empId)
            ->where(function ($query) use ($date) {
                $query->whereDate('attendance_time', $date)
                    ->orWhere(function ($query) use ($date) {
                        $query->whereNull('attendance_time')
                            ->whereDate('leave_time', $date);
                    });
            })
            ->delete();
    }

    private function manualCheckPayload($check)
    {
        return [
            'time_in' => $check && $check->attendance_time
                ? Carbon::parse($check->attendance_time)->format('H:i')
                : '',
            'time_out' => $check && $check->leave_time
                ? Carbon::parse($check->leave_time)->format('H:i')
                : '',
        ];
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
                $timeIn  = $this->normalizeTimeInput($insForDate[$empId]  ?? '');
                $timeOut = $this->normalizeTimeInput($outsForDate[$empId] ?? '');

                if ($timeIn === null || $timeOut === null) {
                    flash()->error(__('app.error'), __('app.invalid_time_format'));
                    return back();
                }

                if ($timeIn === '' && $timeOut === '') {
                    // Keduanya kosong → hapus baris manual jika ada
                    $this->deleteCheckForDate($empId, $date);
                    continue;
                }

                // Cari baris existing untuk emp + date ini
                $existing = $this->findCheckForDate($empId, $date);

                $attendanceTimestamp = $timeIn !== ''
                    ? $date . ' ' . $timeIn
                    : null;

                $leaveTimestamp = $timeOut !== ''
                    ? $date . ' ' . $timeOut
                    : null;

                // Handle overnight: kalau time_out < time_in → time_out hari berikutnya
                if ($attendanceTimestamp && $leaveTimestamp) {
                    $attCarbon   = Carbon::parse($attendanceTimestamp);
                    $leaveCarbon = Carbon::parse($leaveTimestamp);
                    if ($leaveCarbon->lt($attCarbon)) {
                        $leaveCarbon->addDay();
                        $leaveTimestamp = $leaveCarbon->toDateTimeString();
                    }
                }

                if ($existing) {
                    // Update baris yang sudah ada
                    $existing->attendance_time = $attendanceTimestamp;
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

        flash()->success(__('app.success'), __('app.attendance_saved'));
        return back();
    }

    /**
     * Store single cell via AJAX
     */
    public function CheckSingleStore(Request $request)
    {
        $date  = $request->input('date');
        $empId = $request->input('emp_id');
        $timeIn  = $this->normalizeTimeInput($request->input('time_in', ''));
        $timeOut = $this->normalizeTimeInput($request->input('time_out', ''));

        if (!$date || !$empId) {
            return response()->json(['success' => false, 'message' => __('app.missing_date_or_employee')], 400);
        }

        if ($timeIn === null || $timeOut === null) {
            return response()->json(['success' => false, 'message' => __('app.invalid_time_format')], 422);
        }

        if ($timeIn === '' && $timeOut === '') {
            // Keduanya kosong → hapus baris manual jika ada
            $this->deleteCheckForDate($empId, $date);
            return response()->json([
                'success' => true,
                'message' => __('app.data_removed'),
                'time_in' => '',
                'time_out' => '',
            ]);
        }

        // Cari baris existing untuk emp + date ini
        $existing = $this->findCheckForDate($empId, $date);

        $attendanceTimestamp = $timeIn !== ''
            ? $date . ' ' . $timeIn
            : null;

        $leaveTimestamp = $timeOut !== ''
            ? $date . ' ' . $timeOut
            : null;

        // Handle overnight: kalau time_out < time_in → time_out hari berikutnya
        if ($attendanceTimestamp && $leaveTimestamp) {
            $attCarbon   = Carbon::parse($attendanceTimestamp);
            $leaveCarbon = Carbon::parse($leaveTimestamp);
            if ($leaveCarbon->lt($attCarbon)) {
                $leaveCarbon->addDay();
                $leaveTimestamp = $leaveCarbon->toDateTimeString();
            }
        }

        if ($existing) {
            // Update baris yang sudah ada
            $existing->attendance_time = $attendanceTimestamp;
            $existing->leave_time = $leaveTimestamp;
            $existing->save();
            $check = $existing;
        } else {
            // Buat baris baru
            $check = Check::create([
                'emp_id'          => $empId,
                'attendance_time' => $attendanceTimestamp,
                'leave_time'      => $leaveTimestamp,
            ]);
        }

        return response()->json(array_merge(
            ['success' => true, 'message' => __('app.saved')],
            $this->manualCheckPayload($check)
        ));
    }

    public function sheetReport()
    {
        return view('admin.sheet-report');
    }
}
