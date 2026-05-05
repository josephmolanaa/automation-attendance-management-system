<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddScheduleIdToChecksTable extends Migration
{
    public function up()
    {
        Schema::table('checks', function (Blueprint $table) {
            $table->integer('schedule_id')->unsigned()->nullable()->after('leave_time');
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('checks', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');
        });
    }
}
