<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_OFF     = 0;
    const STATUS_WORKING = 1;
    const STATUS_BREAK   = 2;
    const STATUS_DONE    = 3;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakModel::class, 'attendance_id');
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }
}
