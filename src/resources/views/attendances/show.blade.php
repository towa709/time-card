@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/show.css') }}">
@endsection

@section('content')
<div class="attendance-detail-wrapper">
  <h1 class="page-title">勤怠詳細</h1>

  @php
  $isPending = $latestCorrection && $latestCorrection->status === 'pending';
  @endphp

  <div class="attendance-detail-card">
    <form id="attendanceForm" method="POST" action="{{ route('corrections.store') }}">
      @csrf
      <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

      <table class="attendance-detail-table">
        <tr>
          <th>名前</th>
          <td class="name-cell">{{ $attendance->user->name }}</td>
        </tr>

        <tr>
          <th>日付</th>
          <td class="date-cell">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日') }}</td>
        </tr>

        <tr>
          <th>出勤・退勤</th>
          <td>
            @if($isPending)
            <div class="time-range">
              <span class="fake-input">
                {{ $latestCorrection->requested_clock_in
                      ? \Carbon\Carbon::parse($latestCorrection->requested_clock_in)->format('H:i')
                      : ($attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}
              </span>
              <span class="tilde">～</span>
              <span class="fake-input">
                {{ $latestCorrection->requested_clock_out
                      ? \Carbon\Carbon::parse($latestCorrection->requested_clock_out)->format('H:i')
                      : ($attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}
              </span>
            </div>
            @else
            <div class="time-range">
              <input type="text" name="clock_in" class="start-time"
                value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
              <span class="tilde">～</span>
              <input type="text" name="clock_out" class="end-time"
                value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
            </div>
            @error('clock_in') <p class="error-message">{{ $message }}</p> @enderror
            @error('clock_out') <p class="error-message">{{ $message }}</p> @enderror
            @endif
          </td>
        </tr>

        @php
        $breakRecords = $isPending && $latestCorrection->breaks->count() > 0
        ? $latestCorrection->breaks
        : ($attendance->breaks ?? collect());

        $maxBreaks = $isPending
        ? max(1, $breakRecords->count())
        : max(1, $breakRecords->count() + 1);
        @endphp

        @for ($i = 0; $i < $maxBreaks; $i++)
          @php
          $break=$breakRecords[$i] ?? null;
          $start=$break?->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '';
          $end = $break?->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '';
          $label = ($i === 0) ? '休憩' : '休憩' . ($i + 1);
          $startKey = "break_start" . ($i + 1);
          $endKey = "break_end" . ($i + 1);
          @endphp

          <tr class="break-row" data-break="{{ $i }}">
            <th>{{ $label }}</th>
            <td>
              @if($isPending)
              <div class="time-range">
                <span class="fake-input">{{ $start }}</span>
                <span class="tilde">～</span>
                <span class="fake-input">{{ $end }}</span>
              </div>
              @else
              <div class="time-range">
                <input type="text" name="breaks[{{ $i }}][start]" class="start-time"
                  value="{{ old("breaks.$i.start", $start) }}">
                <span class="tilde">～</span>
                <input type="text" name="breaks[{{ $i }}][end]" class="end-time"
                  value="{{ old("breaks.$i.end", $end) }}">
              </div>

              @error($startKey)
              <p class="error-message">{{ $message }}</p>
              @enderror
              @error($endKey)
              <p class="error-message">{{ $message }}</p>
              @enderror
              @endif
            </td>
          </tr>
          @endfor

          <tr>
            <th>備考</th>
            <td>
              @if($isPending)
              <div class="text-note">{{ $latestCorrection->note ?? $attendance->note }}</div>
              @else
              <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
              @error('note') <p class="error-message">{{ $message }}</p> @enderror
              @endif
            </td>
          </tr>
      </table>
    </form>
  </div>

  @if($isPending)
  <p class="pending-message">*承認待ちのため修正はできません。</p>
  @else
  <div class="form-footer">
    <button type="button" id="updateButton" class="btn-submit">修正</button>
  </div>
  @endif
</div>

@if(!$isPending)
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('attendanceForm');
    const button = document.getElementById('updateButton');
    if (!form || !button) return;

    button.addEventListener('click', e => {
      e.preventDefault();
      const rows = document.querySelectorAll('.break-row');
      rows.forEach(row => {
        const index = row.dataset.break;
        const startInput = row.querySelector(`[name="breaks[${index}][start]"]`);
        const endInput = row.querySelector(`[name="breaks[${index}][end]"]`);
        const start = startInput?.value.trim();
        const end = endInput?.value.trim();
        if (!start && !end && index !== '0') {
          startInput.removeAttribute('name');
          endInput.removeAttribute('name');
          row.style.display = 'none';
        }
      });
      form.submit();
    });
  });
</script>
@endif
@endsection