@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
<div class="login-container">
  <h1 class="login-title">ログイン</h1>

  <form method="POST" action="{{ route('login') }}" class="login-form" novalidate>
    @csrf

    <div class="form-group">
      <label for="email">メールアドレス</label>
      <input id="email" type="text" name="email" value="{{ old('email') }}">
      @error('email')
      @if($message !== 'ログイン情報が登録されていません')
      <div class="error-message">{{ $message }}</div>
      @endif
      @enderror
    </div>

    <div class="form-group">
      <label for="password">パスワード</label>
      <input id="password" type="password" name="password">
      @error('password')
      <div class="error-message">{{ $message }}</div>
      @enderror
    </div>

    <div class="form-actions">
      <button type="submit" class="login-button">ログインする</button>
    </div>

    @error('email')
    @if($message === 'ログイン情報が登録されていません')
    <div class="error-message error-center">{{ $message }}</div>
    @endif
    @enderror
  </form>

  <div class="register-link">
    <a href="{{ route('register') }}">会員登録はこちら</a>
  </div>
</div>
@endsection