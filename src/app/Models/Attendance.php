<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'work_date',
    'clock_in',
    'clock_out',
    'break_time',
    'total_work_time',
    'note',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function corrections()
  {
    return $this->hasMany(AttendanceCorrection::class);
  }

  public function breaks()
  {
    return $this->hasMany(WorkBreak::class, 'attendance_id');
  }
}
