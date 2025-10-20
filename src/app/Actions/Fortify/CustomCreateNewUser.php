<?php

namespace App\Actions\Fortify;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CustomCreateNewUser implements CreatesNewUsers
{
  /**
   * 会員登録処理
   *
   * @param  array  $input
   * @return \App\Models\User
   * @throws \Illuminate\Validation\ValidationException
   */
  public function create(array $input)
  {
    $formRequest = app(RegisterRequest::class);
    $formRequest->setContainer(app())
      ->setRedirector(app('redirect'))
      ->replace($input);

    $validator = app('validator')->make(
      $formRequest->all(),
      $formRequest->rules(),
      $formRequest->messages()
    );

    if ($validator->fails()) {
      throw new ValidationException($validator);
    }

    $validated = $validator->validated();

    return User::create([
      'name' => $validated['name'],
      'email' => $validated['email'],
      'password' => Hash::make($validated['password']),
    ]);
  }
}
