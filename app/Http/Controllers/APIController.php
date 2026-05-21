<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Check;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AttendanceEmp;
use Illuminate\Http\Request;


class ApiController extends Controller
{
    
    public function check(AttendanceEmp $request)
    {
        $request->validated();

        if ($employee = Employee::whereEmail(request('email'))->first()) {

            if (Hash::check($request->pin_code, $employee->pin_code)) {




                if(null == Check::whereEmp_id($employee->id)->latest()->first()){
                    ApiController::newAttandance($employee);
                }else{
                    
                    if(Check::whereEmp_id($employee->id)->latest()->first()->leave_time !== null){
                        ApiController::newAttandance($employee);
                    } else {
                        $check = Check::whereEmp_id($employee->id)->latest()->first();
                        $check->leave_time = date("Y-m-d H:i:s");
                        $check->save();
                        return response()->json(['success' => 'Successful in assign the leave'], 200);
                    }

                }

            } else {
                return response()->json(['error' => 'Failed to assign the attendance'], 404);
            }
        }
        return response()->json(['success' => 'Successful in assign the attendance'], 200);
    }


    public function newAttandance($employee){
        $check = new Check;
        $check->emp_id = $employee->id;
        $check->attendance_time = date("Y-m-d H:i:s");
        $check->leave_time = null;
        $check->save();
    }



    public function attendance(AttendanceEmp $request)
    {
         $request->validated();

        if ($employee = Employee::whereEmail(request('email'))->first()) {

            if (Hash::check($request->pin_code, $employee->pin_code)) {
                if (!Check::whereDate('attendance_time', date('Y-m-d'))->whereEmp_id($employee->id)->first()) {
                    ApiController::newAttandance($employee);
                } else {
                    return response()->json(['error' => 'you assigned your attendance before'], 404);
                }
            } else {
                return response()->json(['error' => 'Failed to assign the attendance'], 404);
            }
        }
        return response()->json(['success' => 'Successful in assign the attendance'], 200);

    }



    public function leave(AttendanceEmp $request)
    {
        $request->validated();

        if ($employee = Employee::whereEmail(request('email'))->first()) {

            if (Hash::check($request->pin_code, $employee->pin_code)) {
                $check = Check::whereEmp_id($employee->id)
                    ->whereDate('attendance_time', date('Y-m-d'))
                    ->whereNull('leave_time')
                    ->latest('attendance_time')
                    ->first();

                if ($check) {
                    $check->leave_time = date("Y-m-d H:i:s");
                    $check->save();
                } else {
                    return response()->json(['error' => 'you assigned your leave before'], 404);
                }
            } else {
                return response()->json(['error' => 'Failed to assign the leave'], 404);
            }
        }

        return response()->json(['success' => 'Successful in assign the leave'], 200);
    }

}
