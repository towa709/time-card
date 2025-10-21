<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceCorrectionBreak;
use Carbon\Carbon;

class AdminAttendanceCorrectionTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function it_shows_all_pending_corrections()
  {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();
    AttendanceCorrection::factory()->count(2)->create([
      'status' => 'pending',
      'user_id' => $user->id,
    ]);
    AttendanceCorrection::factory()->create(['status' => 'approved', 'user_id' => $user->id]);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.corrections.index', ['status' => 'pending']));

    $response->assertStatus(200);
    $response->assertSee('承認待ち');
    $response->assertDontSee('承認済み');
  }

  /** @test */
  public function it_shows_all_approved_corrections()
  {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();
    AttendanceCorrection::factory()->count(2)->create([
      'status' => 'approved',
      'user_id' => $user->id,
    ]);
    AttendanceCorrection::factory()->create(['status' => 'pending', 'user_id' => $user->id]);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.corrections.index', ['status' => 'approved']));

    $response->assertStatus(200);
    $response->assertSee('承認済み');
    $response->assertDontSee('承認待ち');
  }

  /** @test */
  public function it_displays_correction_detail_correctly()
  {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();

    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'work_date' => Carbon::today(),
    ]);

    $correction = AttendanceCorrection::factory()->create([
      'attendance_id' => $attendance->id,
      'requested_clock_in' => now()->setTime(9, 0, 0)->format('Y-m-d H:i:s'),
      'requested_clock_out' => now()->setTime(18, 0, 0)->format('Y-m-d H:i:s'),
      'note' => 'テスト修正申請',
      'status' => 'pending',
    ]);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.corrections.show', $correction->id));

    $response->assertStatus(200);
    $response->assertSee('09:00');
    $response->assertSee('18:00');
    $response->assertSee('テスト修正申請');
  }

  /** @test */
  public function it_approves_correction_and_updates_attendance()
  {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();

    $attendance = Attendance::factory()->create([
      'user_id' => $user->id,
      'clock_in'  => now()->setTime(8, 0, 0)->format('Y-m-d H:i:s'),
      'clock_out' => now()->setTime(17, 0, 0)->format('Y-m-d H:i:s'),
      'work_date' => Carbon::today()->format('Y-m-d'),
    ]);

    $correction = AttendanceCorrection::factory()->create([
      'attendance_id' => $attendance->id,
      'requested_clock_in' => now()->setTime(9, 0, 0)->format('Y-m-d H:i:s'),
      'requested_clock_out' => now()->setTime(18, 0, 0)->format('Y-m-d H:i:s'),
      'status' => 'pending',
    ]);

    AttendanceCorrectionBreak::factory()->create([
      'attendance_correction_id' => $correction->id,
      'break_start' => now()->setTime(12, 0, 0)->format('Y-m-d H:i:s'),
      'break_end'   => now()->setTime(13, 0, 0)->format('Y-m-d H:i:s'),
    ]);

    AttendanceCorrectionBreak::factory()->create([
      'attendance_correction_id' => $correction->id,
      'break_start' => now()->setTime(15, 0, 0)->format('Y-m-d H:i:s'),
      'break_end'   => now()->setTime(16, 0, 0)->format('Y-m-d H:i:s'),
    ]);

    $response = $this->actingAs($admin, 'admin')->post(
      route('admin.corrections.approve', $correction->id)
    );

    $response->assertStatus(200);
    $correction->refresh();
    $attendance->refresh();

    $this->assertEquals('approved', $correction->status);
    $this->assertEquals('09:00:00', Carbon::parse($attendance->clock_in)->format('H:i:s'));
    $this->assertEquals('18:00:00', Carbon::parse($attendance->clock_out)->format('H:i:s'));
    $this->assertEquals(180, $attendance->break_time);
    $this->assertEquals(360, $attendance->total_work_time);
  }
}
