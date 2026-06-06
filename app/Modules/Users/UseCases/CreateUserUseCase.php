<?php

namespace App\Modules\Users\UseCases;

use App\Modules\Users\Exceptions\AuthenticationException;
use App\Modules\Users\Models\UsersModel;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;

class CreateUserUseCase
{
    public function __construct(
        private UsersModel $userModel
    ) {}
    public function execute(array $data, Session $session): mixed
    {
        $user = null;
        try {
            $user = $this->userModel->createUser($data);
        } catch (\Exception $e) {
            // Handle exceptions, log errors, etc.
            throw new \RuntimeException('Failed to create user: ' . $e->getMessage());
        }

        try {
            $credentials = [
                'use_email' => $data['email'],
                'password' => $data['password'],
            ];

            if (!Auth::attempt($credentials)) {
                throw new AuthenticationException();
            }

            if ($session) {
                $session->regenerate();
                $session->put('user_id', $user->id);
            }

            return $user;
        } catch (\Exception $e) {
            // Handle session regeneration errors, log them, etc.
            throw new \RuntimeException('Failed to regenerate session: ' . $e->getMessage());
        }
    }
}
