<?php

namespace App\Repositories;

use PDO;

class TokenRepository
{
    public function __construct(private PDO $pdo) {

    }

    /**
     * Create a new API token.
     */
    public function create(int $userId, string $tokenHash, ?string $expiresAt = null): int {
        $sql = "
            INSERT INTO api_tokens (
                user_id,
                token_hash,
                expires_at
            )
            VALUES (
                :user_id,
                :token_hash,
                :expires_at
            )
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Find token and related user.
     */
    // public function findByTokenHash(string $tokenHash): ?array {
    //     $sql = "
    //         SELECT
    //             api_tokens.id AS token_id,
    //             api_tokens.user_id,
    //             api_tokens.token_hash,
    //             api_tokens.expires_at,
    //             users.name,
    //             users.email,
    //             users.role
    //         FROM api_tokens
    //         INNER JOIN users
    //             ON users.id = api_tokens.user_id
    //         WHERE api_tokens.token_hash = :token_hash
    //         LIMIT 1
    //     ";
    //     $stmt = $this->pdo->prepare($sql);
    //     $stmt->execute([
    //         'token_hash' => $tokenHash
    //     ]);
    //     $token = $stmt->fetch(PDO::FETCH_ASSOC);
    //     return $token ?: null;
    // }
    public function findByTokenHash(string $tokenHash): ?array {
        $sql = "
            SELECT
                api_tokens.id AS token_id,
                api_tokens.user_id,
                api_tokens.token_hash,
                api_tokens.expires_at,
                users.id,
                users.name,
                users.email,
                users.role
            FROM api_tokens
            INNER JOIN users
                ON users.id = api_tokens.user_id
            WHERE api_tokens.token_hash = :token_hash
            AND (
                api_tokens.expires_at IS NULL
                OR api_tokens.expires_at > NOW()
            )
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'token_hash' => $tokenHash
        ]);
        $token = $stmt->fetch(PDO::FETCH_ASSOC);
        return $token ?: null;
    }

    /**
     * Delete a specific token.
     */
    public function deleteByTokenHash(string $tokenHash): bool {
        $sql = "
            DELETE FROM api_tokens
            WHERE token_hash = :token_hash
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'token_hash' => $tokenHash
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete all tokens belonging to a user.
     */
    public function deleteByUserId(int $userId): bool {
        $sql = "
            DELETE FROM api_tokens
            WHERE user_id = :user_id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete expired tokens.
     */
    public function deleteExpired(): int
    {
        $sql = "
            DELETE FROM api_tokens
            WHERE expires_at IS NOT NULL
              AND expires_at < NOW()
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }
}