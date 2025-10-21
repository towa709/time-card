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
    $today = Carbon::today();

    return [
      'user_id' => 1,
      'work_date' => $today->format('Y-m-d'),
      'clock_in' => $today->copy()->setTime(9, 0, 0)->format('Y-m-d H:i:s'),
      'clock_out' => $today->copy()->setTime(18, 0, 0)->format('Y-m-d H:i:s'),
      'break_time' => 0,
      'total_work_time' => 0,
      'note' => 'テストデータ',
    ];
  }
}
