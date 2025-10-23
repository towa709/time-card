<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
  use RefreshDatabase;

  public function test_name_is_required()
  {

    $formData = [
      'name' => '',
      'email' => 'test@example.com',
      'password' => 'password123',
      'password_confirmation' => 'password123',
    ];

    $response = $this->post('/register', $formData);

    $response->assertSessionHasErrors(['name']);
  }

  public function test_email_is_required()
  {
    $formData = [
      'name' => '田中',
      'email' => '',
      'password' => 'password123',
      'password_confirmation' => 'password123',
    ];

    $response = $this->post('/register', $formData);

    $response->assertSessionHasErrors(['email']);
  }

  public function test_password_must_be_at_least_8_characters()
  {
    $formData = [
      'name' => '田中',
      'email' => 'test@example.com',
      'password' => 'short',
      'password_confirmation' => 'short',
    ];

    $response = $this->post('/register', $formData);

    $response->assertSessionHasErrors(['password']);
  }

  public function test_password_confirmation_must_match()
  {
    $formData = [
      'name' => '田中',
      'email' => 'test@example.com',
      'password' => 'password123',
      'password_confirmation' => 'different',
    ];

    $response = $this->post('/register', $formData);

    $response->assertSessionHasErrors(['password']);
  }

  public function test_user_can_register_successfully()
  {
    $formData = [
      'name' => '田中',
      'email' => 'test@example.com',
      'password' => 'password123',
      'password_confirmation' => 'password123',
    ];

    $response = $this->post('/register', $formData);

    $this->assertDatabaseHas('users', [
      'email' => 'test@example.com',
    ]);

    $response->assertRedirect('/email/verify');
  }
}
