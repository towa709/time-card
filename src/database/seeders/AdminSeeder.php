<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
  public function run(): void
  {
    Admin::updateOrCreate(
      ['email' => 'admin@example.com'],
      [
        'name'     => '山田 太郎',
        'password' => Hash::make('password123'),
      ]
    );
  }
}
