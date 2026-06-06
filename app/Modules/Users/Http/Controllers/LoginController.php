<?php

namespace App\Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Exceptions\AuthenticationException;
use App\Modules\Users\Http\Requests\LoginRequest;
use App\Modules\Users\UseCases\LoginUseCase;
use App\Modules\Users\UseCases\LogoutUseCase;
use App\Modules\Users\UseCases\MeUseCase;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    // Method to handle the '/login' route
    public function login(LoginRequest $request, LoginUseCase $loginUseCase)
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

    public function me(MeUseCase $meUseCase)
    {
        try {
            return $this->successResponse('User retrieved successfully!', $meUseCase->execute());
        } catch (AuthenticationException $th) {
            return $this->errorResponse($th->getMessage(), $th->getTrace(), 401);
        } catch (\Exception $th) {
            return $this->errorResponse('An error occurred while retrieving the user.', $th->getMessage(), 500);
        }
    }

    public function logout(Request $request, LogoutUseCase $logoutUseCase)
    {
        try {
            $logoutUseCase->execute($request);
            return $this->successResponse('Logout successful!');
        } catch (\Exception $th) {
            return $this->errorResponse('An error occurred during logout.', $th->getMessage(), 500);
        }
    }
}
