<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceDetailTest extends TestCase
{
  use RefreshDatabase;

  /**
   * ② 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
   */
  public function test_attendance_detail_displays_logged_in_user_name()
  {
    $user = User::factory()->create(['name' => 'テストユーザー']);
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => Carbon::today(),
    ]);

    $response = $this->get(route('attendance.detail', $attendance->id));
    $response->assertStatus(200);
    $response->assertSee('テストユーザー', false);
  }

  /**
   * ③ 勤怠詳細画面の「日付」が選択した日付になっている
   */
  public function test_attendance_detail_displays_correct_date()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => Carbon::parse('2025-10-13'),
    ]);

    $response = $this->get(route('attendance.detail', $attendance->id));
    $response->assertStatus(200);
    $response->assertSee('2025年10月13日', false);
  }

  /**
   * ④ 「出勤・退勤」欄に正しい時間が表示されている
   */
  public function test_attendance_detail_displays_correct_clock_in_and_out()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => Carbon::today(),
      'clock_in' => Carbon::today()->setTime(9, 0),
      'clock_out' => Carbon::today()->setTime(18, 0),
    ]);

    $response = $this->get(route('attendance.detail', $attendance->id));
    $response->assertStatus(200);
    $response->assertSee('09:00', false);
    $response->assertSee('18:00', false);
  }

  /**
   * ⑤ 「休憩」欄に登録された休憩時間が表示されている
   */
  public function test_attendance_detail_displays_correct_break_time()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => Carbon::today(),
      'clock_in' => Carbon::today()->setTime(9, 0),
      'clock_out' => Carbon::today()->setTime(18, 0),
    ]);

    // 休憩打刻データ
    \App\Models\WorkBreak::create([
      'attendance_id' => $attendance->id,
      'break_start' => Carbon::today()->setTime(12, 0),
      'break_end' => Carbon::today()->setTime(13, 0),
    ]);

    $response = $this->get(route('attendance.detail', $attendance->id));
    $response->assertStatus(200);

    $html = $response->getContent();

    // ✅ 「12:00」および「13:00」がHTMLに含まれていればOK
    $this->assertTrue(
      str_contains($html, '12:00') && str_contains($html, '13:00'),
      '休憩時間（12:00〜13:00）が正しく表示されていません。'
    );
  }
}
