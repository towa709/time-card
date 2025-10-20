<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\WorkBreak;
use Illuminate\Support\Carbon;

class WorkBreakFactory extends Factory
{
  protected $model = WorkBreak::class;

  public function definition(): array
  {
    $start = Carbon::now()->subHours(2);
    $end   = $start->copy()->addMinutes(60);

    return [
      'attendance_id' => 1,
      'break_start'   => $start,
      'break_end'     => $end,
    ];
  }
}
