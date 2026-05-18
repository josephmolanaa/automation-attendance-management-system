<?php

namespace App\Exports\Sheets;

use App\Models\LatenessRecord;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LatenessRecapSheet implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    use LatenessSheetHelpers;

    private array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'REKAP KARYAWAN';
    }

    public function array(): array
    {
        $rows = [
            ['NIP', 'NAMA', 'JABATAN', 'TOTAL HARI TERLAMBAT', 'TOTAL MENIT', 'TOTAL DURASI', 'RATA-RATA MENIT'],
        ];

        $records = LatenessRecord::with('employee')
            ->late()
            ->forMonth($this->filters['tahun'], $this->filters['bulan'])
            ->get();

        $records->groupBy('employee_id')
            ->map(function ($items) {
                $employee = $items->first()->employee;
                $totalMinutes = $items->sum('late_minutes');

                return [
                    optional($employee)->emp_id ?? optional($employee)->id,
                    optional($employee)->name,
                    optional($employee)->position ?? '-',
                    $items->count(),
                    $totalMinutes,
                    $this->formatMinutes((int) $totalMinutes),
                    $items->count() > 0 ? round($totalMinutes / $items->count(), 1) : 0,
                ];
            })
            ->sortByDesc(fn ($row) => $row[3])
            ->each(function ($row) use (&$rows) {
                $rows[] = $row;
            });

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:G1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= 2) {
                    $sheet->getStyle("A2:G{$highestRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);

                    for ($row = 2; $row <= $highestRow; $row++) {
                        if ((int) $sheet->getCell("D{$row}")->getValue() > 3) {
                            $sheet->getStyle("D{$row}:G{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('F8D7DA');
                        }
                    }
                }
            },
        ];
    }
}
