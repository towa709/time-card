<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
  use HasFactory;

  protected $fillable = [
    'attendance_id',
    'user_id',
    'requested_clock_in',
    'requested_clock_out',
    'note',
    'status',
    'approved_by',
  ];

  public function attendance()
  {
    return $this->belongsTo(Attendance::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function breaks()
  {
    return $this->hasMany(AttendanceCorrectionBreak::class, 'attendance_correction_id');
  }
}
