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

  public function test_it_shows_current_date_by_default()
  {
    $admin = Admin::factory()->create();
    $this->actingAs($admin, 'admin');

    $today = Carbon::today();
    $response = $this->get('/admin/attendances/list');

    $response->assertStatus(200)
      ->assertSee($today->format('Y年n月j日'));
  }

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

  public function test_it_shows_next_day_attendance()
  {
    $admin = Admin::factory()->create();
    $this->actingAs($admin, 'admin');

    $tomorrow = Carbon::tomorrow();
    $response = $this->get('/admin/attendances/list?date=' . $tomorrow->toDateString());

    $response->assertStatus(200)
      ->assertSee($tomorrow->format('Y年n月j日'));
  }

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
