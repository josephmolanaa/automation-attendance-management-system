<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmpIdToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('emp_id')->nullable()->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('emp_id');
        });
    }
}
