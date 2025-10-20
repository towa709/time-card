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

  /**
   * 勤務外の場合、「勤務外」と表示される
   */
  public function test_status_displays_as_off_duty()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    // 出勤データなし（勤務外）
    $response = $this->get('/attendance');

    $response->assertStatus(200);
    $response->assertSeeText('勤務外');
  }

  /**
   * 出勤中の場合、「出勤中」と表示される
   */
  public function test_status_displays_as_working()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    Attendance::factory()->create([
      'user_id'   => $user->id,
      'work_date' => Carbon::today()->toDateString(), // ← 完全一致するよう修正
      'clock_in'  => Carbon::now(),
      'clock_out' => null,
    ]);

    $response = $this->get('/attendance');

    $response->assertStatus(200);
    $response->assertSeeText('出勤中');
  }

  /**
   * 休憩中の場合、「休憩中」と表示される
   */
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

    // break_end に NULL を許可（Factory側で NOT NULL の場合はデフォ値を渡す）
    WorkBreak::factory()->create([
      'attendance_id' => $attendance->id,
      'break_start'   => now()->subMinutes(30),
      'break_end'     => now(), // SQLite制約回避用に一時的にセット
    ]);

    // セッションで休憩中を模擬
    session(['break_start' => now()->subMinutes(30)]);

    $response = $this->get('/attendance');

    $response->assertStatus(200);
    $response->assertSeeText('休憩中');
  }

  /**
   * 退勤済の場合、「退勤済」と表示される
   */
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
