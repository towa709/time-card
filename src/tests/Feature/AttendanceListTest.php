<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceListTest extends TestCase
{
  use RefreshDatabase;

  /**
   * 自分の勤怠情報のみが一覧に表示される
   */
  public function test_user_can_only_see_own_attendance_records()
  {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $this->actingAs($user);

    Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => Carbon::today()->subDay(),
      'clock_in' => Carbon::today()->setTime(9, 0),
      'clock_out' => Carbon::today()->setTime(18, 0),
    ]);

    Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => Carbon::today()->subDay(),
      'clock_in' => Carbon::today()->subDay()->setTime(9, 0),
      'clock_out' => Carbon::today()->subDay()->setTime(18, 0),
    ]);

    $response = $this->get(route('attendance.index', ['month' => Carbon::today()->format('Y-m')]));
    $response->assertStatus(200);
    $html = $response->getContent();

    $this->assertStringContainsString('09:00', $html, '自分の勤怠が表示されていません。');
    $this->assertStringNotContainsString('10:00', $html, '他人の勤怠情報が表示されています。');
  }

  /**
   * 勤怠一覧画面で現在月が表示される
   */
  public function test_current_month_is_displayed_on_attendance_list()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('attendance.index', ['month' => Carbon::now()->format('Y-m')]));
    $response->assertStatus(200);
    $response->assertSee(Carbon::now()->format('Y/m'), false);
  }

  /**
   * 「前月」ボタンを押すと前月の情報が表示される
   */
  public function test_previous_month_button_displays_previous_month()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $prevMonth = Carbon::now()->subMonth();
    $response = $this->get(route('attendance.index', ['month' => $prevMonth->format('Y-m')]));
    $response->assertStatus(200);
    $response->assertSee($prevMonth->format('Y/m'), false);
  }

  /**
   * 「翌月」ボタンを押すと翌月の情報が表示される
   */
  public function test_next_month_button_displays_next_month()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $nextMonth = Carbon::now()->addMonth();
    $response = $this->get(route('attendance.index', ['month' => $nextMonth->format('Y-m')]));
    $response->assertStatus(200);
    $response->assertSee($nextMonth->format('Y/m'), false);
  }

  /**
   * 「詳細」ボタンを押すと該当日の勤怠詳細ページへ遷移する
   */
  public function test_detail_button_redirects_to_attendance_detail()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => Carbon::today()->subDay(),
      'clock_in' => Carbon::today()->subDay()->setTime(9, 0),
      'clock_out' => Carbon::today()->subDay()->setTime(18, 0),
    ]);

    $response = $this->get(route('attendance.index', ['month' => Carbon::today()->format('Y-m')]));
    $response->assertStatus(200);

    // 詳細リンクが正しく出力されているかチェック
    $response->assertSee(route('attendance.detail', $attendance->id), false);
  }
}
