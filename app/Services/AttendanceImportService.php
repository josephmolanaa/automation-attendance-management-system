<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AttendanceImportService
{
    /**
     * Memproses semua baris attendance dari CSV untuk satu employee.
     * Menggunakan pendekatan Collections untuk memproses semalam (overnight return).
     *
     * @param int $employeeId
     * @param Collection $rows
     * @return void
     */
    private function processEmployeeRows(int $employeeId, Collection $rows): void
    {
        $processedRows = $rows->toArray();
        $recordsToCreate = []; // Optional tracking in memory if needed

        for ($i = 0; $i < count($processedRows); $i++) {
            $currentRow = $processedRows[$i];
            $prevRow = $i > 0 ? $processedRows[$i - 1] : null;

            $isOvernight = $this->isOvernightReturn($currentRow, $prevRow);

            if ($isOvernight) {
                // Determine check_in time for the night shift:
                // Jika scan2 baris sebelumnya sudah dikonsumsi sebagai check-in, gunakan scan2, 
                // jika belum (hanya scan1), gunakan scan1.
                $prevCheckInTime = ($prevRow['scan2_consumed'] ?? false) ? $prevRow['scan2'] : $prevRow['scan1'];

                // B.1 Buat / Update satu Attendance record untuk tanggal baris ke-(i-1)
                $checkOutDatetime = Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan1'])->format('Y-m-d H:i:s');
                $checkInDatetime = !empty($prevCheckInTime) ? Carbon::parse($prevRow['date'] . ' ' . $prevCheckInTime)->format('Y-m-d H:i:s') : null;

                $this->createAttendanceRecord($employeeId, [
                    'date' => Carbon::parse($prevRow['date']),
                    'check_in' => $checkInDatetime,
                    'check_out' => $checkOutDatetime,
                    'is_overnight' => true,
                    'needs_review' => false,
                ]);

                // B.2 Jika SCAN2 baris ke-i tidak kosong:
                if (!empty($currentRow['scan2'])) {
                    $this->createAttendanceRecord($employeeId, [
                        'date' => Carbon::parse($currentRow['date']),
                        'check_in' => Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan2'])->format('Y-m-d H:i:s'),
                        'check_out' => null,
                        'is_overnight' => false,
                        'needs_review' => false,
                    ]);

                    // Tandai baris ke-i dengan flag
                    $processedRows[$i]['scan2_consumed'] = true;
                }
                // B.3 Jika SCAN2 baris ke-i kosong: tidak perlu buat record tambahan.

            } else {
                // C. Jika overnight TIDAK terdeteksi (proses normal)

                // Cek apakah baris ke-i ini ADALAH baris yang datanya sudah 
                // diproses (terkonsumsi) oleh step B.2 saat iterasi sebelumnya.
                // Logika Collections manual: if row is consumed, skip.
                if ($currentRow['scan2_consumed'] ?? false) {
                    continue; 
                }

                $ci = !empty($currentRow['scan1']) ? Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan1'])->format('Y-m-d H:i:s') : null;
                $co = !empty($currentRow['scan2']) ? Carbon::parse($currentRow['date'] . ' ' . $currentRow['scan2'])->format('Y-m-d H:i:s') : null;

                $this->createAttendanceRecord($employeeId, [
                    'date' => Carbon::parse($currentRow['date']),
                    'check_in' => $ci,
                    'check_out' => $co,
                    'is_overnight' => false,
                    'needs_review' => empty($currentRow['scan1']) && !empty($currentRow['scan2']),
                ]);
            }
        }
    }

    /**
     * Cek apakah baris ke-i adalah overnight return dari baris prev.
     * Kondisi A (1-4).
     */
    private function isOvernightReturn(array $currentRow, ?array $prevRow): bool
    {
        if (!$prevRow) {
            return false;
        }

        $cScan1 = $currentRow['scan1'] ?? '';
        if (empty($cScan1)) {
            return false;
        }

        // 1. SCAN1 baris ke-i masuk window jam 00:30–06:30
        $t1 = Carbon::parse($cScan1)->format('H:i:s');
        if ($t1 < '00:30:00' || $t1 > '06:30:00') {
            return false;
        }

        // 2. SCAN2 baris ke-(i-1) kosong/null ATAU baris ke-(i-1) sudah ditandai scan2_consumed = true
        $pScan2 = $prevRow['scan2'] ?? '';
        $pConsumed = $prevRow['scan2_consumed'] ?? false;
        if (!empty($pScan2) && !$pConsumed) {
            return false;
        }

        // 3. Waktu masuk shift malam baris ke-(i-1) masuk window jam 17:00–23:59
        // Logika disesuaikan: Check-in malam bisa dari scan1 (normal) atau scan2 (continuous overnight check-in)
        $pCheckIn = $pConsumed ? $pScan2 : ($prevRow['scan1'] ?? '');
        if (empty($pCheckIn)) {
            return false;
        }
        $t2 = Carbon::parse($pCheckIn)->format('H:i:s');
        if ($t2 < '17:00:00' || $t2 > '23:59:59') {
            return false;
        }

        // 4. Selisih tanggal tepat 1 hari
        $diff = Carbon::parse($currentRow['date'])->diffInDays(Carbon::parse($prevRow['date']));
        if ($diff !== 1) {
            return false;
        }

        return true;
    }

    /**
     * Helper: Buat atau update Attendance record.
     * Mencegah N+1 dengan menggunakan updateOrCreate bawaan Eloquent atau upsert (jika didukung).
     */
    private function createAttendanceRecord(int $employeeId, array $data): void
    {
        // Menyimpan status semalam dan needs review, update check_out dll
        Attendance::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $data['date']->format('Y-m-d')
            ],
            [
                // Cek agar check_in sebelumnya tak tertimpa jika null.
                // Jika ingin upsert secara rigid, pastikan check_in hanya ditimpa jika array menyediakannya
                'check_in' => $data['check_in'] ?? \DB::raw('check_in'),
                'check_out' => $data['check_out'],
                'is_overnight' => $data['is_overnight'] ?? false,
                'needs_review' => $data['needs_review'] ?? false,
            ]
        );
    }
}
