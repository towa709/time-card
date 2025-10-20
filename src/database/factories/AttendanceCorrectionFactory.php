<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrection;
use App\Models\Attendance;
use App\Models\User;

class AttendanceCorrectionFactory extends Factory
{
  protected $model = AttendanceCorrection::class;

  public function definition(): array
  {
    $user = User::factory()->create();
    $attendance = Attendance::factory()->create(['user_id' => $user->id]);

    return [
      'attendance_id'        => $attendance->id,
      'user_id'              => $user->id,
      'requested_clock_in'   => '09:00:00',
      'requested_clock_out'  => '18:00:00',
      'requested_break_time' => 60,
      'requested_total_time' => 480,
      'note'                 => '修正申請テスト',
      'status'               => 'pending',
    ];
  }

  public function configure()
  {
    return $this->afterCreating(function (AttendanceCorrection $correction) {
      \App\Models\AttendanceCorrectionBreak::factory()->create([
        'attendance_correction_id' => $correction->id,
        'break_start' => now()->setTime(12, 0),
        'break_end'   => now()->setTime(13, 0),
      ]);
    });
  }
}
