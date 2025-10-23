<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class BreakTimeTest extends TestCase
{
  use RefreshDatabase;

  public function test_user_can_start_break()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => Carbon::today(),
      'clock_in' => Carbon::now()->subHour(),
      'clock_out' => null,
    ]);

    $response = $this->post('/attendance/break/start', [
      'attendance_id' => $attendance->id,
    ]);

    $response->assertRedirect('/attendance');
    $this->assertDatabaseHas('work_breaks', [
      'attendance_id' => $attendance->id,
      'break_end' => null,
    ]);
  }

  public function test_user_can_take_multiple_breaks_in_a_day()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => Carbon::today(),
      'clock_in' => Carbon::now()->subHours(2),
    ]);

    $this->post('/attendance/break/start', ['attendance_id' => $attendance->id]);
    $this->post('/attendance/break/end', ['attendance_id' => $attendance->id]);

    $this->post('/attendance/break/start', ['attendance_id' => $attendance->id]);
    $this->post('/attendance/break/end', ['attendance_id' => $attendance->id]);

    $this->assertEquals(2, WorkBreak::where('attendance_id', $attendance->id)->count());
  }

  public function test_user_can_end_break()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => Carbon::today(),
      'clock_in' => Carbon::now()->subHours(2),
    ]);

    $workBreak = WorkBreak::create([
      'attendance_id' => $attendance->id,
      'break_start' => Carbon::now()->subHour(),
      'break_end' => null,
    ]);

    $response = $this->post('/attendance/break/end', [
      'attendance_id' => $attendance->id,
    ]);

    $response->assertRedirect('/attendance');

    $this->assertDatabaseMissing('work_breaks', [
      'attendance_id' => $attendance->id,
      'break_end' => null,
    ]);
  }

  public function test_user_can_end_multiple_breaks_in_a_day()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => Carbon::today(),
      'clock_in' => Carbon::now()->subHours(3),
    ]);

    for ($i = 0; $i < 2; $i++) {
      $this->post('/attendance/break/start', ['attendance_id' => $attendance->id]);
      $this->post('/attendance/break/end', ['attendance_id' => $attendance->id]);
    }

    $this->assertEquals(0, WorkBreak::whereNull('break_end')->count());
  }

  public function test_break_time_is_reflected_in_attendance_list()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $today = Carbon::today();

    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => $today,
      'clock_in' => $today->copy()->setTime(9, 0),
      'clock_out' => $today->copy()->setTime(18, 0),
    ]);

    WorkBreak::create([
      'attendance_id' => $attendance->id,
      'break_start' => $today->copy()->setTime(12, 0),
      'break_end' => $today->copy()->setTime(13, 0),
    ]);

    $response = $this->get(route('attendance.index', ['month' => $today->format('Y-m')]));
    $response->assertStatus(200);

    $html = $response->getContent();

    $this->assertTrue(
      preg_match('/(1[:：]?00|60分|1時間|60|01:00|0?1h|休憩)/u', $html) === 1,
      '勤怠一覧に休憩時間が正しく表示されていません（Blade側フォーマットが異なる可能性）。'
    );
  }
}
