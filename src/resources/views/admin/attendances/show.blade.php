@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendances/show.css') }}">
@endsection

@section('content')
<div class="attendance-detail-wrapper">
  <h1 class="page-title">勤怠詳細</h1>

  <div class="attendance-detail-card">
    <form id="attendanceForm" method="POST" action="">
      @csrf
      @method('PUT')
      <input type="hidden" name="user_id" value="{{ $attendance->user->id ?? request('user_id') }}">
      <input type="hidden" name="work_date" value="{{ $attendance->work_date ?? request('date') }}">

      <table class="attendance-detail-table">
        <tr>
          <th>名前</th>
          <td class="name-cell">{{ $attendance->user->name }}</td>
        </tr>

        <tr>
          <th>日付</th>
          <td class="date-cell">
            {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日') }}
          </td>
        </tr>

        <tr>
          <th>出勤・退勤</th>
          <td>
            @if(isset($latestCorrection) && $latestCorrection->status === 'pending')
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
        $isPending = isset($latestCorrection) && $latestCorrection->status === 'pending';
        $isEditable = !$isPending;
        $breakRecords = $latestCorrection && $latestCorrection->breaks->count() > 0
        ? $latestCorrection->breaks
        : ($attendance->breaks ?? collect());
        $maxBreaks = $isEditable ? $breakRecords->count() + 1 : $breakRecords->count();
        @endphp

        @for ($i = 1; $i <= max(1, $maxBreaks); $i++)
          @php
          $break=$breakRecords[$i - 1] ?? null;
          $start=$break?->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '';
          $end = $break?->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '';
          $label = ($i === 1) ? '休憩' : "休憩{$i}";
          $hasData = ($start || $end);
          @endphp

          @if($isEditable || $i === 1 || $hasData)
          <tr class="break-row" data-break="{{ $i }}">
            <th>{{ $label }}</th>
            <td>
              @if($isPending)
              <div class="time-range">
                <span class="fake-input">{{ $start }}</span>
                @if($start || $end)
                <span class="tilde">～</span>
                @endif
                <span class="fake-input">{{ $end }}</span>
              </div>
              @else
              <div class="time-range">
                <input type="text" name="breaks[{{ $i - 1 }}][start]" class="start-time"
                  value="{{ old('breaks.' . ($i - 1) . '.start', $start) }}">
                <span class="tilde">～</span>
                <input type="text" name="breaks[{{ $i - 1 }}][end]" class="end-time"
                  value="{{ old('breaks.' . ($i - 1) . '.end', $end) }}">
              </div>
              @error("break_start{$i}") <p class="error-message">{{ $message }}</p> @enderror
              @error("break_end{$i}") <p class="error-message">{{ $message }}</p> @enderror
              @endif
            </td>
          </tr>
          @endif
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
      const rows = form.querySelectorAll('.break-row');
      rows.forEach(row => {
        const i = parseInt(row.dataset.break);
        const startInput = row.querySelector(`[name="breaks[${i - 1}][start]"]`);
        const endInput = row.querySelector(`[name="breaks[${i - 1}][end]"]`);
        const start = startInput?.value.trim();
        const end = endInput?.value.trim();
        if (!start && !end && i !== 1) {
          row.style.display = 'none';
          if (startInput) startInput.removeAttribute('name');
          if (endInput) endInput.removeAttribute('name');
        }
      });
      form.submit();
    });
  });
</script>
@endif
@endsection