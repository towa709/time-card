@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.css">
<link rel="stylesheet" href="{{ asset('css/admin/attendances/index.css') }}">
<style>
  .flatpickr-calendar {
    margin-top: 8px;
  }
</style>
@endsection

@section('content')
<div class="attendance-list-wrapper">
  <h1 class="page-title">{{ $dateTitle }}の勤怠</h1>

  <div class="month-nav-container">
    <div class="month-nav">
      <a href="{{ route('admin.attendances.index', ['date' => $prevDate]) }}" class="nav-button">← 前日</a>

      <div class="month-picker" id="calendarContainer" style="position: relative; display: inline-block;">
        <img src="{{ asset('images/calendar-icon8.jpeg') }}" alt="カレンダー" class="calendar-icon" id="calendarIcon" style="cursor: pointer;">
        <span class="current-month">{{ \Carbon\Carbon::parse($dateInput)->format('Y/m/d') }}</span>
        <input type="text" id="hiddenDateInput" style="display:none;">
      </div>

      <a href="{{ route('admin.attendances.index', ['date' => $nextDate]) }}" class="nav-button">翌日 →</a>
    </div>
  </div>

  <div class="attendance-list-container">
    <table class="attendance-table">
      <thead>
        <tr>
          <th>名前</th>
          <th>出勤</th>
          <th>退勤</th>
          <th>休憩</th>
          <th>合計</th>
          <th>詳細</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($attendances as $attendance)
        @php
        $hasWork = $attendance->id && ($attendance->clock_in || $attendance->clock_out);
        @endphp
        <tr>
          <td>{{ $attendance->user->name }}</td>
          <td>{{ $hasWork ? $attendance->clock_in : '' }}</td>
          <td>{{ $hasWork ? $attendance->clock_out : '' }}</td>
          <td>{{ $hasWork ? $attendance->break_time : '' }}</td>
          <td>{{ $hasWork ? $attendance->total_work_time : '' }}</td>
          <td>
            <a href="{{ $attendance->id
                ? route('admin.attendances.show', ['id' => $attendance->id])
                : route('admin.attendances.show', [
                    'id' => 0,
                    'user_id' => $attendance->user->id,
                    'date' => \Carbon\Carbon::parse($dateInput)->toDateString()
                  ]) }}" class="detail-button">
              詳細
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
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
      dateFormat: "Y-m-d",
      defaultDate: "{{ $dateInput }}",
      appendTo: container,
      onChange: (selectedDates, dateStr) => {
        if (dateStr) {
          const url = "{{ route('admin.attendances.index') }}?date=" + dateStr;
          window.location.href = url;
        }
      }
    });

    icon.addEventListener('click', () => fp.open());
  });
</script>
@endsection