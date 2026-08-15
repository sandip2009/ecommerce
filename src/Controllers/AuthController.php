<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Core\Validator;

class AuthController
{
    public function __construct(private AuthService $authService) {

    }

    public function register(Request $request): never {
        $data = $request->input();

        $validator = new Validator();

        $validator
            ->required('name', $data['name'] ?? null)
            ->string('name', $data['name'] ?? null)
            ->maxLength('name', $data['name'] ?? null, 100)
            ->required('email', $data['email'] ?? null)
            ->string('email', $data['email'] ?? null)
            ->required('password', $data['password'] ?? null)
            ->string('password', $data['password'] ?? null)
            ->min('password', $data['password'] ?? null, 6);

        if ($validator->fails()) {
            Response::error(
                'Validation failed.',
                422,
                $validator->errors()
            );
        }

        $existingUser = $this->authService->findUserByEmail(
            $data['email']
        );

        if ($existingUser !== null) {
            Response::error(
                'Email already registered.',
                409
            );
        }

        $user = $this->authService->registerCustomer(
            $data
        );

        Response::success(
            $user,
            'Customer registered successfully.',
            201
        );
    }

    public function login(Request $request): mixed
    {
        $data = $request->input();
        $result = $this->authService->login(
            $data['email'] ?? '',
            $data['password'] ?? ''
        );

        if ($result === null) {
            return Response::error(
                'Invalid email or password.',
                401
            );
        }

        return Response::success(
            $result,
            'Login successful.'
        );
    }

    public function logout(Request $request): mixed
    {
        $token = $request->bearerToken();

        if ($token) {
            $this->authService->logout($token);
        }

        return Response::success(
            null,
            'Logout successful.'
        );
    }
}