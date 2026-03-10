<?php

namespace App\Exports;

use App\Models\Check;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\HolidayOverride;
use App\Models\IzinDanCuti;
use App\Services\HolidayService;
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

        $employees    = Employee::orderBy('name')->get();
        $allSchedules = Schedule::all();
        $daysInMonth  = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;

        $checks = Check::whereYear('attendance_time', $tahun)
            ->whereMonth('attendance_time', $bulan)
            ->get()->groupBy('emp_id');

        $leaves = IzinDanCuti::whereYear('leave_date', $tahun)
            ->whereMonth('leave_date', $bulan)
            ->get()->groupBy('emp_id');

        $rows = [];
        $headerRow = ['NIP', 'NAMA', 'JABATAN/PARTIM', 'HARI', 'TANGGAL', 'SCAN 1', 'SCAN 2', 'SCAN 3', 'NORMAL', 'DOUBLE', 'MINGGU', 'IZIN / CUTI'];

        foreach ($employees as $employee) {
            $empChecks = $checks->get($employee->id, collect());
            $empLeaves = $leaves->get($employee->id, collect());

            $checksByDate = $empChecks->groupBy(function($c) {
                return Carbon::parse($c->attendance_time)->format('Y-m-d');
            });
            $leavesByDate = $empLeaves->keyBy(function($l) {
                return Carbon::parse($l->leave_date)->format('Y-m-d');
            });

            // Header per karyawan
            $rows[] = $headerRow;
            $this->rowMeta[] = ['type' => 'header'];

            $totalNormal = 0; $totalDouble = 0; $totalMinggu = 0; $totalIzin = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr   = Carbon::createFromDate($tahun, $bulan, $d)->format('Y-m-d');
                $dateObj   = Carbon::parse($dateStr);
                $dayName   = strtoupper($dateObj->locale('id')->dayName);
                $dayOfWeek = $dateObj->dayOfWeek;

                $dayChecks = $checksByDate->get($dateStr, collect());
                $leave     = $leavesByDate->get($dateStr);

                $scan1 = $dayChecks->sortBy('attendance_time')->first();
                $scan1Time = $scan1 && $scan1->attendance_time
                    ? Carbon::parse($scan1->attendance_time)->format('H:i:s') : null;
                $scan2Time = $scan1 && $scan1->leave_time
                    ? Carbon::parse($scan1->leave_time)->format('H:i:s') : null;
                $scan3Time = $scan1 && $scan1->second_leave_time
                    ? Carbon::parse($scan1->second_leave_time)->format('H:i:s') : null;

                $normal = null; $double = null; $minggu = null; $izinCuti = null;
                $isSunday = $dayOfWeek === 0;

                if ($leave) {
                    $izinCuti = strtoupper($leave->reason ?? 'IZIN');
                    $totalIzin++;
                } elseif ($scan1 && $scan1->leave_time) {
                    $scanIn  = Carbon::parse($scan1->attendance_time);
                    $scanOut = Carbon::parse($scan1->leave_time);
                    $dayType = HolidayService::getDayType($dateStr);

                    $matchedSchedule = null;
                    $override = HolidayOverride::where('date', $dateStr)->first();
                    if ($override && $override->schedule_id) {
                        $matchedSchedule = Schedule::find($override->schedule_id);
                    } else {
                        $scanHour = (int) $scanIn->format('H');
                        foreach ($allSchedules as $schedule) {
                            $sDayType = $schedule->day_type ?? 'weekday';
                            $dayMatch = match($sDayType) {
                                'saturday' => $dayType === 'saturday',
                                'holiday'  => $dayType === 'holiday',
                                'weekday'  => $dayType === 'weekday',
                                default    => false,
                            };
                            if (!$dayMatch) continue;
                            $schedHour = (int) Carbon::parse($schedule->time_in)->format('H');
                            $diff = min(abs($scanHour - $schedHour), 24 - abs($scanHour - $schedHour));
                            if ($diff <= 3) { $matchedSchedule = $schedule; break; }
                        }
                    }

                    if ($matchedSchedule) {
                        $schedOut = Carbon::parse($dateStr . ' ' . $matchedSchedule->time_out);
                        if ($schedOut->lt(Carbon::parse($dateStr . ' ' . $matchedSchedule->time_in))) {
                            $schedOut->addDay();
                        }
                        $diffMin = $schedOut->diffInMinutes($scanOut, false);

                        if ($isSunday) {
                            $minggu = 1; $totalMinggu++;
                        } elseif ($diffMin > 15) {
                            $totalHours = floor($diffMin / 60);
                            if ($totalHours <= 3) {
                                $normal = $totalHours; $totalNormal += $normal;
                            } else {
                                $normal = 3; $double = $totalHours - 3;
                                $totalNormal += $normal; $totalDouble += $double;
                            }
                        }
                    }
                }

                $rows[] = [
                    $employee->emp_id ?? $employee->id,
                    $employee->name,
                    $employee->position ?? '',
                    $dayName,
                    Carbon::parse($dateStr)->format('d/m/Y'),
                    $scan1Time,
                    $scan2Time,
                    $scan3Time,
                    $normal,
                    $double,
                    $minggu,
                    $izinCuti,
                ];
                $this->rowMeta[] = ['type' => 'data', 'is_sunday' => $isSunday];
            }

            // Baris TOTAL
            $rows[] = ['', '', '', '', '', $employee->name, 'TOTAL', '', $totalNormal ?: null, $totalDouble ?: null, $totalMinggu ?: null, $totalIzin ?: null];
            $this->rowMeta[] = ['type' => 'total'];

            // Baris kosong
            $rows[] = array_fill(0, 12, null);
            $this->rowMeta[] = ['type' => 'empty'];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getParent()->getActiveSheet()->setTitle('Sheet Report');

                foreach (range('A', 'L') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                foreach ($this->rowMeta as $i => $meta) {
                    $excelRow = $i + 1;
                    $range = "A{$excelRow}:L{$excelRow}";

                    if ($meta['type'] === 'header') {
                        $sheet->getStyle($range)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 9],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '92D050']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                        ]);
                        $sheet->getRowDimension($excelRow)->setRowHeight(16);

                    } elseif ($meta['type'] === 'data') {
                        $sheet->getStyle($range)->applyFromArray([
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D0D0']]],
                            'font' => ['size' => 9],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        ]);

                        if ($meta['is_sunday']) {
                            // Warna background pink muda untuk baris Minggu
                            $sheet->getStyle($range)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FFE4E1');
                            // Font merah di kolom D (Hari) dan E (Tanggal)
                            $sheet->getStyle("D{$excelRow}")->getFont()->getColor()->setRGB('FF0000');
                            $sheet->getStyle("D{$excelRow}")->getFont()->setBold(true);
                            $sheet->getStyle("E{$excelRow}")->getFont()->getColor()->setRGB('FF0000');
                        }
                        $sheet->getRowDimension($excelRow)->setRowHeight(14);

                    } elseif ($meta['type'] === 'total') {
                        $sheet->getStyle($range)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00B0F0']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0090C0']]],
                        ]);
                        $sheet->getRowDimension($excelRow)->setRowHeight(16);
                    }
                }
            }
        ];
    }
}