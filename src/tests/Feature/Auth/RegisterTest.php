<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
  use RefreshDatabase;

  /**
   * 名前が未入力の場合、バリデーションエラーになる
   */
  public function test_name_is_required()
  {
    // 送信データ
    $formData = [
      'name' => '',
      'email' => 'test@example.com',
      'password' => 'password123',
      'password_confirmation' => 'password123',
    ];

    // POSTリクエストを送信
    $response = $this->post('/register', $formData);

    // バリデーションエラー（name項目）を検証
    $response->assertSessionHasErrors(['name']);
  }

  /**
   * メールアドレスが未入力の場合、バリデーションエラーになる
   */
  public function test_email_is_required()
  {
    $formData = [
      'name' => 'とわ',
      'email' => '',
      'password' => 'password123',
      'password_confirmation' => 'password123',
    ];

    $response = $this->post('/register', $formData);

    $response->assertSessionHasErrors(['email']);
  }

  /**
   * パスワードが8文字未満の場合、バリデーションエラーになる
   */
  public function test_password_must_be_at_least_8_characters()
  {
    $formData = [
      'name' => 'とわ',
      'email' => 'test@example.com',
      'password' => 'short', // 5文字
      'password_confirmation' => 'short',
    ];

    $response = $this->post('/register', $formData);

    $response->assertSessionHasErrors(['password']);
  }

  /**
   * パスワード確認が一致しない場合、バリデーションエラーになる
   */
  public function test_password_confirmation_must_match()
  {
    $formData = [
      'name' => 'とわ',
      'email' => 'test@example.com',
      'password' => 'password123',
      'password_confirmation' => 'different',
    ];

    $response = $this->post('/register', $formData);

    $response->assertSessionHasErrors(['password']);
  }

  /**
   * 正常な入力で登録される
   */
  public function test_user_can_register_successfully()
  {
    $formData = [
      'name' => 'とわ',
      'email' => 'test@example.com',
      'password' => 'password123',
      'password_confirmation' => 'password123',
    ];

    $response = $this->post('/register', $formData);

    // データベースに保存されていることを確認
    $this->assertDatabaseHas('users', [
      'email' => 'test@example.com',
    ]);

    // Fortifyのメール認証画面に遷移していることを確認
    $response->assertRedirect('/email/verify');
  }
}
