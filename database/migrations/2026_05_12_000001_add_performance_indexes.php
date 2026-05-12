<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     * Add indexes for performance optimization on frequently queried columns
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checks', function (Blueprint $table) {
            // Index untuk filter by employee
            $table->index('emp_id', 'idx_checks_emp_id');
            
            // Index untuk filter by date/time
            $table->index('attendance_time', 'idx_checks_attendance_time');
            $table->index('leave_time', 'idx_checks_leave_time');
            
            // Composite index untuk query yang sering digunakan (emp_id + attendance_time)
            $table->index(['emp_id', 'attendance_time'], 'idx_checks_emp_attendance');
            
            // Index untuk schedule_id jika ada
            if (Schema::hasColumn('checks', 'schedule_id')) {
                $table->index('schedule_id', 'idx_checks_schedule_id');
            }
        });

        Schema::table('employees', function (Blueprint $table) {
            // Index untuk search by name
            $table->index('name', 'idx_employees_name');
            
            // Index untuk email lookup
            if (Schema::hasColumn('employees', 'email')) {
                $table->index('email', 'idx_employees_email');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasTable('attendances')) {
                // Index untuk legacy attendance table
                $table->index('emp_id', 'idx_attendances_emp_id');
                $table->index('attendance_time', 'idx_attendances_time');
                $table->index('attendance_date', 'idx_attendances_date');
            }
        });

        Schema::table('latetimes', function (Blueprint $table) {
            if (Schema::hasTable('latetimes')) {
                $table->index('emp_id', 'idx_latetimes_emp_id');
                $table->index('latetime_date', 'idx_latetimes_date');
            }
        });

        Schema::table('overtimes', function (Blueprint $table) {
            if (Schema::hasTable('overtimes')) {
                $table->index('emp_id', 'idx_overtimes_emp_id');
                $table->index('overtime_date', 'idx_overtimes_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checks', function (Blueprint $table) {
            $table->dropIndex('idx_checks_emp_id');
            $table->dropIndex('idx_checks_attendance_time');
            $table->dropIndex('idx_checks_leave_time');
            $table->dropIndex('idx_checks_emp_attendance');
            
            if (Schema::hasColumn('checks', 'schedule_id')) {
                $table->dropIndex('idx_checks_schedule_id');
            }
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_employees_name');
            
            if (Schema::hasColumn('employees', 'email')) {
                $table->dropIndex('idx_employees_email');
            }
        });

        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropIndex('idx_attendances_emp_id');
                $table->dropIndex('idx_attendances_time');
                $table->dropIndex('idx_attendances_date');
            });
        }

        if (Schema::hasTable('latetimes')) {
            Schema::table('latetimes', function (Blueprint $table) {
                $table->dropIndex('idx_latetimes_emp_id');
                $table->dropIndex('idx_latetimes_date');
            });
        }

        if (Schema::hasTable('overtimes')) {
            Schema::table('overtimes', function (Blueprint $table) {
                $table->dropIndex('idx_overtimes_emp_id');
                $table->dropIndex('idx_overtimes_date');
            });
        }
    }
}
