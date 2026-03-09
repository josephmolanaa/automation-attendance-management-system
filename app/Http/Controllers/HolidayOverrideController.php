<?php

namespace App\Http\Controllers;

use App\Models\HolidayOverride;
use App\Models\Schedule;
use App\Services\HolidayService;
use Illuminate\Http\Request;

class HolidayOverrideController extends Controller
{
    /**
     * Ambil info hari untuk tanggal tertentu (dipanggil via AJAX)
     */
    public function getDateInfo(Request $request)
    {
        $date = $request->date; // format: Y-m-d

        $carbon       = \Carbon\Carbon::parse($date);
        $originalType = HolidayService::getDayType($date);
        $override     = HolidayOverride::where('date', $date)->first();
        $schedules    = Schedule::all();

        // Nama hari dalam bahasa Indonesia
        $hariMap = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        $namaHari = $hariMap[$carbon->dayOfWeek];

        // Cek apakah tanggal merah dari API
        $isApiHoliday = HolidayService::isHoliday($date);
        $schedules    = Schedule::all();

        return response()->json([
            'date'          => $date,
            'nama_hari'     => $namaHari,
            'original_type' => $originalType,
            'is_api_holiday'=> $isApiHoliday,
            'override'      => $override,
            'schedules'     => $schedules,
        ]);
    }

    /**
     * Simpan override untuk tanggal tertentu
     */
    public function store(Request $request)
    {
        $request->validate([
            'date'          => 'required|date',
            'override_type' => 'required|in:weekday,saturday,holiday',
            'schedule_id'   => 'nullable|exists:schedules,id',
            'note'          => 'nullable|string|max:255',
        ]);

        $originalType = HolidayService::getDayType($request->date);

        $override = HolidayOverride::updateOrCreate(
            ['date' => $request->date],
            [
                'original_type' => $originalType,
                'override_type' => $request->override_type,
                'schedule_id'   => $request->schedule_id,
                'note'          => $request->note,
            ]
        );

        // Clear cache holidays untuk bulan ini supaya langsung reflect
        $carbon = \Carbon\Carbon::parse($request->date);
        HolidayService::clearCache($carbon->year, $carbon->month);

        return response()->json([
            'success' => true,
            'message' => 'Override berhasil disimpan!',
            'data'    => $override,
        ]);
    }

    /**
     * Hapus override untuk tanggal tertentu (kembalikan ke default API)
     */
    public function destroy(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        HolidayOverride::where('date', $request->date)->delete();

        $carbon = \Carbon\Carbon::parse($request->date);
        HolidayService::clearCache($carbon->year, $carbon->month);

        return response()->json([
            'success' => true,
            'message' => 'Override dihapus, kembali ke default.',
        ]);
    }

    /**
     * Ambil semua tanggal merah + override untuk 1 bulan (untuk kalender)
     */
    public function getMonthData(Request $request)
    {
        $year  = $request->year  ?? now()->year;
        $month = $request->month ?? now()->month;

        $holidays  = HolidayService::getHolidays($year, $month);
        $overrides = HolidayOverride::whereYear('date', $year)
                        ->whereMonth('date', $month)
                        ->get()
                        ->keyBy('date');

        return response()->json([
            'holidays'  => $holidays,
            'overrides' => $overrides,
        ]);
    }
}