<?php

namespace App\Modules\Users\UseCases;

use App\Modules\Users\Exceptions\AuthenticationException;
use App\Modules\Users\Models\UsersModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginUseCase
{
    public function execute(array $credentials, mixed $session = null): mixed
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
