<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\FingerHelper;

use App\Http\Controllers\Controller;

use App\Http\Requests\FingerDevice\StoreRequest;

use App\Http\Requests\FingerDevice\UpdateRequest;

use App\Jobs\GetAttendanceJob;

use App\Models\FingerDevices;

use App\Models\Employee;
use App\Models\Check;

use Gate;

use Illuminate\Http\RedirectResponse;

use Rats\Zkteco\Lib\ZKTeco;

use Symfony\Component\HttpFoundation\Response;

class BiometricDeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $devices = FingerDevices::all();

        return view('admin.fingerDevices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.fingerDevices.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $helper = new FingerHelper();

        $device = $helper->init($request->input('ip'));

        if ($device->connect()) {
            // Serial Number Sample CDQ9192960002\x00

            $serial = $helper->getSerial($device);

            FingerDevices::create($request->validated() + ['serialNumber' => $serial]);

            flash()->success(__('app.success'), __('app.biometric_created'));
        } else {
            flash()->error(__('app.error'), __('app.biometric_connect_failed'));
        }

        return redirect()->route('finger_device.index');
    }

    public function show(FingerDevices $fingerDevice)
    {
        return view('admin.fingerDevices.show', compact('fingerDevice'));
    }

    public function edit(FingerDevices $fingerDevice)
    {
        return view('admin.fingerDevices.edit', compact('fingerDevice'));
    }

    public function update(UpdateRequest $request, FingerDevices $fingerDevice): RedirectResponse
    {
        $fingerDevice->update($request->validated());

        flash()->success(__('app.success'), __('app.biometric_updated'));

        return redirect()->route('finger_device.index');
    }
    public function destroy(FingerDevices $fingerDevice): RedirectResponse
    {
        try {
            $fingerDevice->delete();
        } catch (\Exception $e) {
            toast("Failed to delete {$fingerDevice->name}", 'error');
        }

        flash()->success(__('app.success'), __('app.biometric_deleted'));

        return back();
    }

    public function addEmployee(FingerDevices $fingerDevice): RedirectResponse
    {
        $device = new ZKTeco($fingerDevice->ip, 4370);

        $device->connect();

        $deviceUsers = collect($device->getUser())->pluck('uid');

        $employees = Employee::select('name', 'id')
            ->whereNotIn('id', $deviceUsers)
            ->get();

        $i = 1;

        foreach ($employees as $employee) {
            $device->setUser($i++, $employee->id, $employee->name, '', '0', '0');
        }
        flash()->success(__('app.success'), __('app.biometric_employees_added'));

        return back();
    }

    public function getAttendance(FingerDevices $fingerDevice)
    {
        $device = new ZKTeco($fingerDevice->ip, 4370);

        $device->connect();

        $data = $device->getAttendance();
        
        foreach ($data as $value) {
            if (!Employee::whereId($value['id'])->exists()) {
                continue;
            }

            $timestamp = date('Y-m-d H:i:s', strtotime($value['timestamp']));
            $date = date('Y-m-d', strtotime($value['timestamp']));

            if ($value['type'] == 0) {
                Check::firstOrCreate(
                    ['emp_id' => $value['id'], 'attendance_time' => $timestamp],
                    ['leave_time' => null]
                );
                continue;
            }

            $check = Check::where('emp_id', $value['id'])
                ->whereDate('attendance_time', $date)
                ->whereNull('leave_time')
                ->latest('attendance_time')
                ->first();

            if (!$check) {
                $check = Check::where('emp_id', $value['id'])
                    ->whereNull('leave_time')
                    ->latest('attendance_time')
                    ->first();
            }

            if ($check) {
                $check->leave_time = $timestamp;
                $check->save();
            }
        }

        
        flash()->success(__('app.success'), __('app.attendance_queue_started'));

        return back();
    }
}
