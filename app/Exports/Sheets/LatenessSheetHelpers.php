<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;

trait LatenessSheetHelpers
{
    protected function dayName($date): string
    {
        $map = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];
        return $map[Carbon::parse($date)->dayOfWeek] ?? '-';
    }

    protected function monthName(?int $month): string
    {
        if (!$month) {
            return 'SEMUA BULAN';
        }

        $map = [
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
            5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER',
        ];

        return $map[$month] ?? (string) $month;
    }

    protected function periodLabel(array $filters): string
    {
        $month = $this->monthName($filters['bulan'] ?? null);
        $year = $filters['tahun'] ?? 'SEMUA TAHUN';

        return trim("{$month} {$year}");
    }

    protected function applyPeriodFilters($query, array $filters, string $column = 'date')
    {
        if (!empty($filters['tahun'])) {
            $query->whereYear($column, $filters['tahun']);
        }

        if (!empty($filters['bulan'])) {
            $query->whereMonth($column, $filters['bulan']);
        }

        return $query;
    }

    protected function formatDate($date): string
    {
        return Carbon::parse($date)->format('d/m/Y');
    }

    protected function formatTime($dateTime): string
    {
        return $dateTime ? Carbon::parse($dateTime)->format('H:i:s') : '-';
    }

    protected function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        if ($hours > 0 && $remaining > 0) {
            return "{$hours} JAM {$remaining} MENIT";
        }

        if ($hours > 0) {
            return "{$hours} JAM";
        }

        return "{$remaining} MENIT";
    }
}
