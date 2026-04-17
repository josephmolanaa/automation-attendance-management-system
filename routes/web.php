<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FingerDevicesControlller;
use App\Http\Controllers\SheetReportController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('attended/{user_id}', '\App\Http\Controllers\AttendanceController@attended')->name('attended');
Route::get('attended-before/{user_id}', '\App\Http\Controllers\AttendanceController@attendedBefore')->name('attendedBefore');

Auth::routes(['register' => false, 'reset' => false]);

Route::group(['middleware' => ['auth', 'Role'], 'roles' => ['admin']], function () {
    Route::resource('/employees', '\App\Http\Controllers\EmployeeController');
    Route::get('/attendance', '\App\Http\Controllers\AttendanceController@index')->name('attendance');
    Route::get('/attendance/data', '\App\Http\Controllers\AttendanceController@ajaxData')->name('attendance.data');
    Route::get('/overtime/data', '\App\Http\Controllers\AttendanceController@overtimeData')->name('overtime.data');
    Route::get('/latetime', '\App\Http\Controllers\AttendanceController@indexLatetime')->name('indexLatetime');
    Route::get('/latetime/data', '\App\Http\Controllers\AttendanceController@lateTimeData')->name('latetime.data');
    Route::get('/izindancuti', '\App\Http\Controllers\IzinDanCutiController@index')->name('izindancuti');
    Route::post('/izindancuti/store', '\App\Http\Controllers\IzinDanCutiController@store')->name('izindancuti.store');
    Route::delete('/izindancuti/delete', '\App\Http\Controllers\IzinDanCutiController@destroy')->name('izindancuti.delete');
    Route::get('/overtime', '\App\Http\Controllers\IzinDanCutiController@indexOvertime')->name('indexOvertime');
    Route::get('/admin', '\App\Http\Controllers\AdminController@index')->name('admin');
    Route::resource('/schedule', '\App\Http\Controllers\ScheduleController');
    Route::get('/check', '\App\Http\Controllers\CheckController@index')->name('check');
    Route::post('check-store', '\App\Http\Controllers\CheckController@CheckStore')->name('check_store');

    // Fingerprint Devices
    Route::resource('/finger_device', '\App\Http\Controllers\BiometricDeviceController');
    Route::delete('finger_device/destroy', '\App\Http\Controllers\BiometricDeviceController@massDestroy')->name('finger_device.massDestroy');
    Route::get('finger_device/{fingerDevice}/employees/add', '\App\Http\Controllers\BiometricDeviceController@addEmployee')->name('finger_device.add.employee');
    Route::get('finger_device/{fingerDevice}/get/attendance', '\App\Http\Controllers\BiometricDeviceController@getAttendance')->name('finger_device.get.attendance');

    // Temp Clear Attendance route
    Route::get('finger_device/clear/attendance', function () {
        $midnight = \Carbon\Carbon::createFromTime(23, 50, 00);
        $diff = now()->diffInMinutes($midnight);
        dispatch(new ClearAttendanceJob())->delay(now()->addMinutes($diff));
        toast("Attendance Clearance Queue will run in 11:50 P.M}!", "success");
        return back();
    })->name('finger_device.clear.attendance');

    // Holiday Overrides
    Route::prefix('holiday-overrides')->group(function () {
        Route::get('/month',       [App\Http\Controllers\HolidayOverrideController::class, 'getMonthData']);
        Route::get('/date-info',   [App\Http\Controllers\HolidayOverrideController::class, 'getDateInfo']);
        Route::get('/clear-cache', [App\Http\Controllers\HolidayOverrideController::class, 'clearCache']);
        Route::post('/',           [App\Http\Controllers\HolidayOverrideController::class, 'store']);
        Route::delete('/',         [App\Http\Controllers\HolidayOverrideController::class, 'destroy']);
    });

    // Sheet Report (AJAX DataTable + Export)
    Route::get('/sheet-report',        [SheetReportController::class, 'index'])->name('sheet-report');
    Route::get('/sheet-report/data',   [SheetReportController::class, 'ajaxData'])->name('sheet-report.data');
    Route::get('/sheet-report/export', [SheetReportController::class, 'export'])->name('sheet-report.export');

    // Import Absensi via CSV
    Route::get('/scanlog-upload',           [\App\Http\Controllers\ScanlogUploadController::class, 'index'])->name('scanlog.upload');
    Route::get('/scanlog-template',         [\App\Http\Controllers\ScanlogUploadController::class, 'downloadTemplate'])->name('scanlog.template');
    Route::post('/scanlog-parse-csv',       [\App\Http\Controllers\ScanlogUploadController::class, 'parseCsv'])->name('scanlog.parse.csv');
    Route::post('/scanlog-import-db',       [\App\Http\Controllers\ScanlogUploadController::class, 'importToDb'])->name('scanlog.import.db');
});

Route::group(['middleware' => ['auth']], function () {
    // Route::get('/home', 'HomeController@index')->name('home');
});


