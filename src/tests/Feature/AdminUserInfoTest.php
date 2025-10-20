<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminUserInfoTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function admin_can_view_all_users_with_name_and_email()
  {
    $admin = Admin::factory()->create();
    $users = User::factory()->count(3)->create();

    $response = $this->actingAs($admin, 'admin')->get(route('admin.users.index'));

    $response->assertStatus(200);
    foreach ($users as $user) {
      $response->assertSee($user->name);
      $response->assertSee($user->email);
    }
  }

  /** @test */
  public function it_shows_user_attendance_correctly()
  {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();

    // ✅ テストが安定するように日付を固定
    $today = Carbon::create(2025, 10, 1, 0, 0, 0);

    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => $today,
      'clock_in' => $today->copy()->setTime(9, 0),
      'clock_out' => $today->copy()->setTime(18, 0),
    ]);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.attendances.user', $user->id));
    $response->assertStatus(200);
    $response->assertSee($user->name);
    $response->assertSee('09:00');
    $response->assertSee('18:00');
  }

  /** @test */
  public function previous_and_next_month_buttons_work()
  {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'admin')->get(route('admin.attendances.user', [
      'id' => $user->id,
      'month' => '2025-03',
    ]));

    $response->assertStatus(200);
    $response->assertSee('2025-02');
    $response->assertSee('2025-04');
  }

  /** @test */
  public function detail_button_navigates_to_attendance_detail_page()
  {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();
    $attendance = Attendance::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($admin, 'admin')
      ->get(route('admin.attendances.show', $attendance->id));

    $response->assertStatus(200);
    $response->assertSee($user->name);
  }
}
