<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\AdminLoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    $this->app->singleton(CreatesNewUsers::class, CreateNewUser::class);
  }

  public function boot(): void
  {
    Fortify::loginView(function () {
      if (request()->is('admin/*')) {
        return view('admin.auth.login');
      }
      return view('auth.login');
    });

    Fortify::registerView(fn() => view('auth.register'));
    Fortify::requestPasswordResetLinkView(fn() => view('auth.forgot-password'));
    Fortify::resetPasswordView(fn($request) => view('auth.reset-password', ['request' => $request]));
    Fortify::verifyEmailView(fn() => view('auth.verify-email'));

    $this->app->singleton(LogoutResponse::class, function () {
      return new class implements LogoutResponse {
        public function toResponse($request)
        {
          if ($request->is('admin/*') || Auth::guard('admin')->check()) {
            return redirect('/admin/login');
          }
          return redirect('/login');
        }
      };
    });

    $this->app->singleton(RegisterResponse::class, function () {
      return new class implements RegisterResponse {
        public function toResponse($request)
        {
          return redirect()->route('verification.notice');
        }
      };
    });

    $this->app->singleton(LoginResponse::class, function () {
      return new class implements LoginResponse {
        public function toResponse($request)
        {
          if (Auth::guard('admin')->check()) {
            return redirect('/admin/attendances/list');
          }
          return redirect('/attendance');
        }
      };
    });

    RateLimiter::for('login', function ($request) {
      $email = (string) $request->email;
      return [
        Limit::perMinute(5)->by($email . $request->ip()),
      ];
    });

    $this->app->bind(FortifyLoginRequest::class, function ($app) {
      if (request()->is('admin/*')) {
        return $app->make(AdminLoginRequest::class);
      }
      return $app->make(LoginRequest::class);
    });

    Fortify::authenticateUsing(function ($request) {
      if ($request->is('admin/*')) {
        $admin = Admin::where('email', $request->email)->first();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
          $validator = Validator::make([], []);
          $validator->errors()->add('email', $request->authenticationFailedMessage());
          throw new ValidationException($validator);
        }

        Auth::guard('admin')->login($admin);
        return $admin;
      }

      $user = User::where('email', $request->email)->first();

      if (! $user || ! Hash::check($request->password, $user->password)) {
        $validator = Validator::make([], []);
        $validator->errors()->add('email', $request->authenticationFailedMessage());
        throw new ValidationException($validator);
      }

      Auth::login($user);
      return $user;
    });
  }
}
