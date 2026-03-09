<?php
namespace App\Http\Controllers;

use DateTime;
use App\Models\User;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\IzinDanCuti;
use Illuminate\Http\Request;

class IzinDanCutiController extends Controller
{
    public function index()
    {
        $izinDanCutis = IzinDanCuti::with('employee')->whereNotNull('reason')->orderBy('leave_date', 'desc')->get();
        return view('admin.izindancuti', compact('izinDanCutis'));
    }

    public function store(Request $request)
    {
        $izinDanCuti             = new IzinDanCuti();
        $izinDanCuti->emp_id     = $request->emp_id;
        $izinDanCuti->leave_date = $request->date;
        $izinDanCuti->reason     = $request->reason;
        $izinDanCuti->note       = $request->note;
        $izinDanCuti->status     = 1;
        $izinDanCuti->save();

        return response()->json(['success' => true, 'message' => 'Leave berhasil disimpan']);
    }

    public function destroy(Request $request)
    {
        $izinDanCuti = IzinDanCuti::find($request->id);
        if (!$izinDanCuti) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan']);
        }
        $izinDanCuti->delete();
        return response()->json(['success' => true, 'message' => 'Leave berhasil dihapus']);
    }

    public function indexOvertime()
    {
        return view('admin.overtime');
    }

    public static function overTimeDevice($att_dateTime, Employee $employee)
    {
        $attendance_time = new DateTime($att_dateTime);
        $checkout        = new DateTime($employee->schedules->first()->time_out);
        $difference      = $checkout->diff($attendance_time)->format('%H:%I:%S');

        $overtime               = new Overtime();
        $overtime->emp_id       = $employee->id;
        $overtime->duration     = $difference;
        $overtime->overtime_date = date('Y-m-d', strtotime($att_dateTime));
        $overtime->save();
    }
}