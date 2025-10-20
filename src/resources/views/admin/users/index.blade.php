@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/users/index.css') }}">
@endsection

@section('content')
<div class="user-list-wrapper">
  <h1 class="page-title">スタッフ一覧</h1>

  <table class="user-table">
    <thead>
      <tr>
        <th>名前</th>
        <th>メールアドレス</th>
        <th>月次勤怠</th>
      </tr>
    </thead>
    <tbody>
      @foreach($users as $user)
      <tr>
        <td>{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        <td><a href="{{ route('admin.attendances.user', ['id' => $user->id]) }}">詳細</a></td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection