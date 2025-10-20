<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrection;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceCorrectionController extends Controller
{
  private const STATUS_PENDING = 'pending';
  private const STATUS_APPROVED = 'approved';

  public function index(Request $request)
  {
    $status = $request->query('status', self::STATUS_PENDING);

    $corrections = AttendanceCorrection::with('attendance.user')
      ->when($status === self::STATUS_PENDING, fn($q) => $q->where('status', self::STATUS_PENDING))
      ->when($status === self::STATUS_APPROVED, fn($q) => $q->where('status', self::STATUS_APPROVED))
      ->orderByDesc('created_at')
      ->get();

    return view('admin.corrections.index', compact('corrections', 'status'));
  }

  public function show($id)
  {
    $correction = AttendanceCorrection::with(['attendance.user', 'breaks'])->findOrFail($id);

    return view('admin.corrections.show', ['correction' => $correction]);
  }

  public function approve($id)
  {
    if (!auth('admin')->check()) {
      return response()->json(['error' => 'not authenticated'], 401);
    }

    $correction = AttendanceCorrection::with(['attendance', 'attendance.breaks', 'breaks'])->find($id);

    if (!$correction || !$correction->attendance) {
      return response()->json(['error' => 'not found'], 404);
    }

    $attendance = $correction->attendance;
    $workDate = $attendance->work_date ?? now()->toDateString();

    $in  = $correction->requested_clock_in;
    $out = $correction->requested_clock_out;
    if ($in && strlen($in) === 5)  $in  .= ':00';
    if ($out && strlen($out) === 5) $out .= ':00';

    $attendance->clock_in = !str_contains($in, $workDate)
      ? Carbon::parse("{$workDate} {$in}")->format('Y-m-d H:i:s')
      : Carbon::parse($in)->format('Y-m-d H:i:s');

    $attendance->clock_out = !str_contains($out, $workDate)
      ? Carbon::parse("{$workDate} {$out}")->format('Y-m-d H:i:s')
      : Carbon::parse($out)->format('Y-m-d H:i:s');

    $attendance->breaks()->delete();
    foreach ($correction->breaks as $break) {
      $start = $break->break_start;
      $end   = $break->break_end;

      if ($start && strlen($start) === 5) $start .= ':00';
      if ($end && strlen($end) === 5) $end .= ':00';
      if ($start && !str_contains($start, $workDate)) $start = "{$workDate} {$start}";
      if ($end && !str_contains($end, $workDate)) $end = "{$workDate} {$end}";

      $attendance->breaks()->create([
        'break_start' => $start,
        'break_end'   => $end,
      ]);
    }

    $attendance->load('breaks');

    $breakMinutes = $attendance->breaks->sum(
      fn($b) =>
      $b->break_start && $b->break_end
        ? Carbon::parse($b->break_start)->diffInMinutes(Carbon::parse($b->break_end))
        : 0
    );

    $totalWork = 0;
    if ($attendance->clock_in && $attendance->clock_out) {
      $totalWork = Carbon::parse($attendance->clock_in)
        ->diffInMinutes(Carbon::parse($attendance->clock_out)) - $breakMinutes;
      $totalWork = max($totalWork, 0);
    }

    $attendance->update([
      'break_time'      => $breakMinutes,
      'total_work_time' => $totalWork,
    ]);

    $correction->status = self::STATUS_APPROVED;
    $correction->approved_by = auth('admin')->id();
    $correction->saveOrFail();

    return response()->json(['status' => 'approved']);
  }
}
