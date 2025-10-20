<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class LeaveWorkTest extends TestCase
{
  use RefreshDatabase;

  /** 
   * 退勤ボタンが正しく機能する 
   */
  public function test_user_can_end_work()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    // 出勤済みデータを用意
    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => Carbon::today()->toDateString(),
      'clock_in' => Carbon::now()->subHours(8), // 出勤8時間前
      'clock_out' => null,
      'break_time' => 60,
    ]);

    // 退勤ボタン押下
    $response = $this->post(route('attendance.end'));

    $response->assertRedirect(route('attendance.create'));

    // DB反映確認
    $attendance->refresh();
    $this->assertNotNull($attendance->clock_out, '退勤時刻が保存されていません');
    $this->assertTrue($attendance->total_work_time >= 0, '合計勤務時間が不正です');
  }

  /**
   * 勤怠一覧に退勤時刻が表示される
   */
  public function test_attendance_list_displays_clock_out_time()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $today = Carbon::today();
    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => $today,
      'clock_in' => $today->copy()->setTime(9, 0),
      'clock_out' => $today->copy()->setTime(18, 0),
      'break_time' => 60,
    ]);

    $response = $this->get(route('attendance.index', ['month' => $today->format('Y-m')]));
    $response->assertStatus(200);

    $html = $response->getContent();

    // HTMLに「18:00」などが含まれているか確認
    $this->assertTrue(
      str_contains($html, '18:00') || str_contains($html, '17:59'),
      '退勤時刻が勤怠一覧に正しく表示されていません。'
    );
  }
}
