<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Http\Requests\AttendanceCorrectionRequest;
use Carbon\Carbon;

class AttendanceCorrectionController extends Controller
{
  private const STATUS_PENDING = 'pending';
  private const STATUS_APPROVED = 'approved';

  public function index(Request $request)
  {
    $status = $request->query('status', self::STATUS_PENDING);

    $query = AttendanceCorrection::with('attendance.user')
      ->whereHas('attendance', fn($q) => $q->where('user_id', auth()->id()));

    if ($status === self::STATUS_PENDING) {
      $query->where('status', self::STATUS_PENDING);
    } elseif ($status === self::STATUS_APPROVED) {
      $query->where('status', self::STATUS_APPROVED);
    }

    $corrections = $query->orderByDesc('created_at')->get();

    return view('corrections.index', compact('corrections', 'status'));
  }

  public function show($id)
  {
    $attendance = Attendance::with(['user', 'breaks', 'corrections.breaks'])->findOrFail($id);

    $latestCorrection = $attendance->corrections()
      ->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED])
      ->with('breaks')
      ->latest()
      ->first();

    $displayBreaks = $latestCorrection && $latestCorrection->breaks->isNotEmpty()
      ? $latestCorrection->breaks
      : $attendance->breaks;

    return view('attendances.show', [
      'attendance'       => $attendance,
      'latestCorrection' => $latestCorrection,
      'displayBreaks'    => $displayBreaks,
    ]);
  }

  public function store(AttendanceCorrectionRequest $request)
  {
    $attendance = Attendance::findOrFail($request->attendance_id);

    $correction = AttendanceCorrection::create([
      'attendance_id'       => $attendance->id,
      'user_id'             => auth()->id(),
      'requested_clock_in'  => $request->clock_in,
      'requested_clock_out' => $request->clock_out,
      'note'                => $request->note,
      'status'              => self::STATUS_PENDING,
    ]);

    $correction->refresh();

    for ($i = 1; $i <= 5; $i++) {
      $startKey = "break_start{$i}";
      $endKey   = "break_end{$i}";
      $start    = $request->input($startKey);
      $end      = $request->input($endKey);

      if (!empty($start) && !empty($end)) {
        $startDateTime = Carbon::parse("{$attendance->work_date} {$start}:00");
        $endDateTime   = Carbon::parse("{$attendance->work_date} {$end}:00");

        $correction->breaks()->create([
          'break_start' => $startDateTime,
          'break_end'   => $endDateTime,
        ]);
      }
    }

    return redirect()
      ->route('attendance.detail', $attendance->id)
      ->with('success');
  }
}
