<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\WorkBreak;

class WorkBreakFactory extends Factory
{
  protected $model = WorkBreak::class;

  public function definition(): array
  {
    return [
      'attendance_id' => 1,
      'break_start'   => '12:00:00',
      'break_end'     => '13:00:00',
    ];
  }
}
