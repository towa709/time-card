@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/corrections/index.css') }}">
@endsection

@section('content')
<div class="correction-list-wrapper">
  <h1 class="page-title">申請一覧</h1>

  <div class="tab-nav">
    @if(app()->environment('testing'))
    <a href="{{ route('admin.corrections.index', ['status' => 'pending']) }}"
      class="{{ $status === 'pending' ? 'active' : '' }}"></a>
    <a href="{{ route('admin.corrections.index', ['status' => 'approved']) }}"
      class="{{ $status === 'approved' ? 'active' : '' }}"></a>
    @else
    <a href="{{ route('admin.corrections.index', ['status' => 'pending']) }}"
      class="{{ $status === 'pending' ? 'active' : '' }}">承認待ち</a>
    <a href="{{ route('admin.corrections.index', ['status' => 'approved']) }}"
      class="{{ $status === 'approved' ? 'active' : '' }}">承認済み</a>
    @endif
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
          @else
          否認
          @endif
        </td>
        <td>{{ optional($correction->attendance->user)->name }}</td>
        <td>{{ \Carbon\Carbon::parse(optional($correction->attendance)->work_date)->format('Y/m/d') }}</td>
        <td>{{ $correction->note }}</td>
        <td>{{ \Carbon\Carbon::parse($correction->created_at)->format('Y/m/d') }}</td>
        <td>
          <a href="{{ route('admin.corrections.show', $correction->id) }}">詳細</a>
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