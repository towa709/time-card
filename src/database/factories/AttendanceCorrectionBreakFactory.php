<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrectionBreak;

class AttendanceCorrectionBreakFactory extends Factory
{
  protected $model = AttendanceCorrectionBreak::class;

  public function definition(): array
  {
    return [
      'attendance_correction_id' => 1,
      'break_start' => now()->setTime(12, 0),
      'break_end'   => now()->setTime(13, 0),
    ];
  }
}
