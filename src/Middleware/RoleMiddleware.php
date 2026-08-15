<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class RoleMiddleware
{
    public function handle(Request $request, string $role): bool {
        $user = $request->getAttribute('user');

        if (!$user) {
            Response::error('Unauthorized.', 401);
            return false;
        }

        if ($user['role'] !== $role) {
            Response::error('Access denied.', 403);
            return false;
        }
        return true;
    }
}