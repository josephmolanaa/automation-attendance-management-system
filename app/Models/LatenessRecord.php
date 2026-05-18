<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LatenessRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'check_id',
        'schedule_id',
        'date',
        'scheduled_in',
        'actual_scan_in',
        'status',
        'late_seconds',
        'late_minutes',
        'late_duration',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'scheduled_in' => 'datetime',
        'actual_scan_in' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function check()
    {
        return $this->belongsTo(Check::class, 'check_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'terlambat');
    }
}
