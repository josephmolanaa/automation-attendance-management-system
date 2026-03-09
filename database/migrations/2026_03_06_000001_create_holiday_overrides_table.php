<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHolidayOverridesTable2026 extends Migration
{
    public function up()
    {
        Schema::create('holiday_overrides', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('original_type');
            $table->string('override_type');
            $table->integer('schedule_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('holiday_overrides');
    }
}