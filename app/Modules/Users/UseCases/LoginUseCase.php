<?php

namespace App\Modules\Users\UseCases;

use App\Modules\Users\Exceptions\AuthenticationException;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;

class LoginUseCase
{
    public function execute(array $credentials, Session $session): mixed
    {
        $credentials = [
            'use_email' => $credentials['email'],
            'password' => $credentials['password'],
        ];


        if (!Auth::attempt($credentials)) {
            throw new AuthenticationException();
        }

        $user = Auth::user();

        if ($session) {
            $session->regenerate();
            $session->put('user_id', $user->id);
        }

        return $user;
    }
}
