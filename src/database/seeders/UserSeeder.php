<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
  public function run(): void
  {
    $names = [
      '佐藤 太郎',
      '鈴木 花子',
      '高橋 健',
      '田中 美咲',
      '伊藤 翔',
      '渡辺 直子',
      '山本 大輔',
      '中村 由美',
      '小林 誠',
      '加藤 さくら',
    ];

    foreach ($names as $i => $name) {
      User::updateOrCreate(
        ['email' => sprintf('user%02d@example.com', $i + 1)],
        [
          'name' => $name,
          'password' => Hash::make('password123'),
          'role' => 'user',
          'email_verified_at' => now(),
        ]
      );
    }
  }
}
