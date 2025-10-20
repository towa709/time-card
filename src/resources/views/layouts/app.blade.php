<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('subtitle', 'COACHTECH勤怠管理')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/common.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  @yield('css')
</head>

<body>
  <header class="header">
    <div class="header-left">
      @if(Auth::guard('admin')->check())
      <a href="{{ route('admin.attendances.index') }}" class="logo-link disabled-link">
        <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH ロゴ" class="logo">
      </a>
      @else
      <a href="{{ url('/attendance') }}" class="logo-link disabled-link">
        <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH ロゴ" class="logo">
      </a>
      @endif
    </div>

    <div class="header-right">
      @if(Auth::guard('admin')->check())
      <a href="{{ route('admin.attendances.index') }}" class="menu-button">勤怠一覧</a>
      <a href="{{ route('admin.users.index') }}" class="menu-button">スタッフ一覧</a>
      <a href="{{ route('admin.corrections.index') }}" class="menu-button">申請一覧</a>

      <a href="#" class="menu-button"
        onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
        ログアウト
      </a>
      <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
        @csrf
      </form>

      @elseif(Auth::check())
      <a href="{{ route('attendance.create') }}" class="menu-button">勤怠</a>
      <a href="{{ route('attendance.index') }}" class="menu-button">勤怠一覧</a>
      <a href="{{ route('corrections.index') }}" class="menu-button">申請</a>

      <a href="#" class="menu-button"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        ログアウト
      </a>
      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
      </form>
      @endif
    </div>
  </header>

  <main class="main-content">
    @yield('content')
  </main>

  @yield('scripts')
</body>

</html>