@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.css">
<link rel="stylesheet" href="{{ asset('css/admin/users/attendances.css') }}">
<style>
  .flatpickr-calendar {
    margin-top: 8px;
  }
</style>
@endsection

@section('content')
<div class="attendance-list-wrapper">
  <h1 class="page-title">{{ $user->name }}さんの勤怠</h1>

  <div class="month-nav-container">
    <div class="month-nav">
      <a href="{{ route('admin.attendances.user', ['id' => $user->id, 'month' => $prevMonth]) }}" class="nav-button">← 前月</a>

      <div class="month-picker" id="calendarContainer" style="position: relative; display: inline-block;">
        <img src="{{ asset('images/calendar-icon8.jpeg') }}" alt="カレンダー" class="calendar-icon" id="calendarIcon">
        <span class="current-month">{{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}</span>
        <input type="text" id="hiddenDateInput" style="display:none;">
      </div>

      <a href="{{ route('admin.attendances.user', ['id' => $user->id, 'month' => $nextMonth]) }}" class="nav-button">翌月 →</a>
    </div>
  </div>

  <div class="attendance-list-container">
    <table class="attendance-table">
      <thead>
        <tr>
          <th class="date-header">日付</th>
          <th>出勤</th>
          <th>退勤</th>
          <th>休憩</th>
          <th>合計</th>
          <th>詳細</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($days as $day)
        @php
        $attendance = $attendances[$day->toDateString()] ?? null;
        $hasWork = $attendance && ($attendance->clock_in || $attendance->clock_out);

        $clockIn = $hasWork ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '';
        $clockOut = $hasWork ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '';

        $breakMinutes = $hasWork ? ($attendance->break_time ?? 0) : 0;
        $totalMinutes = $hasWork ? ($attendance->total_work_time ?? 0) : 0;

        $breakTimeDisplay = $hasWork ? sprintf("%d:%02d", floor($breakMinutes / 60), $breakMinutes % 60) : '';
        $totalTimeDisplay = $hasWork ? sprintf("%d:%02d", floor($totalMinutes / 60), $totalMinutes % 60) : '';
        @endphp

        <tr>
          <td class="date-cell">
            <span class="date">{{ $day->format('m/d') }}</span>
            <span class="weekday">({{ ['日','月','火','水','木','金','土'][$day->dayOfWeek] }})</span>
          </td>
          <td>{{ $clockIn }}</td>
          <td>{{ $clockOut }}</td>
          <td>{{ $breakTimeDisplay }}</td>
          <td>{{ $totalTimeDisplay }}</td>
          <td class="detail-cell">
            <a href="{{ $attendance
                ? route('admin.attendances.show', ['id' => $attendance->id])
                : route('admin.attendances.show', ['id' => 0, 'user_id' => $user->id, 'date' => $day->toDateString()]) }}"
              class="detail-button"
              style="text-decoration:none;">
              詳細
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="form-footer">
    <a href="{{ route('admin.attendances.exportCsv', ['id' => $user->id, 'month' => $currentMonth]) }}" class="csv-button">
      CSV出力
    </a>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const icon = document.getElementById('calendarIcon');
    const container = document.getElementById('calendarContainer');

    const fp = flatpickr("#hiddenDateInput", {
      locale: "ja",
      dateFormat: "Y-m",
      defaultDate: "{{ $currentMonth }}",
      appendTo: container,
      plugins: [new monthSelectPlugin({
        shorthand: true,
        dateFormat: "Y-m",
        altFormat: "Y年m月",
      })],
      onChange: (selectedDates, dateStr) => {
        if (dateStr) {
          const url = "{{ route('admin.attendances.user', ['id' => $user->id]) }}?month=" + dateStr;
          window.location.href = url;
        }
      }
    });

    icon.addEventListener('click', () => fp.open());
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
@endsection