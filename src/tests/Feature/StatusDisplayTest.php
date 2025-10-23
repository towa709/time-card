<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class StatusDisplayTest extends TestCase
{
  use RefreshDatabase;

  public function test_status_displays_as_off_duty()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/attendance');

    $response->assertStatus(200);
    $response->assertSeeText('勤務外');
  }

  public function test_status_displays_as_working()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    Attendance::factory()->create([
      'user_id'   => $user->id,
      'work_date' => Carbon::today()->toDateString(),
      'clock_in'  => Carbon::now(),
      'clock_out' => null,
    ]);

    $response = $this->get('/attendance');

    $response->assertStatus(200);
    $response->assertSeeText('出勤中');
  }

  public function test_status_displays_as_on_break()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
      'user_id'   => $user->id,
      'work_date' => Carbon::today()->toDateString(),
      'clock_in'  => now()->subHours(2),
      'clock_out' => null,
    ]);

    WorkBreak::factory()->create([
      'attendance_id' => $attendance->id,
      'break_start'   => now()->subMinutes(30),
      'break_end'     => now(),
    ]);

    session(['break_start' => now()->subMinutes(30)]);

    $response = $this->get('/attendance');

    $response->assertStatus(200);
    $response->assertSeeText('休憩中');
  }

  public function test_status_displays_as_finished()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    Attendance::factory()->create([
      'user_id'   => $user->id,
      'work_date' => Carbon::today()->toDateString(),
      'clock_in'  => Carbon::now()->subHours(8),
      'clock_out' => Carbon::now()->subHours(1),
    ]);

    $response = $this->get('/attendance');

    $response->assertStatus(200);
    $response->assertSeeText('退勤済');
  }
}
