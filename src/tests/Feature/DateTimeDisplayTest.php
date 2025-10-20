<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DateTimeDisplayTest extends TestCase
{
  use RefreshDatabase;

  /**
   * 現在の日時が画面上に正しく表示されているか確認する
   */
  public function test_current_datetime_is_displayed_correctly()
  {
    // ユーザーを作成し、ログイン状態にする
    $user = User::factory()->create();
    $this->actingAs($user);

    // 現在の日付（例: 2025年10月8日）
    $now = Carbon::now()->format('Y年n月j日');

    // 勤怠打刻ページを取得
    $response = $this->get('/attendance');

    // ステータスコード確認
    $response->assertStatus(200);

    // 現在の日付が含まれていることを確認
    $response->assertSee($now);
  }
}
