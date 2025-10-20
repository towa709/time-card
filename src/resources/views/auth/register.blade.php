@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="register-container">
  <h1 class="register-title">会員登録</h1>

  {{-- novalidate を付けてブラウザ標準バリデーションを無効化 --}}
  <form method="POST" action="{{ route('register') }}" class="register-form" novalidate>
    @csrf

    {{-- 名前 --}}
    <div class="form-group">
      <label for="name">名前</label>
      <input id="name" type="text" name="name" value="{{ old('name') }}">
      @error('name')
      <div class="error-message">{{ $message }}</div>
      @enderror
    </div>

    {{-- メールアドレス --}}
    <div class="form-group">
      <label for="email">メールアドレス</label>
      <input id="email" type="email" name="email" value="{{ old('email') }}">
      @error('email')
      <div class="error-message">{{ $message }}</div>
      @enderror
    </div>

    {{-- パスワード --}}
    <div class="form-group">
      <label for="password">パスワード</label>
      <input id="password" type="password" name="password">
      @error('password')
      @if($message !== 'パスワードと一致しません')
      <div class="error-message">{{ $message }}</div>
      @endif
      @enderror
    </div>

    {{-- パスワード確認 --}}
    <div class="form-group">
      <label for="password_confirmation">パスワード確認</label>
      <input id="password_confirmation" type="password" name="password_confirmation">
      @error('password_confirmation')
      <div class="error-message">{{ $message }}</div>
      @enderror
      @error('password')
      @if($message === 'パスワードと一致しません')
      <div class="error-message">{{ $message }}</div>
      @endif
      @enderror
    </div>


    {{-- 登録ボタン --}}
    <div class="form-actions">
      <button type="submit" class="register-button">登録する</button>
    </div>
  </form>

  <div class="login-link">
    <a href="{{ route('login') }}">ログインはこちら</a>
  </div>
</div>
@endsection