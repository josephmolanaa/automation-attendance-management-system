<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayService
{
    const API_PRIMARY  = 'https://libur.deno.dev/api';
    const API_FALLBACK = 'https://api-hari-libur.vercel.app/api';

    public static function isHoliday(string $date): bool {
        $carbon = \Carbon\Carbon::parse($date);
        return in_array($date, self::getHolidays($carbon->year, $carbon->month));
    }

    public static function getHolidays(int $year, int $month): array
    {
        $cacheKey = "holidays_id_{$year}_{$month}";
        return Cache::remember($cacheKey, now()->addDays(30), function () use ($year, $month) {
            $dates = self::fetchPrimary($year, $month);
            if (empty($dates)) {
                $dates = self::fetchFallback($year, $month);
            }
            return $dates;
        });
    }

    private static function fetchPrimary(int $year, int $month): array
    {
        try {
            $response = Http::timeout(5)->get(self::API_PRIMARY, [
                'year' => $year, 'month' => $month,
            ]);
            if ($response->successful()) {
                return collect($response->json())
                    ->where('is_national_holiday', true)
                    ->pluck('holiday_date')->filter()->values()->toArray();
            }
        } catch (\Exception $e) {
            Log::warning('[HolidayService] Primary API gagal: ' . $e->getMessage());
        }
        return [];
    }

    private static function fetchFallback(int $year, int $month): array
    {
        try {
            $response = Http::timeout(5)->get(self::API_FALLBACK, [
                'year' => $year, 'month' => $month,
            ]);
            if ($response->successful()) {
                $json = $response->json();
                $data = $json['data'] ?? [];
                return collect($data)->pluck('date')->filter()->values()->toArray();
            }
        } catch (\Exception $e) {
            Log::warning('[HolidayService] Fallback API gagal: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Deteksi tipe hari — cek override dulu, baru API, baru hari kalender.
     */
    public static function getDayType(string $date): string
    {
        // 1. Cek override dari admin
        $override = \App\Models\HolidayOverride::where('date', $date)->first();
        if ($override) {
            return $override->override_type;
        }

        $carbon    = \Carbon\Carbon::parse($date);
        $dayOfWeek = $carbon->dayOfWeek;

        // 2. Minggu atau tanggal merah API → holiday
        if ($dayOfWeek === 0 || self::isHoliday($date)) {
            return 'holiday';
        }

        // 3. Sabtu → saturday
        if ($dayOfWeek === 6) {
            return 'saturday';
        }

        return 'weekday';
    }

    public static function clearCache(?int $year = null, ?int $month = null): void
    {
        if ($year && $month) {
            Cache::forget("holidays_id_{$year}_{$month}");
            return;
        }
        foreach ([date('Y'), date('Y') + 1] as $y) {
            for ($m = 1; $m <= 12; $m++) {
                Cache::forget("holidays_id_{$y}_{$m}");
            }
        }
    }
}

