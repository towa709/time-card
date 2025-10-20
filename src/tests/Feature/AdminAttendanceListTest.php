<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
  use RefreshDatabase;

  /**
   * ① 管理者が勤怠一覧を開いた際、現在日付が表示される
   */
  public function test_it_shows_current_date_by_default()
  {
    $admin = Admin::factory()->create();
    $this->actingAs($admin, 'admin');

    $today = Carbon::today();
    // ✅ デフォルトは date パラメータなし
    $response = $this->get('/admin/attendances/list');

    $response->assertStatus(200)
      ->assertSee($today->format('Y年n月j日'));
  }

  /**
   * ② 前日ボタン押下時、前日の勤怠情報が表示される
   */
  public function test_it_shows_previous_day_attendance()
  {
    $admin = Admin::factory()->create();
    $this->actingAs($admin, 'admin');

    $yesterday = Carbon::yesterday();
    // ✅ クエリパラメータで指定
    $response = $this->get('/admin/attendances/list?date=' . $yesterday->toDateString());

    $response->assertStatus(200)
      ->assertSee($yesterday->format('Y年n月j日'));
  }

  /**
   * ③ 翌日ボタン押下時、翌日の勤怠情報が表示される
   */
  public function test_it_shows_next_day_attendance()
  {
    $admin = Admin::factory()->create();
    $this->actingAs($admin, 'admin');

    $tomorrow = Carbon::tomorrow();
    // ✅ クエリパラメータで指定
    $response = $this->get('/admin/attendances/list?date=' . $tomorrow->toDateString());

    $response->assertStatus(200)
      ->assertSee($tomorrow->format('Y年n月j日'));
  }

  /**
   * ④ 勤怠一覧に全ユーザーの勤怠情報が表示される
   */
  public function test_all_users_attendance_are_loaded()
  {
    $admin = Admin::factory()->create();
    $this->actingAs($admin, 'admin');

    $users = User::factory()->count(3)->create();
    foreach ($users as $user) {
      Attendance::factory()->create([
        'user_id' => $user->id,
        'work_date' => Carbon::today()->toDateString(),
        'clock_in' => Carbon::today()->setTime(9, 0),
        'clock_out' => Carbon::today()->setTime(18, 0),
      ]);
    }

    $response = $this->get('/admin/attendances/list');
    $response->assertStatus(200);

    foreach ($users as $user) {
      $response->assertSee($user->name);
    }
  }
}
