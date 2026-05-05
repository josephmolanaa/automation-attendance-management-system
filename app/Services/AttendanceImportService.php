<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AttendanceImportService
{
    private array $previewBuffer = [];

    /**
     * Memproses baris CSV menjadi array preview tanpa menyimpannya ke database.
     * 
     * @param Collection $rows
     * @return array
     */
    public function parsePreviewMode(Collection $rows): array
    {
        // Ganti properti date/tanggal agar konsisten buat logic
        $mappedRows = $rows->map(function ($r) {
            $r['date'] = $r['tanggal'] ?? $r['date'];
            return $r;
        });

        $this->previewBuffer = [];
        $this->processEmployeeRows(0, $mappedRows, true); // use flag parameter

        // Convert the preview buffer format back to frontend expected keys
        $frontendFormat = [];
        foreach ($this->previewBuffer as $rec) {
            $dateOnly = Carbon::parse($rec['date'])->format('Y-m-d');
            $scan1Time = $rec['check_in'] ? Carbon::parse($rec['check_in'])->format('H:i:s') : '';
            $scan2Time = $rec['check_out'] ? Carbon::parse($rec['check_out'])->format('H:i:s') : '';

            $frontendFormat[] = [
                'tanggal' => $dateOnly,
                'scan1'   => $scan1Time,
                'scan2'   => $scan2Time,
                'is_overnight' => $rec['is_overnight'],
                'needs_review' => $rec['needs_review'],
                'shift_hint' => $rec['shift_hint']
            ];
        }

        return $frontendFormat;
    }

    /**
     * Memproses semua baris attendance dari CSV untuk satu employee.
     *
     * @param int $employeeId
     * @param Collection $rows
     * @param bool $isPreviewMode
     * @return void
     */
    public function processEmployeeRows(int $employeeId, Collection $rows, bool $isPreviewMode = false): void
    {
        // Deteksi pola shift PER MINGGU (bukan per bulan)
        $weeklyShifts = $this->detectWeeklyShifts($rows);
        $processedRows = $rows->toArray();

        for ($i = 0; $i < count($processedRows); $i++) {
            $currentRow = $processedRows[$i];
            $prevRow = $i > 0 ? $processedRows[$i - 1] : null;

            // Ambil shift hint untuk tanggal ini dari weekly map
            $streakHint = $weeklyShifts[$currentRow['date']] ?? 'mixed';

            $needsReview = false;
            $isOvernight = $this->isOvernightReturn($currentRow, $prevRow, $streakHint, $needsReview);

            if ($isOvernight) {
                // CASE A: Baris ini adalah overnight return dari shift malam kemarin
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
                ], $isPreviewMode);

                if (!empty($currentRow['scan2'])) {
                    $cScan3 = $currentRow['scan3'] ?? null;
                    $newCheckOut = !empty($cScan3) ? Carbon::parse($currentRow['date'] . ' ' . $cScan3)->format('Y-m-d H:i:s') : null;

                    $this->createAttendanceRecord($employeeId, [
                        'date' => Carbon::parse($currentRow['date']),
                        'check_in' => Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan2'])->format('Y-m-d H:i:s'),
                        'check_in_date' => Carbon::parse($currentRow['date']),
                        'check_out' => $newCheckOut,
                        'check_out_date' => $newCheckOut ? Carbon::parse($currentRow['date']) : null,
                        'is_overnight' => false,
                        'needs_review' => false,
                        'shift_hint' => $streakHint
                    ], $isPreviewMode);
                    $processedRows[$i]['scan2_consumed'] = true;
                }
            } else {
                // CASE B: Baris normal (bukan overnight return)
                if ($currentRow['scan2_consumed'] ?? false) {
                    continue; 
                }

                $ci = !empty($currentRow['scan1']) ? Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan1'])->format('Y-m-d H:i:s') : null;
                $co = !empty($currentRow['scan2']) ? Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan2'])->format('Y-m-d H:i:s') : null;
                
                $baseReview = empty($currentRow['scan1']) && !empty($currentRow['scan2']);
                
                // Cek scan masuk tunggal yang anomali (orphaned checkout)
                $ciTime = !empty($currentRow['scan1']) ? Carbon::parse($currentRow['scan1'])->format('H:i:s') : null;
                $isOrphanedCheckout = $ciTime && $ciTime >= '00:30:00' && $ciTime <= '06:30:00';

                // Cek apakah kemarin sudah punya check_out — jika sudah, ini bukan orphaned checkout
                // melainkan hanya early check-in hari ini (misal datang pagi-pagi di Sabtu)
                if ($isOrphanedCheckout && $isPreviewMode) {
                    $yesterdayStr = Carbon::parse($currentRow['date'])->subDay()->format('Y-m-d');
                    if (isset($this->previewBuffer[$yesterdayStr]) && !empty($this->previewBuffer[$yesterdayStr]['check_out'])) {
                        $isOrphanedCheckout = false; // kemarin sudah ada check_out, treat as normal early check-in
                    }
                }

                if ($isOrphanedCheckout) {
                    // Ini adalah orphaned checkout (sisa shift malam sebelumnya yang baris pertamanya terpotong)
                    $yesterday = Carbon::parse($currentRow['date'])->subDay();
                    $this->createAttendanceRecord($employeeId, [
                        'date' => $yesterday,
                        'check_in' => null, 
                        'check_in_date' => null,
                        'check_out' => Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan1'])->format('Y-m-d H:i:s'),
                        'check_out_date' => Carbon::parse($currentRow['date']),
                        'is_overnight' => true,
                        'needs_review' => true,
                        'shift_hint' => $streakHint
                    ], $isPreviewMode);

                    // Shift untuk hari ini bergeser ke scan2 dan scan3
                    $ci = !empty($currentRow['scan2']) ? Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan2'])->format('Y-m-d H:i:s') : null;
                    $co = !empty($currentRow['scan3']) ? Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan3'])->format('Y-m-d H:i:s') : null;
                    
                    if ($ci && !$co) {
                        $baseReview = true;
                    }
                } else {
                    // Jika tidak overnight, tapi ada scan3, gunakan scan3 sebagai co
                    if (!empty($currentRow['scan3']) && !empty($currentRow['scan2'])) {
                        $co = Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan3'])->format('Y-m-d H:i:s');
                    }
                    
                    if (!empty($ci) && empty($co)) {
                        if ($streakHint === 'shift_1' && $ciTime >= '11:00:00') {
                            $co = $ci; $ci = null; $baseReview = true;
                        } elseif ($streakHint === 'shift_2' && $ciTime <= '12:00:00') {
                            $co = $ci; $ci = null; $baseReview = true;
                        }
                    }
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
                ], $isPreviewMode);
            }
        }
    }

    /**
     * Deteksi pola shift PER MINGGU (ISO week).
     * 
     * Return: array map [tanggal => shift_hint]
     * Contoh: ['2026-04-01' => 'shift_1', '2026-04-07' => 'shift_2', ...]
     *
     * @param Collection $rows
     * @return array
     */
    private function detectWeeklyShifts(Collection $rows): array
    {
        $totalRows = $rows->count();
        if ($totalRows < 3) {
            // Insufficient data — beri semua tanggal hint 'insufficient_data'
            $map = [];
            foreach ($rows as $row) {
                $map[$row['date']] = 'insufficient_data';
            }
            return $map;
        }

        // 1. Group by ISO week
        $weekGroups = [];
        foreach ($rows as $row) {
            $date = Carbon::parse($row['date']);
            $weekKey = $date->isoWeekYear . '-W' . str_pad($date->isoWeek, 2, '0', STR_PAD_LEFT);
            
            if (!isset($weekGroups[$weekKey])) {
                $weekGroups[$weekKey] = ['dates' => [], 'day' => 0, 'night' => 0, 'over' => 0, 'total' => 0];
            }
            $weekGroups[$weekKey]['dates'][] = $row['date'];
            
            if (empty($row['scan1'])) continue;
            
            $t = Carbon::parse($row['scan1'])->format('H:i:s');
            $weekGroups[$weekKey]['total']++;
            
            if ($t >= '06:31:00' && $t <= '16:59:59') {
                $weekGroups[$weekKey]['day']++;
            } elseif ($t >= '17:00:00' && $t <= '23:59:59') {
                $weekGroups[$weekKey]['night']++;
            } elseif ($t >= '00:30:00' && $t <= '06:30:00') {
                $weekGroups[$weekKey]['over']++;
            }
        }

        // 2. Per minggu, tentukan shift dominan
        $map = [];
        foreach ($weekGroups as $weekKey => $group) {
            $total = $group['total'];
            $shift = 'mixed';
            
            if ($total > 0) {
                $shift2Count = $group['night'] + $group['over'];
                
                if ($shift2Count / $total >= 0.60) {
                    $shift = 'shift_2';
                } elseif ($group['day'] / $total >= 0.60) {
                    $shift = 'shift_1';
                }
            }
            
            // Assign shift hint ke semua tanggal di minggu ini
            foreach ($group['dates'] as $date) {
                $map[$date] = $shift;
            }
        }

        return $map;
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
    private function createAttendanceRecord(int $employeeId, array $data, bool $isPreviewMode = false): void
    {
        if ($isPreviewMode) {
            // Skip hari kosong (tidak ada scan sama sekali)
            if (empty($data['check_in']) && empty($data['check_out'])) {
                return;
            }

            $d = $data['date']->format('Y-m-d');
            if (isset($this->previewBuffer[$d])) {
                // Jangan timpa check_in jika null, mensimulasikan DB::raw('check_in')
                if (empty($data['check_in']) && !empty($this->previewBuffer[$d]['check_in'])) {
                    $data['check_in'] = $this->previewBuffer[$d]['check_in'];
                }
                // Jangan timpa check_out yang sudah ada dengan value baru
                // Ini mencegah orphaned checkout menimpa check_out yang valid
                if (!empty($this->previewBuffer[$d]['check_out']) && !empty($data['check_out'])) {
                    unset($data['check_out']);
                }
                $this->previewBuffer[$d] = array_merge($this->previewBuffer[$d], $data);
            } else {
                $this->previewBuffer[$d] = $data;
            }
            return;
        }

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
