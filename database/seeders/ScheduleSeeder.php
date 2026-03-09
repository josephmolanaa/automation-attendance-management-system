<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    public function run()
    {

        DB::table('schedule_employees')->delete();
        DB::table('schedules')->delete();

        DB::table('schedules')->insert([
            [
                'id'         => 1,
                'slug'       => 'SHIFT_1_WEEKDAY',
                'day_type'   => 'weekday',        // Senin - Jumat
                'time_in'    => '08:00:00',
                'time_out'   => '17:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 2,
                'slug'       => 'SHIFT_2_WEEKDAY',
                'day_type'   => 'weekday',       // Senin - Jumat Malam
                'time_in'    => '19:00:00',
                'time_out'   => '03:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // SHIFT MALAM
            [
                'id'         => 3,
                'slug'       => 'SHIFT_1_WEEKEND',
                'day_type'   => 'saturday',        // Sabtu pagi
                'time_in'    => '08:00:00',
                'time_out'   => '13:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 4,
                'slug'       => 'SHIFT_2_WEEKEND',
                'day_type'   => 'saturday',         // Sabtu Siang
                'time_in'    => '13:00:00',
                'time_out'   => '17:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 5,
                'slug'       => 'LEMBUR_SHIFT_1',
                'day_type'   => 'holiday',         // Minggu / Tanggal Merah pagi
                'time_in'    => '08:00:00',
                'time_out'   => '17:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 6,
                'slug'       => 'LEMBUR_SHIFT_2',
                'day_type'   => 'holiday',         // Minggu / Tanggal Merah malam
                'time_in'    => '19:00:00',
                'time_out'   => '03:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('✅ Schedules berhasil diisi! (6 Shift)');
    }
}