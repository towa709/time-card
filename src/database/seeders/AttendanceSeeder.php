<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkBreak;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceSeeder extends Seeder
{
  public function run(): void
  {
    $months = [
      now()->subMonthNoOverflow()->startOfMonth(),
      now()->startOfMonth(),
    ];

    $users = User::all();

    foreach ($users as $user) {
      foreach ($months as $monthStart) {
        $monthEnd = $monthStart->copy()->endOfMonth();
        $days = CarbonPeriod::create($monthStart, $monthEnd);

        foreach ($days as $day) {
          if ($day->isWeekend()) continue;
          if (random_int(1, 100) <= 10) continue;

          $clockIn = $day->copy()->setTime(rand(8, 10), [0, 15, 30, 45][array_rand([0, 15, 30, 45])]);
          $clockOut = $clockIn->copy()->addHours(rand(9, 10))->addMinutes([0, 15, 30, 45][array_rand([0, 15, 30, 45])]);

          $break1Start = $day->copy()->setTime(12, 0);
          $break1End   = $day->copy()->setTime(13, 0);

          $break2Start = null;
          $break2End   = null;
          if (random_int(1, 100) <= 20) {
            $break2Start = $day->copy()->setTime(15, 0);
            $break2End   = $break2Start->copy()->addMinutes(10);
          }

          $breakMinutes = 0;
          if ($break1End > $clockIn && $break1Start < $clockOut) {
            $breakMinutes += $break1Start->diffInMinutes($break1End);
          }
          if ($break2Start && $break2End && $break2End > $clockIn && $break2Start < $clockOut) {
            $breakMinutes += $break2Start->diffInMinutes($break2End);
          }

          $totalWork = $clockOut->diffInMinutes($clockIn) - $breakMinutes;
          if ($totalWork < 0) $totalWork = 0;

          // ✅ Attendance 登録
          $attendance = Attendance::updateOrCreate(
            [
              'user_id'   => $user->id,
              'work_date' => $day->toDateString(),
            ],
            [
              'clock_in'        => $clockIn,
              'clock_out'       => $clockOut,
              'break_time'      => $breakMinutes,
              'total_work_time' => $totalWork,
            ]
          );

          // ✅ WorkBreak 再登録（日時付き）
          WorkBreak::where('attendance_id', $attendance->id)->delete();

          WorkBreak::create([
            'attendance_id' => $attendance->id,
            'break_start'   => $day->copy()->setTime(12, 0), // ← 日付付きに修正
            'break_end'     => $day->copy()->setTime(13, 0),
          ]);

          if ($break2Start && $break2End) {
            WorkBreak::create([
              'attendance_id' => $attendance->id,
              'break_start'   => $break2Start,
              'break_end'     => $break2End,
            ]);
          }
        }
      }
    }
  }
}
