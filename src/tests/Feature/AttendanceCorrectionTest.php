<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceCorrectionTest extends TestCase
{
  use RefreshDatabase;

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
