<?php

namespace App\Exports\Sheets;

use App\Models\LatenessRecord;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LatenessScanlogSheet implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    use LatenessSheetHelpers;

    private array $filters;
    private int $dataStartRow = 5;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'DATA SCANLOG';
    }

    public function array(): array
    {
        $rows = [
            ['', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', '', '', '', '', '', '', '', '', '', ''],
            ['', '', 'DATA SCANLOG KETERLAMBATAN'],
            ['', '', 'NIP', 'NAMA', 'JABATAN', 'HARI', 'TANGGAL', 'SCAN MASUK', 'SCAN KELUAR', 'SHIFT', 'JADWAL MASUK', 'STATUS', 'DURASI TERLAMBAT', 'MENIT'],
        ];

        foreach ($this->records() as $record) {
            $rows[] = [
                '',
                '',
                optional($record->employee)->emp_id ?? optional($record->employee)->id,
                optional($record->employee)->name,
                optional($record->employee)->position ?? '-',
                $this->dayName($record->date),
                $this->formatDate($record->date),
                $this->formatTime($record->actual_scan_in),
                $this->formatTime(optional($record->check)->leave_time),
                optional($record->schedule)->slug ?? '-',
                $this->formatTime($record->scheduled_in),
                $this->statusLabel($record->status),
                $record->late_duration ?: '00:00:00',
                $record->late_minutes,
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('C3:N3');
                $sheet->freezePane('C5');

                $sheet->getStyle('C3:N3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('C4:N4')->applyFromArray($this->headerStyle());

                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= $this->dataStartRow) {
                    $sheet->getStyle("C{$this->dataStartRow}:N{$highestRow}")->applyFromArray($this->bodyStyle());

                    for ($row = $this->dataStartRow; $row <= $highestRow; $row++) {
                        $status = (string) $sheet->getCell("L{$row}")->getValue();
                        $color = match ($status) {
                            'TERLAMBAT' => 'F8D7DA',
                            'TEPAT WAKTU' => 'D4EDDA',
                            'TIDAK ADA SCAN' => 'E9ECEF',
                            default => null,
                        };

                        if ($color) {
                            $sheet->getStyle("L{$row}:N{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB($color);
                        }
                    }
                }
            },
        ];
    }

    private function records()
    {
        $query = LatenessRecord::with(['employee', 'check', 'schedule']);

        $this->applyPeriodFilters($query, $this->filters);

        $query->orderBy('date')
            ->orderBy('employee_id');

        if (!empty($this->filters['employee'])) {
            $employee = $this->filters['employee'];
            $query->whereHas('employee', function ($query) use ($employee) {
                $query->where('id', $employee)->orWhere('emp_id', $employee);
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->get();
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'terlambat' => 'TERLAMBAT',
            'tidak_ada_scan' => 'TIDAK ADA SCAN',
            default => 'TEPAT WAKTU',
        };
    }

    private function headerStyle(): array
    {
        return [
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
    }

    private function bodyStyle(): array
    {
        return [
            'font' => ['size' => 9],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
    }
}
