@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/auth/login.css') }}">
@endsection

@section('content')
<div class="login-container">
  <h1 class="login-title">管理者ログイン</h1>

  <form method="POST" action="{{ route('admin.login') }}" class="login-form" novalidate>
    @csrf

    <div class="form-group">
      <label for="email">メールアドレス</label>
      <input
        id="email"
        type="email"
        name="email"
        value="{{ old('email') }}"
        required
        autocomplete="username">
      @if ($errors->has('email') && $errors->first('email') !== 'ログイン情報が登録されていません')
      <div class="error-message">{{ $errors->first('email') }}</div>
      @endif
    </div>

    <div class="form-group">
      <label for="password">パスワード</label>
      <input
        id="password"
        type="password"
        name="password"
        required
        autocomplete="current-password">
      @if ($errors->has('password'))
      <div class="error-message">{{ $errors->first('password') }}</div>
      @endif
    </div>

    <button type="submit" class="login-button">管理者ログインする</button>

    @if ($errors->has('email') && $errors->first('email') === 'ログイン情報が登録されていません')
    <div class="error-center">{{ $errors->first('email') }}</div>
    @endif
  </form>
</div>
@endsection