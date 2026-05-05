<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Check extends Model
{
    protected $table = 'checks';

    protected $fillable = ['emp_id', 'attendance_time', 'leave_time', 'schedule_id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}