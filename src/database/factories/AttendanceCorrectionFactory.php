<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrection;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class AttendanceCorrectionFactory extends Factory
{
  protected $model = AttendanceCorrection::class;

  public function definition(): array
  {
    $user = User::factory()->create();
    $today = Carbon::today();

    $attendance = Attendance::create([
      'user_id' => $user->id,
      'work_date' => $today->format('Y-m-d'),
      'clock_in' => $today->copy()->setTime(9, 0, 0)->format('Y-m-d H:i:s'),
      'clock_out' => $today->copy()->setTime(18, 0, 0)->format('Y-m-d H:i:s'),
      'break_time' => 0,
      'total_work_time' => 0,
      'note' => '修正元勤怠',
    ]);

    return [
      'attendance_id'        => $attendance->id,
      'user_id'              => $user->id,
      'requested_clock_in'   => $today->copy()->setTime(9, 0, 0)->format('Y-m-d H:i:s'),
      'requested_clock_out'  => $today->copy()->setTime(18, 0, 0)->format('Y-m-d H:i:s'),
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
        'break_start' => now()->setTime(12, 0)->format('Y-m-d H:i:s'),
        'break_end'   => now()->setTime(13, 0)->format('Y-m-d H:i:s'),
      ]);
    });
  }
}
