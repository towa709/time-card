<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;

class AdminLoginTest extends TestCase
{
  use RefreshDatabase;

  public function test_admin_email_is_required()
  {
    Admin::factory()->create([
      'email' => 'admin@example.com',
      'password' => bcrypt('admin123'),
    ]);

    $response = $this->post('/admin/login', [
      'email' => '',
      'password' => 'admin123',
    ]);

    $response->assertSessionHasErrors(['email']);
  }

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

  public function test_admin_login_fails_with_incorrect_credentials()
  {
    Admin::factory()->create([
      'email' => 'admin@example.com',
      'password' => bcrypt('admin123'),
    ]);

    $response = $this->post('/admin/login', [
      'email' => 'admin@example.com',
      'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors([
      'email' => 'ログイン情報が登録されていません',
    ]);
  }
}
