<?php

namespace App\Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Users\Http\Requests\CreateUserRequest;
use App\Modules\Users\UseCases\CreateUserUseCase;

class UserController extends Controller
{
    public function create(CreateUserRequest $request, CreateUserUseCase $createUserUseCase)
    {
        try {
            return $this->successResponse('User created successfully!', $createUserUseCase->execute($request->only(['name', 'email', 'password']), $request->session()));
        } catch (\Exception $th) {
            return $this->errorResponse('An error occurred while creating the user.', $th->getMessage(), 500);
        }
    }
}
