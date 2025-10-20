<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin; // 管理者モデルを使用

class AdminLoginTest extends TestCase
{
  use RefreshDatabase;

  /**
   * メールアドレスが未入力の場合、バリデーションエラーになる
   */
  public function test_admin_email_is_required()
  {
    // 管理者ユーザー作成
    Admin::factory()->create([
      'email' => 'admin@example.com',
      'password' => bcrypt('admin123'),
    ]);

    // メールアドレス未入力でログイン
    $response = $this->post('/admin/login', [
      'email' => '',
      'password' => 'admin123',
    ]);

    // エラーメッセージを確認
    $response->assertSessionHasErrors(['email']);
  }

  /**
   * パスワードが未入力の場合、バリデーションエラーになる
   */
  public function test_admin_password_is_required()
  {
    Admin::factory()->create([
      'email' => 'admin@example.com',
      'password' => bcrypt('admin123'),
    ]);

    $response = $this->post('/admin/login', [
      'email' => 'admin@example.com',
      'password' => '',
    ]);

    $response->assertSessionHasErrors(['password']);
  }

  /**
   * 登録内容と一致しない場合、バリデーションエラーになる
   */
  public function test_admin_login_fails_with_incorrect_credentials()
  {
    // 正しい管理者を登録
    Admin::factory()->create([
      'email' => 'admin@example.com',
      'password' => bcrypt('admin123'),
    ]);

    // 間違ったパスワードでログイン
    $response = $this->post('/admin/login', [
      'email' => 'admin@example.com',
      'password' => 'wrongpassword',
    ]);

    // Fortifyまたは独自認証のエラーメッセージを検証
    $response->assertSessionHasErrors([
      'email' => 'ログイン情報が登録されていません',
    ]);
  }
}
