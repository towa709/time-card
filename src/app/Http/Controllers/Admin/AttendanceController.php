<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\AdminAttendanceUpdateRequest;

class AttendanceController extends Controller
{
  public function index(Request $request)
  {
    $currentDate = $request->date ? Carbon::parse($request->date) : Carbon::today();
    $users = User::all();

    $attendances = $users->map(function ($user) use ($currentDate) {
      $attendance = Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->whereDate('work_date', $currentDate->toDateString())
        ->first();

      if (!$attendance) {
        return (object)[
          'id' => 0,
          'user' => $user,
          'clock_in' => '',
          'clock_out' => '',
          'break_time' => '',
          'total_work_time' => '',
        ];
      }

      $clockIn  = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
      $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;

      $breakMinutes = $attendance->breaks->reduce(function ($carry, $break) {
        if ($break->break_start && $break->break_end) {
          return $carry + Carbon::parse($break->break_start)->diffInMinutes(Carbon::parse($break->break_end));
        }
        return $carry;
      }, 0);

      for ($i = 1; $i <= 5; $i++) {
        $start = "break_start{$i}";
        $end   = "break_end{$i}";
        if ($attendance->$start && $attendance->$end) {
          $breakMinutes += Carbon::parse($attendance->$start)->diffInMinutes(Carbon::parse($attendance->$end));
        }
      }

      $totalWork = '';
      if ($clockIn && $clockOut) {
        $minutes = $clockIn->diffInMinutes($clockOut) - $breakMinutes;
        $minutes = max($minutes, 0);
        $totalWork = sprintf('%d:%02d', floor($minutes / 60), $minutes % 60);
      }

      return (object)[
        'id'              => $attendance->id,
        'user'            => $user,
        'clock_in'        => $clockIn ? $clockIn->format('H:i') : '',
        'clock_out'       => $clockOut ? $clockOut->format('H:i') : '',
        'break_time'      => sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60),
        'total_work_time' => $totalWork,
      ];
    });

