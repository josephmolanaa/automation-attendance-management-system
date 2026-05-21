<?php

namespace App\Exports;

use App\Models\Check;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\HolidayOverride;
use App\Models\IzinDanCuti;
use App\Services\HolidayService;
use App\Services\ShiftDetectionService;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Events\AfterSheet;

class SheetReportExport implements FromArray, WithEvents
{
    protected $bulan;
    protected $tahun;
    protected $rowMeta = [];

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function array(): array
    {
        $bulan = $this->bulan;
        $tahun = $this->tahun;

        $employees = Employee::orderBy('id')->get();
        $allSchedules = Schedule::all();
        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;

        $monthStart = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
        $monthEnd = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->endOfDay();

        $checks = Check::where(function ($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('attendance_time', [$monthStart, $monthEnd])
                    ->orWhereBetween('leave_time', [$monthStart, $monthEnd]);
            })
            ->get()->groupBy('emp_id');

        $leaves = IzinDanCuti::whereYear('leave_date', $tahun)
            ->whereMonth('leave_date', $bulan)
            ->get()->groupBy('emp_id');

        $rows = [];
        $rows[] = [' ', '', '', '', '', '', '', '', '', '', '', '']; // Row 1
        $this->rowMeta[] = ['type' => 'empty'];

        $rows[] = [' ', '', '', '', '', '', '', '', '', '', '', '']; // Row 2
        $this->rowMeta[] = ['type' => 'empty'];

        $rows[] = ['', '', 'DATA SCANLOG']; // Row 3
        $this->rowMeta[] = ['type' => 'title'];

        $headerRow = ['', '', 'NIP', 'NAMA', 'HARI', 'TANGGAL', 'SCAN 1', 'SCAN 2', 'LATE TIME', 'NORMAL ', 'DOUBLE ', 'MINGGU '];

        foreach ($employees as $index => $employee) {
            $empChecks = $checks->get($employee->id, collect());
            $empLeaves = $leaves->get($employee->id, collect());

            $checksByDate = $empChecks->groupBy(function ($c) {
                $scanDate = $c->attendance_time ?: $c->leave_time;
                return Carbon::parse($scanDate)->format('Y-m-d');
            });
            $leavesByDate = $empLeaves->keyBy(function ($l) {
                return Carbon::parse($l->leave_date)->format('Y-m-d');
            });

            if ($index > 0) {
                $rows[] = [' ', '', '', '', '', '', '', '', '', '', '', ''];
                $this->rowMeta[] = ['type' => 'empty'];
                $rows[] = [' ', '', '', '', '', '', '', '', '', '', '', ''];
                $this->rowMeta[] = ['type' => 'empty'];
            }

            // Header per karyawan
            $rows[] = $headerRow;
            $this->rowMeta[] = ['type' => 'header'];

            $totalNormal = 0;
            $totalDouble = 0;
            $totalMinggu = 0;
            $totalIzin = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = Carbon::createFromDate($tahun, $bulan, $d)->format('Y-m-d');
                $dateObj = Carbon::parse($dateStr);
                $dayName = strtoupper($dateObj->locale('id')->dayName);
                $dayOfWeek = $dateObj->dayOfWeek;

                $dayChecks = $checksByDate->get($dateStr, collect());
                $leave = $leavesByDate->get($dateStr);

                $scan1 = $dayChecks->sortBy('attendance_time')->first();
                $scan1Time = $scan1 && $scan1->attendance_time
                    ? Carbon::parse($scan1->attendance_time)->format('H:i:s') : null;
                $scan2Time = $scan1 && $scan1->leave_time
                    ? Carbon::parse($scan1->leave_time)->format('H:i:s') : null;

                $normal = null;
                $double = null;
                $minggu = null;
                $lateTime = null;
                $izinCuti = null;
                $isSunday = $dayOfWeek === 0;

                if ($leave) {
                    $izinCuti = strtoupper($leave->reason ?? 'IZIN');
                    $totalIzin++;
                } elseif ($scan1 && $scan1->attendance_time && $scan1->leave_time) {
                    $scanIn = Carbon::parse($scan1->attendance_time);
                    $scanOut = Carbon::parse($scan1->leave_time);

                    // ── Shift detection: baca dari DB, fallback ke service ──
                    $matchedSchedule = null;
                    if ($scan1->schedule_id) {
                        $matchedSchedule = $allSchedules->firstWhere('id', $scan1->schedule_id);
                    }
                    if (!$matchedSchedule) {
                        $matchedSchedule = ShiftDetectionService::detectAsSchedule(
                            $dateStr,
                            $scanIn->format('H:i:s')
                        );
                    }

                    if ($matchedSchedule) {
                        $lateTime = $this->calculateLateTime($dateStr, $scanIn, $matchedSchedule);

                        $schedOut = Carbon::parse($dateStr . ' ' . $matchedSchedule->time_out);
                        if ($schedOut->lt(Carbon::parse($dateStr . ' ' . $matchedSchedule->time_in))) {
                            $schedOut->addDay();
                        }
                        $diffMin = $schedOut->diffInMinutes($scanOut, false);

                        if ($isSunday) {
                            $minggu = 1;
                            $totalMinggu++;
                        } elseif ($diffMin > 15) {
                            $totalHours = floor($diffMin / 60);
                            if ($totalHours <= 3) {
                                $normal = $totalHours;
                                $totalNormal += $normal;
                            } else {
                                $normal = 3;
                                $double = $totalHours - 3;
                                $totalNormal += $normal;
                                $totalDouble += $double;
                            }
                        }
                    }
                }

                $rows[] = [
                    '',
                    '', // A, B
                    $employee->emp_id ?? $employee->id, // C
                    $employee->name, // D
                    $dayName, // E
                    Carbon::parse($dateStr)->format('d/m/Y'), // F
                    $scan1Time, // G
                    $scan2Time, // H
                    $lateTime, // I
                    $normal, // J
                    $double, // K
                    $minggu // L
                ];
                $this->rowMeta[] = ['type' => 'data', 'is_sunday' => $isSunday];
            }

            // NIP before TOTAL
            $rows[] = [' ', '', $employee->emp_id ?? $employee->id, '', '', '', '', '', '', '', '', ''];
            $this->rowMeta[] = ['type' => 'empty_before_total'];

            // Baris TOTAL
            $rows[] = [
                '',
                '',
                '',
                '',
                '',
                '',
                $employee->name, // G
                'TOTAL', // H
                '', // I
                $totalNormal ?: 0, // J
                $totalDouble ?: 0, // K
                $totalMinggu ?: 0 // L
            ];
            $this->rowMeta[] = ['type' => 'total'];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getParent()->getActiveSheet()->setTitle('Sheet Report');

                foreach (range('C', 'L') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                foreach ($this->rowMeta as $i => $meta) {
                    $excelRow = $i + 1;
                    $range = "C{$excelRow}:L{$excelRow}";

                    if ($meta['type'] === 'title') {
                        $sheet->getStyle("C{$excelRow}")->getFont()->setBold(true)->setSize(11);

                    } elseif ($meta['type'] === 'header') {
                        $sheet->getStyle($range)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 9],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '92D050']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                        ]);
                        $sheet->getRowDimension($excelRow)->setRowHeight(16);

                    } elseif ($meta['type'] === 'data') {
                        $sheet->getStyle($range)->applyFromArray([
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                            'font' => ['size' => 9],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        ]);

                        if ($meta['is_sunday']) {
                            // Warna background pink muda untuk baris Minggu
                            $sheet->getStyle($range)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FFE4E1');
                            // Font merah di kolom E (Hari) dan F (Tanggal)
                            $sheet->getStyle("E{$excelRow}")->getFont()->getColor()->setRGB('FF0000');
                            $sheet->getStyle("E{$excelRow}")->getFont()->setBold(true);
                            $sheet->getStyle("F{$excelRow}")->getFont()->getColor()->setRGB('FF0000');
                        }
                        $sheet->getRowDimension($excelRow)->setRowHeight(14);

                    } elseif ($meta['type'] === 'total') {
                        $totalRange = "G{$excelRow}:L{$excelRow}";
                        $sheet->getStyle($totalRange)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00B0F0']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                        ]);
                        $sheet->getRowDimension($excelRow)->setRowHeight(16);
                    }
                }
            }
        ];
    }

    private function calculateLateTime(string $dateStr, Carbon $scanIn, Schedule $schedule): ?string
    {
        $schedIn = Carbon::parse($dateStr . ' ' . $schedule->time_in);
        $totalSeconds = $schedIn->diffInSeconds($scanIn, false);

        if ($totalSeconds <= 60) {
            return null;
        }

        $lateHours = floor($totalSeconds / 3600);
        $lateMins = floor(($totalSeconds % 3600) / 60);
        $lateSecs = $totalSeconds % 60;

        return sprintf('%02d:%02d:%02d', $lateHours, $lateMins, $lateSecs);
    }
}
