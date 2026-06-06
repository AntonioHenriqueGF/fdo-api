<?php

namespace App\Modules\Users\UseCases;

use App\Modules\Users\Exceptions\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class MeUseCase
{
    public function execute(): mixed
    {
        $user = Auth::user();

        if (!$user) {
            throw new AuthenticationException();
        }

        return $user;
    }
}
