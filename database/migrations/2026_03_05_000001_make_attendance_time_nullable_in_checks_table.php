<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeAttendanceTimeNullableInChecksTable extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE checks MODIFY attendance_time TIMESTAMP NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE checks MODIFY attendance_time TIMESTAMP NOT NULL');
    }
}