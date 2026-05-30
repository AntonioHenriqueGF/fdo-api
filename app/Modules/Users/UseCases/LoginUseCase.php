<?php

namespace App\Modules\Users\UseCases;

use App\Modules\Users\Models\UsersModels;
use Illuminate\Support\Facades\Hash;

class LoginUseCase
{
    public function __construct(
        private UsersModels $usersModel
    ) {
        // You can inject any dependencies here if needed
    }

    public function execute(array $credentials)
    {
        dd($this->usersModel->verifyUserCredentials($credentials['email'], $credentials['password']));
        // Implement your login logic here, such as validating user credentials and generating a token
        return 'Login successful!';
    }
}
