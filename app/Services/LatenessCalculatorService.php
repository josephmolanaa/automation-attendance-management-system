<?php

namespace App\Services;

use App\Models\Check;
use App\Models\HolidayOverride;
use App\Models\LatenessRecord;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LatenessCalculatorService
{
    private const GRACE_SECONDS = 60;

    public function calculate(Check $check, bool $force = false): ?LatenessRecord
    {
        $date = $this->getCheckDate($check);
        if (!$date) {
            return null;
        }

        $actualScanIn = $check->attendance_time ? Carbon::parse($check->attendance_time) : null;
        $schedule = $this->resolveSchedule($date, $check->schedule_id, $actualScanIn);

        if (!$schedule) {
            if ($force) {
                LatenessRecord::where('check_id', $check->id)->delete();
            }
            return null;
        }

        $scheduledIn = $this->getScheduledIn($date, $schedule);
        $status = $this->determineStatus($actualScanIn, $scheduledIn);
        $lateSeconds = $status === 'terlambat'
            ? $this->calculateLateSeconds($actualScanIn, $scheduledIn)
            : 0;
        $lateMinutes = $lateSeconds > 0 ? (int) ceil($lateSeconds / 60) : 0;

        if (!$force && LatenessRecord::where('check_id', $check->id)->exists()) {
            return LatenessRecord::where('check_id', $check->id)->first();
        }

        return LatenessRecord::updateOrCreate(
            ['check_id' => $check->id],
            [
                'employee_id' => $check->emp_id,
                'schedule_id' => $schedule->id,
                'date' => $date->toDateString(),
                'scheduled_in' => $scheduledIn->toDateTimeString(),
                'actual_scan_in' => $actualScanIn ? $actualScanIn->toDateTimeString() : null,
                'status' => $status,
                'late_seconds' => $lateSeconds,
                'late_minutes' => $lateMinutes,
                'late_duration' => $this->formatDuration($lateSeconds),
                'notes' => $this->buildNotes($date, $schedule, $check),
            ]
        );
    }

    public function calculateRange(Carbon $start, Carbon $end, ?string $employee = null, bool $force = false): Collection
    {
        $query = Check::query()
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('attendance_time', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->whereNull('attendance_time')
                            ->whereBetween('leave_time', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
                    });
            })
            ->orderBy('attendance_time');

        if ($employee) {
            $query->whereHas('employee', function ($q) use ($employee) {
                $q->where('id', $employee)->orWhere('emp_id', $employee);
            });
        }

        return $query->get()
            ->map(fn (Check $check) => $this->calculate($check, $force))
            ->filter()
            ->values();
    }

    public function resolveSchedule(Carbon $date, ?int $scheduleId, ?Carbon $actualScanIn = null): ?Schedule
    {
        if ($scheduleId) {
            return Schedule::find($scheduleId);
        }

        $dayType = $this->resolveDayType($date);
        if ($dayType === 'sunday') {
            return null;
        }

        if ($actualScanIn) {
            $detected = $this->bestScheduleForScan($dayType, $actualScanIn);
            if ($detected) {
                return $detected;
            }
        }

        $defaultSlugs = [
            'weekday' => 'SHIFT_1_WEEKDAY',
            'friday' => 'SHIFT_1_WEEKDAY',
            'saturday' => 'SHIFT_1_WEEKEND',
            'holiday' => 'LEMBUR_SHIFT_1',
        ];

        return isset($defaultSlugs[$dayType])
            ? Schedule::where('slug', $defaultSlugs[$dayType])->first()
            : null;
    }

    public function resolveDayType(Carbon $date): string
    {
        $dateString = $date->toDateString();
        $override = HolidayOverride::where('date', $dateString)->first();
        if ($override) {
            return $override->override_type ?: 'holiday';
        }

        if ($date->dayOfWeek === Carbon::SUNDAY) {
            return 'sunday';
        }

        return match ($date->dayOfWeek) {
            Carbon::FRIDAY => 'friday',
            Carbon::SATURDAY => 'saturday',
            default => 'weekday',
        };
    }

    public function getScheduledIn(Carbon $date, Schedule $schedule): Carbon
    {
        return Carbon::parse($date->toDateString() . ' ' . $schedule->time_in);
    }

    public function determineStatus(?Carbon $actualScanIn, Carbon $scheduledIn): string
    {
        if (!$actualScanIn) {
            return 'tidak_ada_scan';
        }

        return $actualScanIn->lte($scheduledIn->copy()->addSeconds(self::GRACE_SECONDS))
            ? 'tepat_waktu'
            : 'terlambat';
    }

    public function calculateLateMinutes(Carbon $actualScanIn, Carbon $scheduledIn): int
    {
        $lateSeconds = $this->calculateLateSeconds($actualScanIn, $scheduledIn);
        return $lateSeconds > 0 ? (int) ceil($lateSeconds / 60) : 0;
    }

    public function calculateLateSeconds(Carbon $actualScanIn, Carbon $scheduledIn): int
    {
        $graceLimit = $scheduledIn->copy()->addSeconds(self::GRACE_SECONDS);
        if ($actualScanIn->lte($graceLimit)) {
            return 0;
        }

        return $graceLimit->diffInSeconds($actualScanIn);
    }

    public function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }

    private function bestScheduleForScan(string $dayType, Carbon $actualScanIn): ?Schedule
    {
        $candidates = Schedule::all()->filter(function (Schedule $schedule) use ($dayType) {
            $scheduleDayType = $schedule->day_type ?? 'weekday';

            if ($dayType === 'friday') {
                return in_array($scheduleDayType, ['weekday', 'friday'], true);
            }

            return $scheduleDayType === $dayType;
        });

        if ($candidates->isEmpty()) {
            return null;
        }

        $scanHour = (int) $actualScanIn->format('H');
        $best = null;
        $bestDiff = PHP_INT_MAX;

        foreach ($candidates as $schedule) {
            $scheduleHour = (int) Carbon::parse($schedule->time_in)->format('H');
            $diff = min(abs($scanHour - $scheduleHour), 24 - abs($scanHour - $scheduleHour));

            if ($diff < $bestDiff) {
                $best = $schedule;
                $bestDiff = $diff;
            }
        }

        return $best;
    }

    private function getCheckDate(Check $check): ?Carbon
    {
        if ($check->attendance_time) {
            return Carbon::parse($check->attendance_time)->startOfDay();
        }

        if ($check->leave_time) {
            return Carbon::parse($check->leave_time)->startOfDay();
        }

        return $check->created_at ? Carbon::parse($check->created_at)->startOfDay() : null;
    }

    private function buildNotes(Carbon $date, Schedule $schedule, Check $check): ?string
    {
        if ($date->dayOfWeek === Carbon::SUNDAY && HolidayOverride::where('date', $date->toDateString())->exists()) {
            return 'Minggu dihitung karena ada holiday override.';
        }

        if (!$check->attendance_time) {
            return 'Tidak ada scan masuk, hanya data scan keluar atau input manual parsial.';
        }

        return 'Shift: ' . $schedule->slug;
    }
}
