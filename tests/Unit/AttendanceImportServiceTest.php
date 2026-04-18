<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\AttendanceImportService;
use App\Models\Attendance;
use App\Models\Employee;

class AttendanceImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttendanceImportService $service;
    private $employeeId;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Buat dummy employee jika Model Attendance memiliki referensi relasi ke Employee
        $employee = Employee::create([
            'name' => 'SAEFUL ROHMAN',
            'position' => 'Staff',
            'pin' => '123'
        ]);
        
        $this->employeeId = $employee->id;
        $this->service = new AttendanceImportService();
    }

    public function test_mixed_shift_transition()
    {
        $rows = collect([
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-09', 'scan1'=>'07:50:28', 'scan2'=>'19:08:01'],
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-10', 'scan1'=>'07:50:03', 'scan2'=>'17:06:14'],
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-11', 'scan1'=>'07:52:27', 'scan2'=>'13:38:05'],
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-12', 'scan1'=>'07:48:41', 'scan2'=>'16:02:02'],
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-13', 'scan1'=>'18:50:30', 'scan2'=>null],
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-14', 'scan1'=>'05:01:51', 'scan2'=>'18:46:46'],
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-15', 'scan1'=>'05:01:09', 'scan2'=>'18:51:18'],
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-16', 'scan1'=>'05:02:29', 'scan2'=>'18:47:55'],
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-17', 'scan1'=>'05:00:47', 'scan2'=>null],
        ]);

        $this->service->processEmployeeRows($this->employeeId, $rows);

        $records = Attendance::where('employee_id', $this->employeeId)->orderBy('date')->get();

        $this->assertCount(8, $records);
        
        $this->assertEquals('mixed', $records[0]->shift_hint);
        
        // Assert Tanggal 09 Apr
        $this->assertEquals('2026-04-09 07:50:28', $records[0]->check_in);
        $this->assertEquals('2026-04-09 19:08:01', $records[0]->check_out);
        $this->assertFalse((bool) $records[0]->is_overnight);

        // Assert Tanggal 13 Apr (Overnight CheckIn)
        $record13 = $records->where('date', '2026-04-13')->first();
        $this->assertNotNull($record13);
        $this->assertEquals('2026-04-13 18:50:30', $record13->check_in);
        $this->assertEquals('2026-04-14 05:01:51', $record13->check_out);
        $this->assertTrue((bool) $record13->is_overnight);

        // Assert Tanggal 16 Apr
        $record16 = $records->where('date', '2026-04-16')->first();
        $this->assertEquals('2026-04-16 18:47:55', $record16->check_in);
        $this->assertEquals('2026-04-17 05:00:47', $record16->check_out);
        $this->assertTrue((bool) $record16->is_overnight);
        
        // Pastikan tidak ada Tgl 17 Apr
        $this->assertNull($records->where('date', '2026-04-17')->first());
    }

    public function test_shift_2_streak()
    {
        $rows = collect([
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-07', 'scan1'=>'18:55:00', 'scan2'=>null],
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-08', 'scan1'=>'05:10:00', 'scan2'=>'18:48:00'],
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-09', 'scan1'=>'05:08:00', 'scan2'=>'18:52:00'],
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-10', 'scan1'=>'05:11:00', 'scan2'=>'18:49:00'],
            ['name' => 'SAEFUL ROHMAN', 'date'=>'2026-04-11', 'scan1'=>'05:09:00', 'scan2'=>null],
        ]);

        $this->service->processEmployeeRows($this->employeeId, $rows);

        $records = Attendance::where('employee_id', $this->employeeId)->orderBy('date')->get();

        $this->assertCount(4, $records);
        $this->assertEquals('shift_2', $records[0]->shift_hint);

        // Tanggal 07
        $this->assertEquals('2026-04-07 18:55:00', $records[0]->check_in);
        $this->assertEquals('2026-04-08 05:10:00', $records[0]->check_out);
        $this->assertTrue((bool) $records[0]->is_overnight);
    }

    public function test_shift_1_with_early_anomaly()
    {
        $rows = collect([
            ['name' => 'TEST', 'date'=>'2026-04-07', 'scan1'=>'07:55:00', 'scan2'=>'17:02:00'],
            ['name' => 'TEST', 'date'=>'2026-04-08', 'scan1'=>'07:50:00', 'scan2'=>'17:05:00'],
            ['name' => 'TEST', 'date'=>'2026-04-09', 'scan1'=>'05:30:00', 'scan2'=>'17:00:00'],
            ['name' => 'TEST', 'date'=>'2026-04-10', 'scan1'=>'07:52:00', 'scan2'=>'17:03:00'],
        ]);

        $this->service->processEmployeeRows($this->employeeId, $rows);

        $records = Attendance::where('employee_id', $this->employeeId)->orderBy('date')->get();

        $this->assertCount(4, $records);
        $this->assertEquals('shift_1', $records[0]->shift_hint);

        $anomalyRecord = $records->where('date', '2026-04-09')->first();
        $this->assertEquals('2026-04-09 05:30:00', $anomalyRecord->check_in);
        $this->assertEquals('2026-04-09 17:00:00', $anomalyRecord->check_out);
        $this->assertFalse((bool) $anomalyRecord->is_overnight);
        $this->assertTrue((bool) $anomalyRecord->needs_review);
    }

    public function test_missing_days_handling()
    {
        $rows = collect([
            ['name' => 'TEST', 'date'=>'2026-04-07', 'scan1'=>'18:55:00', 'scan2'=>null],
            // 08 hilang
            ['name' => 'TEST', 'date'=>'2026-04-09', 'scan1'=>'05:10:00', 'scan2'=>'18:48:00'],
        ]);

        $this->service->processEmployeeRows($this->employeeId, $rows);

        $records = Attendance::where('employee_id', $this->employeeId)->orderBy('date')->get();

        $this->assertCount(2, $records);
        $this->assertEquals('insufficient_data', $records[0]->shift_hint);

        // Tanggal 07
        $record07 = $records->where('date', '2026-04-07')->first();
        $this->assertEquals('2026-04-07 18:55:00', $record07->check_in);
        $this->assertNull($record07->check_out);

        // Tanggal 09 (Needs review karena scan1 adalah window overnight tapi tak ada prev day)
        $record09 = $records->where('date', '2026-04-09')->first();
        $this->assertEquals('2026-04-09 05:10:00', $record09->check_in);
        $this->assertEquals('2026-04-09 18:48:00', $record09->check_out);
        $this->assertTrue((bool) $record09->needs_review);
    }
}
