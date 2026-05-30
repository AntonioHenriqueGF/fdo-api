<?php

namespace App\Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\UseCases\LoginUseCase;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    // Method to handle the '/login' route
    public function login(Request $request, LoginUseCase $loginUseCase)
    {

        $loginUseCase->execute($request->only(['email', 'password']));
        // Implement your login logic here, such as validating user credentials and generating a token
        return $this->successResponse('Login successful!');
    }
}
