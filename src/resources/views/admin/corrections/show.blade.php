@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/corrections/show.css') }}">
@endsection

@section('content')
<div class="attendance-detail-wrapper">
  <h1 class="page-title">勤怠詳細</h1>

  <div class="attendance-detail-card" data-correction-id="{{ $correction->id }}">
    <table class="attendance-detail-table">
      <tr>
        <th>名前</th>
        <td class="name-cell">{{ optional($correction->attendance->user)->name }}</td>
      </tr>

      <tr>
        <th>日付</th>
        <td class="date-cell">
          @php $d = \Carbon\Carbon::parse(optional($correction->attendance)->work_date); @endphp
          <span class="year">{{ $d->format('Y年') }}</span>
          <span class="month">{{ $d->format('n月') }}</span>
          <span class="day">{{ $d->format('j日') }}</span>
        </td>
      </tr>

      <tr>
        <th>出勤・退勤</th>
        <td>
          <div class="time-range">
            <span class="fake-input">
              {{ $correction->requested_clock_in ? \Carbon\Carbon::parse($correction->requested_clock_in)->format('H:i') : '' }}
            </span>
            <span class="tilde">～</span>
            <span class="fake-input">
              {{ $correction->requested_clock_out ? \Carbon\Carbon::parse($correction->requested_clock_out)->format('H:i') : '' }}
            </span>
          </div>
        </td>
      </tr>

      @php
      $breaks = $correction->breaks ?? collect();
      $first = $breaks->get(0);
      $second = $breaks->get(1);
      @endphp

      <tr>
        <th>休憩</th>
        <td>
          <div class="time-range">
            @if($first)
            <span class="fake-input">{{ \Carbon\Carbon::parse($first->break_start)->format('H:i') }}</span>
            <span class="tilde">～</span>
            <span class="fake-input">{{ \Carbon\Carbon::parse($first->break_end)->format('H:i') }}</span>
            @else
            <span class="fake-input"></span>
            <span class="tilde" style="visibility:hidden;">～</span>
            <span class="fake-input"></span>
            @endif
          </div>
        </td>
      </tr>

      <tr>
        <th>休憩2</th>
        <td>
          <div class="time-range">
            @if($second)
            <span class="fake-input">{{ \Carbon\Carbon::parse($second->break_start)->format('H:i') }}</span>
            <span class="tilde">～</span>
            <span class="fake-input">{{ \Carbon\Carbon::parse($second->break_end)->format('H:i') }}</span>
            @else
            <span class="fake-input"></span>
            <span class="tilde" style="visibility:hidden;">～</span>
            <span class="fake-input"></span>
            @endif
          </div>
        </td>
      </tr>

      @foreach($breaks->slice(2) as $index => $break)
      <tr>
        <th>休憩{{ $index + 3 }}</th>
        <td>
          <div class="time-range">
            <span class="fake-input">{{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}</span>
            <span class="tilde">～</span>
            <span class="fake-input">{{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}</span>
          </div>
        </td>
      </tr>
      @endforeach

      <tr>
        <th>備考</th>
        <td>{{ $correction->note }}</td>
      </tr>
    </table>
  </div>

  <div class="form-footer">
    @if($correction->status === 'pending')
    <button type="button" id="approveButton" class="btn-approve">承認</button>
    @else
    <button class="btn-approve" disabled>承認済み</button>
    @endif
  </div>
  @endsection

  @section('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const btn = document.getElementById('approveButton');
      if (!btn) return;

      btn.addEventListener('click', async () => {
        const id = document.querySelector('.attendance-detail-card').dataset.correctionId;
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const url = `/admin/stamp_correction_request/approve/${id}`;

        btn.disabled = true;
        btn.style.backgroundColor = '#888';
        btn.style.cursor = 'not-allowed';

        try {
          const response = await fetch(url, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': token,
              'Accept': 'application/json'
            },
            credentials: 'same-origin'
          });

        } catch (error) {
          console.error('通信エラー:', error);
        }
      });
    });
  </script>
  @endsection