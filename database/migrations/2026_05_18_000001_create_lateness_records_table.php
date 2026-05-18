<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLatenessRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('lateness_records', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id')->unsigned();
            $table->integer('check_id')->unsigned();
            $table->integer('schedule_id')->unsigned()->nullable();
            $table->date('date');
            $table->dateTime('scheduled_in');
            $table->dateTime('actual_scan_in')->nullable();
            $table->enum('status', ['tepat_waktu', 'terlambat', 'tidak_ada_scan']);
            $table->unsignedInteger('late_seconds')->default(0);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->string('late_duration', 20)->default('00:00:00');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('check_id', 'uniq_lateness_check_id');
            $table->index(['date', 'status'], 'idx_lateness_date_status');
            $table->index(['employee_id', 'date'], 'idx_lateness_employee_date');

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('check_id')->references('id')->on('checks')->onDelete('cascade');
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lateness_records');
    }
}
