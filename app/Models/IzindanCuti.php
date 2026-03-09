<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IzinDanCuti extends Model
{
    use HasFactory;

    protected $table = 'leaves';

    protected $fillable = [
        'emp_id',
        'leave_date',
        'reason',
        'note',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id');
    }
}