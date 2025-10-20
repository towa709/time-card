<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class AttendanceCorrectionTest extends TestCase
{
  use RefreshDatabase;

  /** ① 出勤時間が退勤時間より後の場合、エラーが出る */
  public function test_error_when_clock_in_is_after_clock_out()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $attendance = Attendance::factory()->create(['user_id' => $user->id]);

    $response = $this->from(route('attendance.detail', $attendance->id))
      ->put(route('attendance.update', $attendance->id), [
        'clock_in' => '18:00',
        'clock_out' => '09:00',
        'note' => 'テスト備考',
        'breaks' => [['start' => '12:00', 'end' => '13:00']],
      ]);

    $response->assertSessionHasErrors(['clock_out']);
  }

  /** ② 休憩開始時間が退勤時間より後の場合、エラーが出る */
  public function test_error_when_break_start_is_after_clock_out()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $attendance = Attendance::factory()->create(['user_id' => $user->id]);

    $response = $this->from(route('attendance.detail', $attendance->id))
      ->put(route('attendance.update', $attendance->id), [
        'clock_in' => '09:00',
        'clock_out' => '18:00',
        'breaks' => [['start' => '19:00', 'end' => '19:30']],
        'note' => 'テスト備考',
      ]);

    $response->assertSessionHasErrors(['break_start1']);
  }

  /** ③ 休憩終了時間が退勤時間より後の場合、エラーが出る */
  public function test_error_when_break_end_is_after_clock_out()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $attendance = Attendance::factory()->create(['user_id' => $user->id]);

    $response = $this->from(route('attendance.detail', $attendance->id))
      ->put(route('attendance.update', $attendance->id), [
        'clock_in' => '09:00',
        'clock_out' => '18:00',
        'breaks' => [['start' => '17:00', 'end' => '19:00']],
        'note' => 'テスト備考',
      ]);

    $response->assertSessionHasErrors(['break_end1']);
  }

  /** ④ 備考が未入力の場合、エラーが出る */
  public function test_error_when_note_is_empty()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $attendance = Attendance::factory()->create(['user_id' => $user->id]);

    $response = $this->from(route('attendance.detail', $attendance->id))
      ->put(route('attendance.update', $attendance->id), [
        'clock_in' => '09:00',
        'clock_out' => '18:00',
        'note' => '',
        'breaks' => [['start' => '12:00', 'end' => '13:00']],
      ]);

    $response->assertSessionHasErrors(['note']);
  }

  //** ⑤ 修正申請が保存される */
  public function test_correction_is_saved_successfully()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $attendance = Attendance::factory()->create(['user_id' => $user->id]);

    $response = $this->put("/attendance/detail/{$attendance->id}", [
      'clock_in' => '09:00',
      'clock_out' => '18:00',
      'breaks' => [['start' => '12:00', 'end' => '13:00']],
      'note' => '勤務時間修正申請',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('attendance_corrections', [
      'attendance_id' => $attendance->id,
      'user_id' => $user->id,
      'note' => '勤務時間修正申請',
    ]);
  }
}
