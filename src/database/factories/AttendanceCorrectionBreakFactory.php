<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrectionBreak;
use Illuminate\Support\Carbon;

class AttendanceCorrectionBreakFactory extends Factory
{
  protected $model = AttendanceCorrectionBreak::class;

  public function definition(): array
  {
    $start = Carbon::now()->setTime(12, 0, 0);
    $end   = $start->copy()->addHour();

    return [
      'attendance_correction_id' => 1,
      'break_start' => $start->format('Y-m-d H:i:s'),
      'break_end'   => $end->format('Y-m-d H:i:s'),
    ];
  }
}
