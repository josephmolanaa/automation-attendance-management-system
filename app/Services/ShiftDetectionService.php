<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\HolidayOverride;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ShiftDetectionService
{
    private static $schedulesCache = null;

    /**
     * Detect schedule_id berdasarkan tanggal + jam scan masuk.
     *
     * @param string $date       Format Y-m-d
     * @param string|null $scanTime  Format H:i:s (jam scan masuk)
     * @return int|null           schedule_id
     */
    public static function detect(string $date, ?string $scanTime): ?int
    {
        $schedule = self::detectAsSchedule($date, $scanTime);
        return $schedule?->id;
    }

    /**
     * Detect dan return Schedule model.
     *
     * @param string $date
     * @param string|null $scanTime
     * @return Schedule|null
     */
    public static function detectAsSchedule(string $date, ?string $scanTime): ?Schedule
    {
        $allSchedules = self::getAllSchedules();

        // 1. Cek HolidayOverride — admin bisa override schedule untuk tanggal tertentu
        $override = HolidayOverride::where('date', $date)->first();
        if ($override && $override->schedule_id) {
            return $allSchedules->firstWhere('id', $override->schedule_id);
        }

        // 2. Tentukan day_type dari tanggal
        $dayType = HolidayService::getDayType($date);
        $carbon = Carbon::parse($date);
        $dayOfWeek = $carbon->dayOfWeek;
        $isFriday = $dayOfWeek === Carbon::FRIDAY && $dayType === 'weekday';

        // 3. Filter schedules yang day_type-nya cocok
        // Pada hari Jumat, KEDUA schedule weekday (SHIFT_1) dan friday (SHIFT_2) harus jadi kandidat,
        // agar best-match berdasarkan jam scan bisa menentukan yang benar.
        $candidates = $allSchedules->filter(function ($schedule) use ($dayType, $isFriday) {
            $sDayType = $schedule->day_type ?? 'weekday';
            return match ($sDayType) {
                'friday'   => $isFriday,
                'saturday' => $dayType === 'saturday',
                'holiday'  => $dayType === 'holiday',
                'weekday'  => $dayType === 'weekday', // Jumat tetap include weekday schedules
                default    => false,
            };
        });

        if ($candidates->isEmpty()) {
            return null;
        }

        // 4. Jika tidak ada scan time, return schedule pertama yang cocok
        if (empty($scanTime)) {
            return $candidates->first();
        }

        // 5. Best-match: pilih schedule dengan time_in paling dekat ke jam scan
        $scanHour = (int) Carbon::parse($scanTime)->format('H');
        $bestSchedule = null;
        $bestDiff = PHP_INT_MAX;

        foreach ($candidates as $schedule) {
            $schedHour = (int) Carbon::parse($schedule->time_in)->format('H');
            $diff = min(abs($scanHour - $schedHour), 24 - abs($scanHour - $schedHour));

            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $bestSchedule = $schedule;
            }
        }

        // Fallback: jika best diff terlalu besar (> 5 jam), mungkin salah day_type
        // Tetap return best match tapi log warning
        if ($bestDiff > 5) {
            Log::warning("[ShiftDetection] Large diff={$bestDiff}h for date={$date} scan={$scanTime} → {$bestSchedule->slug}");
        }

        return $bestSchedule;
    }

    /**
     * Cache schedules agar tidak query berulang kali
     */
    private static function getAllSchedules()
    {
        if (self::$schedulesCache === null) {
            self::$schedulesCache = Schedule::all();
        }
        return collect(self::$schedulesCache);
    }

    /**
     * Clear cache (untuk testing)
     */
    public static function clearCache(): void
    {
        self::$schedulesCache = null;
    }
}
