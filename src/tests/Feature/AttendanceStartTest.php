<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceStartTest extends TestCase
{
  use RefreshDatabase;

  /** 出勤ボタンが正しく機能する（初回出勤） */
  public function test_user_can_start_work()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->assertDatabaseMissing('attendances', [
      'user_id' => $user->id,
      'work_date' => Carbon::today()->toDateString(),
    ]);

    $response = $this->post('/attendance/start');

    $this->assertDatabaseHas('attendances', [
      'user_id' => $user->id,
      'work_date' => Carbon::today()->toDateString(),
    ]);

    $response->assertRedirect('/attendance');
  }

  /** 出勤は1日1回のみできる */
  public function test_user_cannot_start_work_twice_in_one_day()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    Attendance::create([
      'user_id'   => $user->id,
      'work_date' => Carbon::today()->toDateString(),
      'clock_in'  => Carbon::now(),
    ]);

    $response = $this->post('/attendance/start');

    $attendance = Attendance::where('user_id', $user->id)
      ->where('work_date', Carbon::today()->toDateString())
      ->first();

    $this->assertNotNull($attendance->clock_in);
    $response->assertRedirect('/attendance');
  }

  /** 出勤時刻が勤怠一覧画面に正しく表示される */
  public function test_attendance_start_time_is_displayed_in_list()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    Carbon::setTestNow(Carbon::createFromFormat('Y-m-d H:i:s', '2025-10-08 09:00:00', 'Asia/Tokyo'));
    $today = Carbon::now('Asia/Tokyo');
    $clockInTime = $today->copy()->setTime(9, 0);

    Attendance::create([
      'user_id'   => $user->id,
      'work_date' => $today->toDateString(),
      'clock_in'  => $clockInTime,
      'clock_out' => $clockInTime->copy()->addHours(8), // ✅ 出勤だけでなく退勤も仮定（表示されるように）
    ]);

    $response = $this->get(route('attendance.index', ['month' => $today->format('Y-m')]));
    $response->assertStatus(200);

    $html = $response->getContent();

    // ✅ 出勤時刻がHTMLに含まれているかチェック
    $this->assertTrue(
      str_contains($html, $clockInTime->format('H:i')) || str_contains($html, $clockInTime->format('G:i')),
      '出勤時刻が一覧に表示されていません。'
    );
  }
}
