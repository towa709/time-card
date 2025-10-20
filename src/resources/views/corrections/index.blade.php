@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/corrections/index.css') }}">
@endsection

@section('content')
<div class="correction-list-wrapper">
  <h1 class="page-title">申請一覧</h1>

  <div class="tab-nav">
    <a href="{{ route('corrections.index', ['status' => 'pending']) }}"
      class="{{ $status === 'pending' ? 'active' : '' }}">承認待ち</a>
    <a href="{{ route('corrections.index', ['status' => 'approved']) }}"
      class="{{ $status === 'approved' ? 'active' : '' }}">承認済み</a>
  </div>

  <table class="correction-table">
    <thead>
      <tr>
        <th>状態</th>
        <th>名前</th>
        <th>対象日時</th>
        <th>申請理由</th>
        <th>申請日時</th>
        <th>詳細</th>
      </tr>
    </thead>
    <tbody>
      @forelse($corrections as $correction)
      <tr>
        <td>
          @if($correction->status === 'pending')
          承認待ち
          @elseif($correction->status === 'approved')
          承認済み
          @endif
        </td>
        <td>{{ $correction->attendance->user->name }}</td>
        <td>{{ \Carbon\Carbon::parse($correction->attendance->work_date)->format('Y/m/d') }}</td>
        <td>{{ $correction->note }}</td>
        <td>{{ \Carbon\Carbon::parse($correction->created_at)->format('Y/m/d') }}</td>
        <td>
          <a href="{{ route('attendance.detail', $correction->attendance->id) }}">詳細</a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6">対象の申請はありません。</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection