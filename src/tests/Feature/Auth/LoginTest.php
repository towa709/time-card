<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
  use RefreshDatabase;

  /**
   * メールアドレスが未入力の場合、バリデーションエラーになる
   */
  public function test_email_is_required()
  {
    // 事前にユーザーを作成
    User::factory()->create([
      'email' => 'test@example.com',
      'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
      'email' => '',
      'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['email']);
  }

  /**
   * パスワードが未入力の場合、バリデーションエラーになる
   */
  public function test_password_is_required()
  {
    User::factory()->create([
      'email' => 'test@example.com',
      'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
      'email' => 'test@example.com',
      'password' => '',
    ]);

    $response->assertSessionHasErrors(['password']);
  }

  /**
   * 登録内容と一致しない場合、バリデーションエラーになる
   */
  public function test_login_fails_with_incorrect_credentials()
  {
    // 正しいユーザーを作成
    User::factory()->create([
      'email' => 'test@example.com',
      'password' => bcrypt('password123'),
    ]);

    // 間違ったパスワードでログインを試みる
    $response = $this->post('/login', [
      'email' => 'test@example.com',
      'password' => 'wrongpassword',
    ]);

    // Fortifyのカスタムメッセージ（または一般的な認証エラー）を検証
    $response->assertSessionHasErrors([
      'email' => 'ログイン情報が登録されていません',
    ]);
  }
}
