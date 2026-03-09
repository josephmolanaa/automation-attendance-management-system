<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeAttendanceTimeNullableInChecksTable extends Migration
{
    public function up()
    {
        Schema::table('checks', function (Blueprint $table) {
            $table->timestamp('attendance_time')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('checks', function (Blueprint $table) {
            $table->timestamp('attendance_time')->nullable(false)->change();
        });
    }
}