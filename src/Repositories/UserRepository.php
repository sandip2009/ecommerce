<?php

namespace App\Repositories;

use PDO;

class UserRepository
{
    public function __construct(private PDO $pdo) {

    }

    /**
     * Find user by ID.
     */
    public function findById(int $id): ?array
    {
        $sql = "
            SELECT
                id,
                name,
                email,
                password,
                role,
                created_at,
                updated_at
            FROM users
            WHERE id = :id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $id
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "
            SELECT
                id,
                name,
                email,
                password,
                role,
                created_at,
                updated_at
            FROM users
            WHERE email = :email
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'email' => $email
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Create a new user.
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO users (
                name,
                email,
                password,
                role
            )
            VALUES (
                :name,
                :email,
                :password,
                :role
            )
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? 'customer',
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Check whether email already exists.
     */
    public function existsByEmail(string $email): bool
    {
        $sql = "
            SELECT 1
            FROM users
            WHERE email = :email
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'email' => $email
        ]);
        return (bool) $stmt->fetchColumn();
    }
}