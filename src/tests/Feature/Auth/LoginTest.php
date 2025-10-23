<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
  use RefreshDatabase;

  public function test_email_is_required()
  {
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

  public function test_login_fails_with_incorrect_credentials()
  {
    User::factory()->create([
      'email' => 'test@example.com',
      'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
      'email' => 'test@example.com',
      'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors([
      'email' => 'ログイン情報が登録されていません',
    ]);
  }
}
