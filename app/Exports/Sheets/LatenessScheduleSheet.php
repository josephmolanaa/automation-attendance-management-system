<?php

namespace App\Exports\Sheets;

use App\Models\Schedule;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class LatenessScheduleSheet implements FromArray, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'JADWAL SHIFT';
    }

    public function array(): array
    {
        $rows = [['SLUG', 'DAY TYPE', 'TIME IN', 'TIME OUT']];

        foreach (Schedule::orderBy('id')->get() as $schedule) {
            $rows[] = [
                $schedule->slug,
                $schedule->day_type ?? 'weekday',
                $schedule->time_in,
                $schedule->time_out,
            ];
        }

        return $rows;
    }
}
