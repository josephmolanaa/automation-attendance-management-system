<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidayOverride extends Model
{
    protected $table    = 'holiday_overrides';
    protected $fillable = ['date', 'original_type', 'override_type', 'schedule_id', 'note'];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}