<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
  use RefreshDatabase;

  protected $admin;
  protected $user;
  protected $attendance;

  protected function setUp(): void
  {
    parent::setUp();

    $this->admin = Admin::factory()->create();
    $this->user = User::factory()->create();

    $this->attendance = Attendance::factory()->create([
      'user_id' => $this->user->id,
      'work_date' => Carbon::today()->toDateString(),
      'clock_in' => '09:00:00',
      'clock_out' => '18:00:00',
    ]);
  }

  /**
   * 出勤 > 退勤 の場合にエラーになること
   */
  public function test_clock_in_is_later_than_clock_out_should_fail()
  {
    $response = $this->actingAs($this->admin, 'admin')->put(
      route('admin.attendances.update', $this->attendance->id),
      [
        'user_id' => $this->user->id,
        'work_date' => $this->attendance->work_date,
        'clock_in' => '19:00',
        'clock_out' => '09:00',
        'breaks' => [
          ['start' => '12:00', 'end' => '13:00']
        ],
        'note' => 'test'
      ]
    );

    $response->assertSessionHasErrors(['clock_in']);
    $this->assertStringContainsString(
      '出勤時間もしくは退勤時間が不適切な値です',
      session('errors')->first('clock_in')
    );
  }

  /**
   * 休憩開始が退勤より後の場合にエラーになること
   */
  public function test_break_start_is_later_than_clock_out_should_fail()
  {
    $response = $this->actingAs($this->admin, 'admin')->put(
      route('admin.attendances.update', $this->attendance->id),
      [
        'user_id' => $this->user->id,
        'work_date' => $this->attendance->work_date,
        'clock_in' => '09:00',
        'clock_out' => '18:00',
        'breaks' => [
          ['start' => '19:00', 'end' => '19:30']
        ],
        'note' => 'test'
      ]
    );

    $response->assertSessionHasErrors(['break_start1']);
    $this->assertStringContainsString(
      '休憩時間が不適切な値です',
      session('errors')->first('break_start1')
    );
  }

  /**
   * 休憩終了が退勤より後の場合にエラーになること
   */
  public function test_break_end_is_later_than_clock_out_should_fail()
  {
    $response = $this->actingAs($this->admin, 'admin')->put(
      route('admin.attendances.update', $this->attendance->id),
      [
        'user_id' => $this->user->id,
        'work_date' => $this->attendance->work_date,
        'clock_in' => '09:00',
        'clock_out' => '18:00',
        'breaks' => [
          ['start' => '12:00', 'end' => '19:00']
        ],
        'note' => 'test'
      ]
    );

    $response->assertSessionHasErrors(['break_end1']);
    $this->assertStringContainsString(
      '休憩時間もしくは退勤時間が不適切な値です',
      session('errors')->first('break_end1')
    );
  }

  /**
   * 備考未入力でエラーになること
   */
  public function test_note_is_required_should_fail()
  {
    $response = $this->actingAs($this->admin, 'admin')->put(
      route('admin.attendances.update', $this->attendance->id),
      [
        'user_id' => $this->user->id,
        'work_date' => $this->attendance->work_date,
        'clock_in' => '09:00',
        'clock_out' => '18:00',
        'breaks' => [
          ['start' => '12:00', 'end' => '13:00']
        ],
        'note' => '' // 未入力
      ]
    );

    $response->assertSessionHasErrors(['note']);
    $this->assertStringContainsString(
      '備考を記入してください',
      session('errors')->first('note')
    );
  }

  /**
   * 正常データで更新成功すること
   */
  public function test_valid_data_should_pass_validation()
  {
    $response = $this->actingAs($this->admin, 'admin')->put(
      route('admin.attendances.update', $this->attendance->id),
      [
        'user_id' => $this->user->id,
        'work_date' => $this->attendance->work_date,
        'clock_in' => '09:00',
        'clock_out' => '18:00',
        'breaks' => [
          ['start' => '12:00', 'end' => '13:00']
        ],
        'note' => '修正テスト'
      ]
    );

    $response->assertSessionHasNoErrors();
    $response->assertStatus(303); // リダイレクト(303 See Other)
  }
}
