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

class LatenessListSheet implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    use LatenessSheetHelpers;

    private array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'ABSEN YG TERLAMBAT';
    }

    public function array(): array
    {
        $monthName = $this->monthName($this->filters['bulan']);
        $rows = [
            [],
            [],
            ['', '', '', "DAFTAR NAMA KARYAWAN YANG TERLAMBAT BULAN {$monthName} {$this->filters['tahun']}"],
            [],
            [],
            ['', '', 'NO', 'NAMA KARYAWAN TELAT', 'HARI', 'TGL', 'JAM', 'TELAT', 'TOTAL'],
            [],
        ];

        $totals = LatenessRecord::late()
            ->forMonth($this->filters['tahun'], $this->filters['bulan'])
            ->get()
            ->groupBy('employee_id')
            ->map(fn ($items) => $items->sum('late_minutes'));

        $records = LatenessRecord::with(['employee'])
            ->late()
            ->forMonth($this->filters['tahun'], $this->filters['bulan'])
            ->orderBy('date')
            ->orderBy('employee_id')
            ->get();

        $no = 1;
        foreach ($records as $record) {
            $rows[] = [
                '',
                '',
                $no++,
                optional($record->employee)->name,
                $this->dayName($record->date),
                $this->formatDate($record->date),
                $this->formatTime($record->actual_scan_in),
                $this->formatMinutes($record->late_minutes),
                $this->formatMinutes((int) ($totals[$record->employee_id] ?? 0)),
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('D3:J3');
                $sheet->getStyle('D3:J3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle('C6:I6')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= 8) {
                    $sheet->getStyle("C8:I{$highestRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                }
            },
        ];
    }
}
