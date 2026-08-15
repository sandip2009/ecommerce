<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;


class AuthMiddleware
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function handle(Request $request): ?array
    {
        $token = $request->bearerToken();

        if (!$token) {
            Response::error(
                'Authentication token is required.',
                401
            );
            return null;
        }

        $user = $this->authService->authenticate($token);

        if (!$user) {
            Response::error(
                'Invalid or expired token.',
                401
            );
            return null;
        }
        return $user;
    }
}