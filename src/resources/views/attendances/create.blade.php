@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendances/create.css') }}">
@endsection

@section('content')
<div class="attendance-wrapper">
  <div class="attendance-container">
    <div class="status-badge">
      @if($status === 'before_work')
      勤務外
      @elseif($status === 'working')
      出勤中
      @elseif($status === 'break')
      休憩中
      @elseif($status === 'after_work')
      退勤済
      @endif
    </div>

    <div class="date">
      {{ $date }}<span class="weekday">（{{ $weekday }}）</span>
    </div>

    <time id="current-time" class="time" aria-live="polite">
      <span id="hours"></span><span class="colon">:</span><span id="minutes"></span>
    </time>

    <div class="buttons">
      @if($status === 'before_work')
      <form method="POST" action="{{ route('attendance.start') }}">
        @csrf
        <button type="submit" class="btn black">出勤</button>
      </form>
      @elseif($status === 'working')
      <form method="POST" action="{{ route('attendance.end') }}">
        @csrf
        <button type="submit" class="btn black">退勤</button>
      </form>
      <form method="POST" action="{{ route('attendance.break_in') }}">
        @csrf
        <button type="submit" class="btn white">休憩入</button>
      </form>
      @elseif($status === 'break')
      <form method="POST" action="{{ route('attendance.break_out') }}">
        @csrf
        <button type="submit" class="btn white">休憩戻</button>
      </form>
      @elseif($status === 'after_work')
      <p class="after-work">お疲れ様でした。</p>
      @endif
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('hours').textContent = hours;
    document.getElementById('minutes').textContent = minutes;
  }

  updateClock();

  setInterval(updateClock, 1000);
</script>
@endsection