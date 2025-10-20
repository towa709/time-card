<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class AttendanceFactory extends Factory
{
  protected $model = Attendance::class;

  public function definition(): array
  {
    return [
      'user_id' => 1,
      'work_date' => Carbon::today(),
      'clock_in' => null,
      'clock_out' => null,
      'break_time' => 0,
      'total_work_time' => 0,
      'note' => null,
    ];
  }
}
