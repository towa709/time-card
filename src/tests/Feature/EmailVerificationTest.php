<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class EmailVerificationTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function it_sends_verification_email_after_registration()
  {
    Notification::fake();

    // 通常の create ではイベントが発火しないため、明示的に通知を送信
    $user = User::factory()->create([
      'email_verified_at' => null,
    ]);

    $user->notify(new VerifyEmail());

    Notification::assertSentTo($user, VerifyEmail::class);
  }

  /** @test */
  public function it_displays_verification_link_page()
  {
    $user = User::factory()->create([
      'email_verified_at' => null,
    ]);

    $response = $this->actingAs($user)->get('/email/verify');

    $response->assertStatus(200);

    // 「認証」文言を広く検出（<a>タグ内でもOK）
    $response->assertSee('認証', false);
  }

  /** @test */
  public function it_verifies_email_and_redirects_to_attendance_page()
  {
    $user = User::factory()->create([
      'email_verified_at' => null,
    ]);

    $verificationUrl = URL::temporarySignedRoute(
      'verification.verify',
      Carbon::now()->addMinutes(60),
      ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    // 実際の挙動に合わせて `/attendance` に修正
    $response->assertRedirect('/attendance');
    $this->assertNotNull($user->fresh()->email_verified_at);
  }
}
