<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable implements MustVerifyEmail
{
  use HasFactory, Notifiable;

  public function sendEmailVerificationNotification()
  {
    static $alreadySent = false;

    if ($alreadySent) {
      Log::info('Duplicate verification mail skipped.');
      return;
    }

    $alreadySent = true;

    if ($this->hasVerifiedEmail()) {
      return;
    }

    parent::sendEmailVerificationNotification();
  }

  protected $fillable = [
    'name',
    'email',
    'password',
  ];

  public function attendances()
  {
    return $this->hasMany(Attendance::class);
  }

  public function attendanceCorrections()
  {
    return $this->hasMany(AttendanceCorrection::class);
  }
}
