<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\TokenRepository;
use App\Services\CartService;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private TokenRepository $tokenRepository,
        private CartService $cartService
    ) {}

    public function registerCustomer(array $data): array {
        $passwordHash = password_hash(
            $data['password'],
            PASSWORD_DEFAULT
        );

        $userId = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $passwordHash,
            'role' => 'customer'
        ]);

        return $this->userRepository->findById(
            $userId
        );
    }

    public function findUserByEmail(string $email): ?array {
        return $this->userRepository->findByEmail(
            $email
        );
    }

    public function login(string $email,string $password): ?array {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }
        $token = bin2hex(random_bytes(32));
        // Token storage will be added next.
        // Store only hash in database
        $tokenHash = hash('sha256',$token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $this->tokenRepository->create((int) $user['id'], $tokenHash, $expiresAt);
        $this->cartService->mergeGuestCart((int) $user['id']);
        return [
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ],
            'token' => $token,
            'expires_at' => $expiresAt,
        ];
    }

    public function logout(string $token): bool
    {
        // Token deletion will be added next.
        $tokenHash = hash('sha256',$token);
        return $this->tokenRepository->deleteByTokenHash($tokenHash);
    }

    public function authenticate(string $token): ?array {
        $tokenHash = hash('sha256',$token);
        $tokenData = $this->tokenRepository->findByTokenHash($tokenHash);
        if (!$tokenData) {
            return null;
        }
        // Check expiration
        if ($tokenData['expires_at'] !== null && strtotime($tokenData['expires_at']) < time()) {
            $this->tokenRepository->deleteByTokenHash($tokenHash);
            return null;
        }

        return [
            'id' => (int) $tokenData['user_id'],
            'name' => $tokenData['name'],
            'email' => $tokenData['email'],
            'role' => $tokenData['role'],
        ];
    }
}