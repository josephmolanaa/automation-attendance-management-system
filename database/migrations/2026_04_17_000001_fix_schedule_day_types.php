<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixScheduleDayTypes extends Migration
{
    /**
     * Set day_type yang benar untuk setiap schedule berdasarkan slug-nya.
     * Dijalankan sekali jalan untuk fix data production yang day_type-nya masih default 'weekday'.
     */
    public function up()
    {
        $mapping = [
            'SHIFT_1_WEEKDAY' => 'weekday',
            'SHIFT_2_WEEKDAY' => 'weekday',
            'SHIFT_1_WEEKEND' => 'saturday',
            'SHIFT_2_WEEKEND' => 'saturday',
            'LEMBUR_SHIFT_1'  => 'holiday',
            'LEMBUR_SHIFT_2'  => 'holiday',
            'SHIFT_2_FRIDAY'  => 'friday',
        ];

        foreach ($mapping as $slug => $dayType) {
            DB::table('schedules')
                ->where('slug', $slug)
                ->update(['day_type' => $dayType]);
        }
    }

    public function down()
    {
        // Kembalikan semua jadi default 'weekday'
        DB::table('schedules')->update(['day_type' => 'weekday']);
    }
}
