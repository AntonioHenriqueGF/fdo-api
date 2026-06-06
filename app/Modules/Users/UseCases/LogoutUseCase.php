<?php

namespace App\Modules\Users\UseCases;

use App\Modules\Users\Exceptions\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutUseCase
{
    public function execute(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
