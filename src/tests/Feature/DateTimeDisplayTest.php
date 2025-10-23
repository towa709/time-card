<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DateTimeDisplayTest extends TestCase
{
  use RefreshDatabase;


  public function test_current_datetime_is_displayed_correctly()
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $now = Carbon::now()->format('Y年n月j日');

    $response = $this->get('/attendance');

    $response->assertStatus(200);

    $response->assertSee($now);
  }
}
