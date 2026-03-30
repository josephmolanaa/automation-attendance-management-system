<?php

use Iluminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeAttendanceTimeNullableInChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE checks MODIFY attendance_time DATETIME NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE checks MODIFY attendance_time DATETIME NOT NULL');
    }
}