    return view('admin.attendances.index', [
      'attendances' => $attendances,
      'dateTitle'   => $currentDate->format('Y年n月j日'),
      'dateInput'   => $currentDate->format('Y/m/d'),
      'prevDate'    => $currentDate->copy()->subDay()->toDateString(),
      'nextDate'    => $currentDate->copy()->addDay()->toDateString(),
    ]);
  }

  public function show($id, Request $request)
  {
    $admin = auth('admin')->user();
    if (!$admin) abort(403, '管理者ログインが必要です。');

    $attendance = Attendance::with(['user', 'breaks', 'corrections.breaks'])->find($id);

    if (!$attendance) {
      $userId = $request->input('user_id') ?? session('selected_user_id');
      $workDate = $request->input('date') ?? session('selected_work_date') ?? Carbon::today()->toDateString();
      if (!$userId) abort(404, '対象ユーザー情報が見つかりません。');

      $attendance = new Attendance([
        'user_id'   => $userId,
        'work_date' => $workDate,
      ]);
      $attendance->setRelation('user', User::find($userId));
      $attendance->setRelation('breaks', collect());
      $attendance->setRelation('corrections', collect());
    }

    $latestCorrection = $attendance->corrections()->where('status', 'pending')->latest()->first();

    return view('admin.attendances.show', [
      'attendance'       => $attendance,
      'latestCorrection' => $latestCorrection,
    ]);
  }

  public function update(AdminAttendanceUpdateRequest $request, $id)
  {
    $attendance = Attendance::with('breaks')->find($id);

    if (!$attendance) {
      $attendance = new Attendance([
        'user_id'   => $request->user_id,
        'work_date' => $request->work_date,
      ]);
    }

    $workDate = $attendance->work_date;
    $clockIn  = $request->clock_in  ? Carbon::parse("{$workDate} {$request->clock_in}") : null;
    $clockOut = $request->clock_out ? Carbon::parse("{$workDate} {$request->clock_out}") : null;

    $attendance->fill([
      'clock_in'  => $clockIn,
      'clock_out' => $clockOut,
      'note'      => $request->note, 
    ])->save();

    if ($request->has('breaks')) {
      $attendance->breaks()->delete();
      foreach ($request->breaks as $break) {
        if (!empty($break['start']) && !empty($break['end'])) {
          $attendance->breaks()->create([
            'break_start' => Carbon::parse("{$workDate} {$break['start']}:00"),
            'break_end'   => Carbon::parse("{$workDate} {$break['end']}:00"),
          ]);
        }
      }
    }

    $attendance->refresh()->load('breaks');

    $breakMinutes = $attendance->breaks->reduce(function ($carry, $break) {
      if ($break->break_start && $break->break_end) {
        return $carry + Carbon::parse($break->break_start)->diffInMinutes(Carbon::parse($break->break_end));
      }
      return $carry;
    }, 0);

    for ($i = 1; $i <= 5; $i++) {
      $start = $request->input("break_start{$i}");
      $end   = $request->input("break_end{$i}");
      if ($start && $end) {
        $breakMinutes += Carbon::parse("{$workDate} {$start}")
          ->diffInMinutes(Carbon::parse("{$workDate} {$end}"));
      }
    }

    $totalWork = null;
    if ($clockIn && $clockOut) {
      $totalWork = $clockIn->diffInMinutes($clockOut) - $breakMinutes;
      if ($totalWork < 0) $totalWork = 0;
    }

    $attendance->update([
      'break_time'      => $breakMinutes,
      'total_work_time' => $totalWork,
      'updated_at'      => now(),
      'note'            => null,
    ]);

    $attendance->refresh()->load(['user', 'breaks', 'corrections.breaks']);

    if ($request->ajax()) {
      return response()->json(['success' => true]);
    }

    return redirect()
      ->to(route('admin.attendances.show', ['id' => $attendance->id]) . '?_=' . now()->timestamp)
      ->with('success')
      ->setStatusCode(303)
      ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
      ->header('Pragma', 'no-cache')
      ->header('Expires', '0');
  }

  public function userAttendances($id, Request $request)
  {
    $user = User::findOrFail($id);
    $month = $request->query('month', now()->format('Y-m'));
    $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
    $endOfMonth   = $startOfMonth->copy()->endOfMonth();

    $attendances = Attendance::with('breaks')
      ->where('user_id', $user->id)
      ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
      ->orderBy('work_date')
      ->get()
      ->map(function ($a) {
        $breakMinutes = $a->breaks->reduce(function ($carry, $break) {
          if ($break->break_start && $break->break_end) {
            return $carry + Carbon::parse($break->break_start)->diffInMinutes(Carbon::parse($break->break_end));
          }
          return $carry;
        }, 0);

        $totalWork = 0;
        if ($a->clock_in && $a->clock_out) {
          $totalWork = Carbon::parse($a->clock_in)->diffInMinutes(Carbon::parse($a->clock_out)) - $breakMinutes;
          $totalWork = max($totalWork, 0);
        }

        $a->break_time = $breakMinutes;
        $a->total_work_time = $totalWork;
        return $a;
      })
      ->keyBy(fn($a) => Carbon::parse($a->work_date)->toDateString());

    $days = \Carbon\CarbonPeriod::create($startOfMonth, $endOfMonth);

    return view('admin.users.attendances', [
      'user'         => $user,
      'attendances'  => $attendances,
      'days'         => $days,
      'currentMonth' => $startOfMonth->format('Y-m'),
      'prevMonth'    => $startOfMonth->copy()->subMonth()->format('Y-m'),
      'nextMonth'    => $startOfMonth->copy()->addMonth()->format('Y-m'),
    ]);
  }

  public function exportCsv($id, Request $request)
  {
    $user = User::findOrFail($id);
    $month = $request->query('month', now()->format('Y-m'));
    $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
    $endOfMonth   = $startOfMonth->copy()->endOfMonth();

    $attendances = Attendance::with('breaks')
      ->where('user_id', $user->id)
      ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
      ->orderBy('work_date')
      ->get()
      ->keyBy(fn($a) => Carbon::parse($a->work_date)->toDateString());

    $days = \Carbon\CarbonPeriod::create($startOfMonth, $endOfMonth);
    $csvHeader = ['日付', '出勤', '退勤', '休憩合計', '勤務時間'];
    $fmt = fn($m) => (is_null($m) || $m <= 0) ? '0:00' : sprintf('%d:%02d', intdiv($m, 60), $m % 60);

    $callback = function () use ($days, $attendances, $csvHeader, $fmt) {
      $file = fopen('php://output', 'w');
      fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
      fputcsv($file, $csvHeader);

      foreach ($days as $day) {
        $a = $attendances->get($day->toDateString());
        if (!$a) {
          fputcsv($file, [$day->format('Y/m/d'), '', '', '', '']);
          continue;
        }

        $clockIn  = $a->clock_in  ? Carbon::parse($a->clock_in)->format('H:i') : '';
        $clockOut = $a->clock_out ? Carbon::parse($a->clock_out)->format('H:i') : '';

        if (!$clockIn && !$clockOut) {
          fputcsv($file, [$day->format('Y/m/d'), '', '', '', '']);
          continue;
        }

        $breakMinutes = $a->breaks->reduce(function ($carry, $b) {
          if ($b->break_start && $b->break_end) {
            return $carry + Carbon::parse($b->break_start)->diffInMinutes(Carbon::parse($b->break_end));
          }
          return $carry;
        }, 0);

        $workTime = 0;
        if ($a->clock_in && $a->clock_out) {
          $workTime = Carbon::parse($a->clock_in)->diffInMinutes(Carbon::parse($a->clock_out)) - $breakMinutes;
          $workTime = max($workTime, 0);
        }

        fputcsv($file, [
          $day->format('Y/m/d'),
          $clockIn,
          $clockOut,
          $fmt($breakMinutes),
          $fmt($workTime),
        ]);
      }

      fclose($file);
    };

    $fileName = "{$user->name}_{$month}_attendances.csv";

    return response()->stream($callback, 200, [
      'Content-Type'        => 'text/csv',
      'Content-Disposition' => "attachment; filename={$fileName}",
    ]);
  }
}
