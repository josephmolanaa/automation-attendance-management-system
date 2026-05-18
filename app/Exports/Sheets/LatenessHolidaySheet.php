<?php

namespace App\Exports\Sheets;

use App\Models\HolidayOverride;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class LatenessHolidaySheet implements FromArray, ShouldAutoSize, WithTitle
{
    private array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'HARI LIBUR';
    }

    public function array(): array
    {
        $rows = [['TANGGAL', 'ORIGINAL TYPE', 'OVERRIDE TYPE', 'SCHEDULE ID', 'CATATAN']];

        $overrides = HolidayOverride::whereYear('date', $this->filters['tahun'])
            ->whereMonth('date', $this->filters['bulan'])
            ->orderBy('date')
            ->get();

        foreach ($overrides as $override) {
            $rows[] = [
                $override->date,
                $override->original_type,
                $override->override_type,
                $override->schedule_id,
                $override->note,
            ];
        }

        return $rows;
    }
}
