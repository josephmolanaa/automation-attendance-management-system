<?php

namespace App\Exports;

use App\Exports\Sheets\LatenessListSheet;
use App\Exports\Sheets\LatenessRecapSheet;
use App\Exports\Sheets\LatenessScanlogSheet;
use App\Exports\Sheets\LatenessHolidaySheet;
use App\Exports\Sheets\LatenessScheduleSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LatenessExport implements WithMultipleSheets
{
    private array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new LatenessScanlogSheet($this->filters),
            new LatenessListSheet($this->filters),
            new LatenessRecapSheet($this->filters),
            new LatenessScheduleSheet(),
            new LatenessHolidaySheet($this->filters),
        ];
    }
}
