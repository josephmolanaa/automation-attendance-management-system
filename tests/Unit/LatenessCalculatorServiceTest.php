<?php

namespace Tests\Unit;

use App\Models\Check;
use App\Models\Employee;
use App\Models\HolidayOverride;
use App\Models\Schedule;
use App\Services\LatenessCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LatenessCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    private LatenessCalculatorService $service;
    private Employee $employee;
    private Schedule $weekdayShift;
    private Schedule $nightShift;
    private Schedule $holidayShift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LatenessCalculatorService();
        $this->employee = new Employee();
        $this->employee->emp_id = 'EMP001';
        $this->employee->name = 'TEST EMPLOYEE';
        $this->employee->position = 'Operator';
        $this->employee->save();

        $this->weekdayShift = $this->makeSchedule('SHIFT_1_WEEKDAY', 'weekday', '08:00:00', '17:00:00');
        $this->nightShift = $this->makeSchedule('SHIFT_2_WEEKDAY', 'weekday', '19:00:00', '03:00:00');
        $this->holidayShift = $this->makeSchedule('LEMBUR_SHIFT_1', 'holiday', '08:00:00', '17:00:00');
    }

    public function test_scan_at_grace_limit_is_on_time(): void
    {
        $check = $this->makeCheck('2026-05-18 08:01:00', '2026-05-18 17:00:00');

        $record = $this->service->calculate($check, true);

        $this->assertEquals('tepat_waktu', $record->status);
        $this->assertEquals(0, $record->late_seconds);
        $this->assertEquals(0, $record->late_minutes);
    }

    public function test_one_second_after_grace_limit_is_one_late_minute(): void
    {
        $check = $this->makeCheck('2026-05-18 08:01:01', '2026-05-18 17:00:00');

        $record = $this->service->calculate($check, true);

        $this->assertEquals('terlambat', $record->status);
        $this->assertEquals(1, $record->late_seconds);
        $this->assertEquals(1, $record->late_minutes);
        $this->assertEquals('00:00:01', $record->late_duration);
    }

    public function test_missing_scan_in_is_marked_as_no_scan(): void
    {
        $check = $this->makeCheck(null, '2026-05-18 17:00:00');

        $record = $this->service->calculate($check, true);

        $this->assertEquals('tidak_ada_scan', $record->status);
        $this->assertEquals(0, $record->late_minutes);
    }

    public function test_night_shift_lateness_uses_shift_start_date(): void
    {
        $check = $this->makeCheck('2026-05-18 19:02:30', '2026-05-19 03:00:00', $this->nightShift->id);

        $record = $this->service->calculate($check, true);

        $this->assertEquals('terlambat', $record->status);
        $this->assertEquals('2026-05-18 19:00:00', $record->scheduled_in->format('Y-m-d H:i:s'));
        $this->assertEquals(90, $record->late_seconds);
        $this->assertEquals(2, $record->late_minutes);
    }

    public function test_sunday_without_override_is_skipped(): void
    {
        $check = $this->makeCheck('2026-05-17 08:05:00', '2026-05-17 17:00:00');

        $record = $this->service->calculate($check, true);

        $this->assertNull($record);
    }

    public function test_sunday_with_override_is_calculated(): void
    {
        HolidayOverride::create([
            'date' => '2026-05-17',
            'original_type' => 'sunday',
            'override_type' => 'holiday',
            'schedule_id' => $this->holidayShift->id,
            'note' => 'Lembur Minggu',
        ]);
        $check = $this->makeCheck('2026-05-17 08:05:00', '2026-05-17 17:00:00');

        $record = $this->service->calculate($check, true);

        $this->assertNotNull($record);
        $this->assertEquals('terlambat', $record->status);
        $this->assertEquals($this->holidayShift->id, $record->schedule_id);
    }

    private function makeSchedule(string $slug, string $dayType, string $timeIn, string $timeOut): Schedule
    {
        $schedule = new Schedule();
        $schedule->slug = $slug;
        $schedule->day_type = $dayType;
        $schedule->time_in = $timeIn;
        $schedule->time_out = $timeOut;
        $schedule->save();

        return $schedule;
    }

    private function makeCheck(?string $scanIn, ?string $scanOut, ?int $scheduleId = null): Check
    {
        return Check::create([
            'emp_id' => $this->employee->id,
            'attendance_time' => $scanIn,
            'leave_time' => $scanOut,
            'schedule_id' => $scheduleId,
        ]);
    }
}
