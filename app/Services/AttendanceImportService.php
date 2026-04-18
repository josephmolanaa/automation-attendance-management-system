<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AttendanceImportService
{
    /**
     * Memproses semua baris attendance dari CSV untuk satu employee.
     *
     * @param int $employeeId
     * @param Collection $rows
     * @return void
     */
    public function processEmployeeRows(int $employeeId, Collection $rows): void
    {
        // 3. Deteksi pola shift
        $streakHint = $this->detectShiftStreak($rows);
        $processedRows = $rows->toArray();

        for ($i = 0; $i < count($processedRows); $i++) {
            $currentRow = $processedRows[$i];
            $prevRow = $i > 0 ? $processedRows[$i - 1] : null;

            $needsReview = false;
            $isOvernight = $this->isOvernightReturn($currentRow, $prevRow, $streakHint, $needsReview);

            if ($isOvernight) {
                // CASE A
                $prevCheckInTime = ($prevRow['scan2_consumed'] ?? false) ? $prevRow['scan2'] : $prevRow['scan1'];

                $checkOutDatetime = Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan1'])->format('Y-m-d H:i:s');
                $checkInDatetime = !empty($prevCheckInTime) ? Carbon::parse($prevRow['date'] . ' ' . $prevCheckInTime)->format('Y-m-d H:i:s') : null;

                $this->createAttendanceRecord($employeeId, [
                    'date' => Carbon::parse($prevRow['date']),
                    'check_in' => $checkInDatetime,
                    'check_in_date' => Carbon::parse($prevRow['date']),
                    'check_out' => $checkOutDatetime,
                    'check_out_date' => Carbon::parse($currentRow['date']),
                    'is_overnight' => true,
                    'needs_review' => $needsReview,
                    'shift_hint' => $streakHint
                ]);

                if (!empty($currentRow['scan2'])) {
                    $this->createAttendanceRecord($employeeId, [
                        'date' => Carbon::parse($currentRow['date']),
                        'check_in' => Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan2'])->format('Y-m-d H:i:s'),
                        'check_in_date' => Carbon::parse($currentRow['date']),
                        'check_out' => null,
                        'check_out_date' => null,
                        'is_overnight' => false,
                        'needs_review' => false,
                        'shift_hint' => $streakHint
                    ]);
                    $processedRows[$i]['scan2_consumed'] = true;
                }
            } else {
                // CASE B
                if ($currentRow['scan2_consumed'] ?? false) {
                    continue; 
                }

                $ci = !empty($currentRow['scan1']) ? Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan1'])->format('Y-m-d H:i:s') : null;
                $co = !empty($currentRow['scan2']) ? Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan2'])->format('Y-m-d H:i:s') : null;
                
                $baseReview = empty($currentRow['scan1']) && !empty($currentRow['scan2']);
                
                // Tambahan: cek scan masuk yang sangat pagi tapi gagal overnight (karena bukan baris lanjutan)
                $ciTime = !empty($currentRow['scan1']) ? Carbon::parse($currentRow['scan1'])->format('H:i') : null;
                if ($ciTime && $ciTime >= '00:30' && $ciTime <= '06:30') {
                    $baseReview = true;
                }
                
                $finalReview = $baseReview || $needsReview;

                $this->createAttendanceRecord($employeeId, [
                    'date' => Carbon::parse($currentRow['date']),
                    'check_in' => $ci,
                    'check_in_date' => $ci ? Carbon::parse($currentRow['date']) : null,
                    'check_out' => $co,
                    'check_out_date' => $co ? Carbon::parse($currentRow['date']) : null,
                    'is_overnight' => false,
                    'needs_review' => $finalReview,
                    'shift_hint' => $streakHint
                ]);
            }
        }
    }

    /**
     * Deteksi pola shift
     *
     * @param Collection $rows
     * @return string
     */
    private function detectShiftStreak(Collection $rows): string
    {
        $total = $rows->count();
        if ($total < 3) {
            return 'insufficient_data';
        }

        $day = 0;
        $night = 0;
        $over = 0;

        foreach ($rows as $row) {
            if (empty($row['scan1'])) continue;
            
            $t = Carbon::parse($row['scan1'])->format('H:i:s');
            if ($t >= '06:31:00' && $t <= '16:59:59') {
                $day++;
            } elseif ($t >= '17:00:00' && $t <= '23:59:59') {
                $night++;
            } elseif ($t >= '00:30:00' && $t <= '06:30:00') {
                $over++;
            }
        }

        $shift2Count = $night + $over;
        if ($shift2Count / $total >= 0.60) {
            return 'shift_2';
        }
        if ($day / $total >= 0.60) {
            return 'shift_1';
        }
        return 'mixed';
    }

    /**
     * Cek apakah baris ke-i adalah overnight return dari baris prev.
     * Kondisi A (1-4).
     */
    private function isOvernightReturn(array $currentRow, ?array $prevRow, string $streakHint, bool &$needsReview = false): bool
    {
        $needsReview = false;
        
        $cScan1 = $currentRow['scan1'] ?? '';
        if (empty($cScan1)) return false;

        $t1 = Carbon::parse($cScan1)->format('H:i:s');
        $cond1 = ($t1 >= '00:30:00' && $t1 <= '06:30:00');

        if (!$prevRow) {
            if ($streakHint === 'shift_1' && $cond1) {
                $needsReview = true;
            }
            return false;
        }

        $pScan2 = $prevRow['scan2'] ?? '';
        $pConsumed = $prevRow['scan2_consumed'] ?? false;
        $cond2 = empty($pScan2) || $pConsumed;

        $pCheckIn = $pConsumed ? $pScan2 : ($prevRow['scan1'] ?? '');
        $t2 = !empty($pCheckIn) ? Carbon::parse($pCheckIn)->format('H:i:s') : null;
        $threshold = ($streakHint === 'shift_2') ? '16:30:00' : '17:00:00';
        $cond3 = $t2 && ($t2 >= $threshold && $t2 <= '23:59:59');

        $diff = Carbon::parse($currentRow['date'])->diffInDays(Carbon::parse($prevRow['date']));
        $cond4 = ($diff === 1);

        if ($streakHint === 'shift_1' && $cond1) {
            $needsReview = true;
            return false;
        }

        if ($cond1 && $cond2 && $cond4) {
            if ($cond3) {
                return true;
            } else if ($streakHint === 'shift_2') {
                // Terdapat toleransi untuk kasus di luar threshold tapi tetap return true
                $needsReview = true;
                return true;
            }
        }

        return false;
    }

    /**
     * Helper: Buat atau update Attendance record.
     */
    private function createAttendanceRecord(int $employeeId, array $data): void
    {
        Attendance::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $data['date']->format('Y-m-d')
            ],
            [
                'check_in' => $data['check_in'] ?? \DB::raw('check_in'),
                'check_out' => $data['check_out'],
                'is_overnight' => $data['is_overnight'] ?? false,
                'needs_review' => $data['needs_review'] ?? false,
                'shift_hint' => $data['shift_hint'] ?? null,
            ]
        );
    }
}
