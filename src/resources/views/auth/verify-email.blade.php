@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-container">

  <div class="verify-message">
    <h2>
      登録していただいたメールアドレスに認証メールを送付しました。<br>
      メール認証を完了してください。
    </h2>
  </div>

  <div>
    @if(isset($verificationUrl))
    <a href="{{ $verificationUrl }}" class="verify-button">
      認証はこちらから
    </a>
    @else
    <a href="#" class="verify-button" aria-disabled="true">認証はこちらから</a>
    @endif
  </div>

  <div>
    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="verify-resend">
        認証メールを再送する
      </button>
      @if (session('message'))
      <p class="verify-alert">{{ session('message') }}</p>
      @endif
    </form>
  </div>

</div>
@endsection