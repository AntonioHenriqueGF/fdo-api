<?php

namespace App\Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Exceptions\AuthenticationException;
use App\Modules\Users\UseCases\LoginUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Method to handle the '/login' route
    public function login(Request $request, LoginUseCase $loginUseCase)
    {
        try {
            return $this->successResponse('Login successful!', $loginUseCase->execute($request->only(['email', 'password']), $request->session()));
        } catch (AuthenticationException $th) {
            return $this->errorResponse($th->getMessage(), $th->getTrace(), 401);
        } catch (\Exception $th) {
            return $this->errorResponse('An error occurred during login.', $th->getMessage(), 500);
        }
        // Implement your login logic here, such as validating user credentials and generating a token
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout realizado'
        ]);
    }
}
