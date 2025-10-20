<?php

namespace App\Http\Requests;

use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class LoginRequest extends FortifyLoginRequest
{
  public function rules(): array
  {
    return [
      'email' => ['required', 'email'],
      'password' => ['required', 'string'],
    ];
  }

  public function messages(): array
  {
    return [
      'email.required' => 'メールアドレスを入力してください',
      'email.email' => '正しいメールアドレス形式で入力してください',
      'password.required' => 'パスワードを入力してください',
    ];
  }

  public function authenticationFailedMessage(): string
  {
    return 'ログイン情報が登録されていません';
  }
}
